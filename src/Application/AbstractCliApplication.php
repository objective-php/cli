<?php

namespace ObjectivePHP\Cli\Application;

use Composer\Autoload\ClassLoader;
use League\CLImate\CLImate;
use ObjectivePHP\Application\AbstractApplication;
use ObjectivePHP\Application\AbstractEngine;
use ObjectivePHP\Application\Workflow\WorkflowEvent;
use ObjectivePHP\Cli\Action\AbstractCliAction;
use ObjectivePHP\Cli\Action\CliActionInterface;
use ObjectivePHP\Cli\Action\Parameter\Argument;
use ObjectivePHP\Cli\Action\Parameter\ParameterInterface;
use ObjectivePHP\Cli\Action\Usage;
use ObjectivePHP\Cli\Config\CliCommandsPaths;
use ObjectivePHP\Cli\Exception\CliException;
use ObjectivePHP\Cli\Exception\CliExceptionHandler;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\ServicesFactory\ServicesFactory;
use ObjectivePHP\ServicesFactory\Specification\PrefabServiceSpecification;

/**
 * Class AbstractCliApplication
 *
 * @package ObjectivePHP\Cli\Application
 */
class AbstractCliApplication extends AbstractApplication implements CliApplicationInterface
{
    /**
     * @var CLImate
     */
    protected $console;

    /**
     * @var array
     */
    protected $commands;

    /**
     * AbstractCliApplication constructor.
     *
     * @param ClassLoader|null $autoloader
     */
    public function __construct(AbstractEngine $engine)
    {
        $this->setEngine($engine);
        $buffer = $this->cleanBuffer();

        ob_start();

        if ($buffer) {
            echo $buffer;
        }

        if ($autoloader = $this->getEngine()->getAutoloader()) {
            // register default local packages storage
            $reflectionObject = new \ReflectionObject($this);
            $autoloader->addPsr4($reflectionObject->getNamespaceName() . '\\Package\\', 'app/packages/');
        }

        $this->console = new CLImate();
        $this->eventsHandler = new EventsHandler();

        // register default configuration directives
        $this->getConfig()->registerDirective(...$this->getConfigDirectives());

        // load default configuration parameters
        $this->getConfig()->hydrate($this->getConfigParams());

    }

    /**
     * @return CLImate
     */
    public function getConsole(): CLImate
    {
        return $this->console;
    }

    /**
     * @param CLImate $console
     */
    public function setConsole(CLImate $console)
    {
        $this->console = $console;
    }

    public function init()
    {
        // override this method in your own CliApplication class
        // to make init act as a delegated constructor
    }

    /**
     * @return mixed
     */
    public function run()
    {
        $c = $this->getConsole();
        try {
            // init parameters
            $cliParameters = [];
            $argv = $_SERVER['argv'];

            $script = array_shift($argv); // remove script
            $requestedCommand = array_shift($argv); // remove command
            $handledParameters = [];
            $arguments = [];

            if (empty($requestedCommand)) {
                goto usage;
            }

            $commands = $this->findAvailableCommands();

            foreach ($commands as $command) {
                if (!($command->getCommand() === $requestedCommand)) {
                    continue;
                }

                $this->getServicesFactory()->injectDependencies($command);

                /** @var ParameterInterface $parameter */
                foreach ($command->getExpectedParameters() as $parameter) {
                    // skip aliased parameters that are already handled
                    if (in_array($parameter, $handledParameters)) {
                        continue;
                    }

                    // keep arguments apart - they should be processed last
                    if ($parameter instanceof Argument) {
                        $arguments[] = $parameter;
                        continue;
                    }

                    $argv = $parameter->hydrate($argv);
                    $value = $parameter->getValue();

                    if (!is_null($value)) {
                        $longName = $parameter->getLongName();
                        $shortName = $parameter->getShortName();

                        if ($shortName) {
                            $cliParameters[$shortName] = $value;
                        }

                        if ($longName && $shortName) {
                            $cliParameters[$longName] = &$cliParameters[$shortName];
                        } else {
                            if ($longName) {
                                $cliParameters[$longName] = $value;
                            }
                        }
                    } else {
                        if ($parameter->getOptions() & ParameterInterface::MANDATORY) {
                            $c->br();
                            $c->error(
                                sprintf(
                                    'Mandatory parameter "%s" is missing',
                                    $parameter->getLongName() ?: $parameter->getShortName()
                                )
                            );
                            $c->br();
                            echo $command->getUsage();
                            $c->br();
                            exit;
                        }
                    }

                    $handledParameters[] = $parameter;
                }

                // look for unexpected params or toggles
                if (!$command->areUnexpectedParametersAllowed()) {
                    foreach ($argv as $argument) {
                        if (strpos($argument, '-') === 0) {
                            $c->br();
                            $c->out(sprintf('Unexpected parameter "<error>%s</error>"', $argument));
                            $c->br();
                            echo $command->getUsage();
                            exit;
                        }
                    }
                }

                /** @var Argument $argument */
                foreach ($arguments as $argument) {
                    $argv = $argument->hydrate($argv);
                    $value = $argument->getValue();
                    if (is_null($value) && ($argument->getOptions() & Argument::MANDATORY)) {
                        $c->br();
                        $c->out(
                            sprintf(
                                'Mandatory argument "<error>%s</error>" is missing',
                                $argument->getLongName()
                            )
                        );
                        $c->br();
                        echo $command->getUsage();
                        $c->br();
                        exit;
                    }
                    $cliParameters[$argument->getLongName()] = $value;
                }

                $command->run($this, $this->console, $requestedCommand);

                exit;
            }

            $this->getConsole()->error(sprintf('Unknown command "%s"', $requestedCommand));
            usage:
            (new Usage())->run($this, $this->console, $requestedCommand);
            exit(0);

        } catch (\Throwable $e) {
            (new CliExceptionHandler)->handle($e);
        }
    }

    /**
     * Defines default application config directives
     */
    protected function getConfigDirectives(): array
    {
        return [
            new CliCommandsPaths()
        ];
    }

    /**
     * @return array
     */
    protected function getConfigParams()
    {
        return [
            'cli.commands.paths' => ['default' => getcwd() . '/app/src/Cli']
        ];
    }

    public function findAvailableCommands()
    {
        if (is_null($this->commands)) {
            $this->commands = [new Usage()];
            // load commands
            $commandsPaths = $this->getConfig()->get(CliCommandsPaths::KEY);
            $declaredClasses = get_declared_classes();
            foreach ($commandsPaths as $commandsPath) {
                $dir = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($commandsPath));

                /** @var \SplFileInfo $entry */
                foreach ($dir as $entry) {
                    if ($entry->getExtension() == 'php') {
                        require_once $entry->getRealPath();
                    }
                }
            }

            $commandClasses = array_slice(get_declared_classes(), count($declaredClasses));

            foreach ($commandClasses as $commandClass) {
                // exclude abstract classes or interfaces
                $reflectedCommand = new \ReflectionClass($commandClass);

                if ($reflectedCommand->isAbstract() || $reflectedCommand->isInterface()) {
                    continue;
                }

                /** @var AbstractCliAction $command */
                $command = $this->getServicesFactory($commandClass);

                if (!$command instanceof CliActionInterface) {
                    throw new CliException(
                        sprintf(
                            'Cannot register command "%s" because it does not implement %s',
                            get_class($command),
                            CliActionInterface::class
                        )
                    );
                }

                $this->commands[] = $command;
            }
        }

        return $this->commands;
    }
}
