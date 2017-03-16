<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Cli\Action;


use ObjectivePHP\Cli\Action\Parameter\Argument;
use ObjectivePHP\Cli\Action\Parameter\Exception;
use ObjectivePHP\Cli\Action\Parameter\ParameterException;
use ObjectivePHP\Cli\Action\Parameter\ParameterInterface;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\Invokable\InvokableInterface;
use ObjectivePHP\ServicesFactory\ServicesFactory;

abstract class AbstractCliAction implements CliActionInterface
{
    
    protected $expectedParameters = [];
    
    protected $eventsHandler;
    
    protected $application;
    
    protected $servicesFactory;
    
    /**
     * @var string
     */
    protected $command = '';
    
    /**
     * @param array $args
     *
     * @return mixed
     * @throws Exception
     */
    public function __invoke(...$args)
    {
        
        $app = array_shift($args);
        
        if (!$app instanceof ApplicationInterface)
        {
            throw new CliActionException('Action must be invoked with an ApplicationInterface instance as first parameter');
        }
        
        
        $this->setApplication($app);
        $this->setServicesFactory($app->getServicesFactory());
        $this->setEventsHandler($app->getEventsHandler());
        
        // init action
        $this->init();
        
        // actually execute action
        return $this->run($app);
        
    }
    
    public function init()
    {
        
    }
    
    public function expects(ParameterInterface $parameter) 
    {
        // take care of optional arguments consistency
        if ($parameter instanceof Argument && $parameter->getOptions() & ParameterInterface::MANDATORY)
        {
            $lastArgument = null;
            foreach ($this->getExpectedParameters() as $expectedParameter)
            {
                if ($expectedParameter instanceof Argument)
                {
                    $lastArgument = $expectedParameter;
                }
            }
        
            if ($lastArgument && !($lastArgument->getOptions() & ParameterInterface::MANDATORY))
            {
                throw new ParameterException(sprintf('It is forbidden to expect a mandatory parameter (%s) after an optional one (%s)', $parameter->getLongName(), $expectedParameter->getLongName()));
            }
        }
        
        if($shortName = $parameter->getShortName())
        {
            if(isset($this->expectedParameters[$shortName]))
            {
                throw new ParameterException(sprintf('Parameter "%s" has already been registered', $shortName));
            }
            
            $this->expectedParameters[$shortName] = $parameter;
        }
        
        if ($longName = $parameter->getLongName())
        {
            if (isset($this->expectedParameters[$longName]))
            {
                throw new ParameterException(sprintf('Parameter "%s" has already been registered', $longName));
            }
        
            $this->expectedParameters[$longName] = $parameter;
        }
        
        
        return $this;
    }
    
    public function getExpectedParameters()
    {
        return $this->expectedParameters;
    }
    
    public function getUsage(): string
    {
        $output = 'Usage for command "' . $this->getCommand() . '":' . PHP_EOL ;
        $expectedParameters = $this->getExpectedParameters();
        $handledParameters = [];
        
        /** @var \ObjectivePHP\Cli\Action\Parameter\ParameterInterface $parameter */
        foreach ($expectedParameters as $parameter)
        {
            if(in_array($parameter, $handledParameters)) continue;
            $shortName = $parameter->getShortName();
            $longName  = $parameter->getLongName();
            $output .= "\t";
            if ($shortName) $output .= '-' . $shortName;
            if ($shortName && $longName) $output .= " | ";
            if ($longName) $output .= '--' . $longName;
            $output .= "\t\t\t" . $parameter->getDescription();
            $output .= PHP_EOL;
            $handledParameters[] = $parameter;
        }
        
        return $output;
    }
    
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
        return $this->getUsage();
    }
    
    public function getCommand(): string
    {
        return $this->command;
    }
    
    public function setCommand($command)
    {
        $this->command = $command;
        
        return $this;
    }
    
    public function getCallable()
    {
        return $this;
    }
    
    
    abstract public function run(ApplicationInterface $app);
}
