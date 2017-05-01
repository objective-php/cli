<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Cli\Action;


use League\CLImate\CLImate;
use League\CLImate\Util\Cursor;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Cli\Action\Parameter\Argument;
use ObjectivePHP\Cli\Action\Parameter\ParameterException;
use ObjectivePHP\Cli\Action\Parameter\ParameterInterface;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\Invokable\InvokableInterface;
use ObjectivePHP\ServicesFactory\ServicesFactory;

/**
 * Class AbstractCliAction
 * @package ObjectivePHP\Cli\Action
 */
abstract class AbstractCliAction implements CliActionInterface
{

    /**
     * @var array
     */
    protected $expectedParameters = [];

    /**
     * @var
     */
    protected $eventsHandler;

    /**
     * @var
     */
    protected $application;

    /**
     * @var
     */
    protected $servicesFactory;

    /**
     * @var string
     */
    protected $command = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var bool
     */
    protected $allowUnexpectedParameters = false;

    /**
     * @param array $args
     *
     * @return mixed
     * @throws CliActionException
     */
    public function __invoke(...$args)
    {

        $app = array_shift($args);

        if (!$app instanceof ApplicationInterface) {
            throw new CliActionException('Action must be invoked with an ApplicationInterface instance as first parameter');
        }

        $this->setApplication($app);
        $this->setServicesFactory($app->getServicesFactory());
        $this->setEventsHandler($app->getEventsHandler());

        // actually execute action
        return $this->run($app);

    }

    /**
     * @param ParameterInterface $parameter
     * @return $this
     * @throws ParameterException
     */
    public function expects(ParameterInterface $parameter)
    {
        // take care of optional arguments consistency
        if ($parameter instanceof Argument && $parameter->getOptions() & ParameterInterface::MANDATORY) {
            $lastArgument = null;
            foreach ($this->getExpectedParameters() as $expectedParameter) {
                if ($expectedParameter instanceof Argument) {
                    $lastArgument = $expectedParameter;
                }
            }

            if ($lastArgument && !($lastArgument->getOptions() & ParameterInterface::MANDATORY)) {
                throw new ParameterException(sprintf('It is forbidden to expect a mandatory parameter (%s) after an optional one (%s)', $parameter->getLongName(), $expectedParameter->getLongName()));
            }
        }

        if ($shortName = $parameter->getShortName()) {
            if (isset($this->expectedParameters[$shortName])) {
                throw new ParameterException(sprintf('Parameter "%s" has already been registered', $shortName));
            }

            $this->expectedParameters[$shortName] = $parameter;
        }

        if ($longName = $parameter->getLongName()) {
            if (isset($this->expectedParameters[$longName])) {
                throw new ParameterException(sprintf('Parameter "%s" has already been registered', $longName));
            }

            $this->expectedParameters[$longName] = $parameter;
        }


        return $this;
    }

    /**
     * @return array
     */
    public function getExpectedParameters()
    {
        return $this->expectedParameters;
    }

    /**
     * @return string
     */
    public function getUsage(): string
    {
        $c = new CLImate();
        $cursor = new Cursor();

        echo $cursor->defaultStyle();

        ob_start();
        $expectedParameters = $this->getExpectedParameters();
        $handledParameters = [];
        $c->style->addCommand('mandatory', ['red', 'bold']);


        $arguments = array_filter($expectedParameters, function ($param) {
            return $param instanceof Argument;
        });
        $params = array_filter($expectedParameters, function ($param) {
            return !$param instanceof Argument;
        });

        $sortMandatories = function (ParameterInterface $a, ParameterInterface $b) {
            return (!$a instanceof Argument && ($a->getOptions() & ParameterInterface::MANDATORY)) ? -1 : 1;
        };

        usort($params, $sortMandatories);

        $expectedParameters = array_merge($params, $arguments);
        $c->out(sprintf('Usage for command "<bold><green>%s</green></bold>"', $this->getCommand()));
        $parametersList = [];

        /** @var \ObjectivePHP\Cli\Action\Parameter\ParameterInterface $parameter */
        foreach ($expectedParameters as $parameter) {
            $output = $style = '';

            if (in_array($parameter, $handledParameters)) continue;
            $shortName = $parameter->getShortName();
            $longName = $parameter->getLongName();

            if ($parameter->getOptions() & ParameterInterface::MANDATORY) $style = 'mandatory';
            else $style = 'dim';

            if (!$parameter instanceof Argument) {
                $argName = '';
                if ($longName) $argName .= '--' . $longName;
                if ($shortName && $longName) $argName .= " | ";
                if ($shortName) $argName .= '-' . $shortName;
                $argName = sprintf('<%s>' . $argName . '</%s>', $style, $style);
            } else {
                $argName = $longName;
                $argName = sprintf('<<%s>' . $argName . '</%s>>', $style, $style);
            }


            $parametersList[] = [$argName,  $parameter->getDescription()];

            $handledParameters[] = $parameter;
        }

        foreach($parametersList as $param)
        {
            $c->tab()->inline($param[0])->inline("\r" . "\033[30C")->out($param[1]);
        }

        return ob_get_clean();
    }

    /**
     * @param $param
     * @param null $default
     * @param string $origin
     * @return mixed
     */
    public function getParam($param, $default = null, $origin = 'cli')
    {
        return $this->getApplication()->getRequest()->getParameters()->get($param, $default, $origin);
    }

    /**
     * @return ApplicationInterface
     */
    public function getApplication(): ApplicationInterface
    {
        return $this->application;
    }

    /**
     * @param ApplicationInterface $application
     *
     * @return $this|InvokableInterface
     */
    public function setApplication(ApplicationInterface $application): InvokableInterface
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Return the given service
     *
     * @param $serviceId
     *
     * @return mixed|null
     * @throws \ObjectivePHP\ServicesFactory\Exception
     */
    public function getService($serviceId)
    {
        return $this->getServicesFactory()->get($serviceId);
    }

    /**
     * @return ServicesFactory
     */
    public function getServicesFactory(): ServicesFactory
    {
        return $this->servicesFactory;
    }

    /**
     * @param ServicesFactory $servicesFactory
     *
     * @return $this
     */
    public function setServicesFactory(ServicesFactory $servicesFactory)
    {
        $this->servicesFactory = $servicesFactory;

        return $this;
    }

    /**
     * @return EventsHandler
     */
    public function getEventsHandler()
    {
        return $this->eventsHandler;
    }

    /**
     * @param EventsHandler $eventsHandler
     *
     * @return $this
     */
    public function setEventsHandler($eventsHandler)
    {
        $this->eventsHandler = $eventsHandler;

        return $this;
    }

    /**
     * Return short description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @param $command
     * @return $this
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return $this
     */
    public function getCallable()
    {
        return $this;
    }


    /**
     * @param ApplicationInterface $app
     * @return mixed
     */
    abstract public function run(ApplicationInterface $app);

    /**
     * @return bool
     */
    public function areUnexpectedParametersAllowed(): bool
    {
        return $this->allowUnexpectedParameters;
    }

    /**
     * @param bool $allowUnexpectedParameters
     */
    public function allowUnexpectedParameters(bool $allowUnexpectedParameters = true)
    {
        $this->allowUnexpectedParameters = $allowUnexpectedParameters;
    }
}
