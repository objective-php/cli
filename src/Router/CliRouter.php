<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Cli\Router;


use League\CLImate\CLImate;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Cli\Action\CliActionException;
use ObjectivePHP\Cli\Action\CliActionInterface;
use ObjectivePHP\Cli\Action\Parameter\Argument;
use ObjectivePHP\Cli\Action\Parameter\ParameterInterface;
use ObjectivePHP\Cli\Action\Usage;
use ObjectivePHP\Cli\CliExceptionHandler;
use ObjectivePHP\Cli\Config\CliCommand;
use ObjectivePHP\Cli\Request\CliRequest;
use ObjectivePHP\Cli\Request\Parameter\Container\CliParameterContainer;
use ObjectivePHP\Router\MatchedRoute;
use ObjectivePHP\Router\RouterInterface;
use ObjectivePHP\Router\RoutingResult;
use ObjectivePHP\ServicesFactory\Exception\Exception;
use ObjectivePHP\ServicesFactory\ServiceReference;

/**
 * Class CliRouter
 *
 * @package ObjectivePHP\Cli\Router
 */
class CliRouter implements RouterInterface
{
    protected $registeredCommands = [];
    
    protected $warningMessageDisplayed;
    
    protected $commands;
    
    
    /**
     * CliRouter constructor.
     */
    public function __construct()
    {
        $this->registerCommand((new Usage)->setRouter($this));
    }
    
    public function registerCommand($command)
    {
        $this->registeredCommands[] = $command;
        
        return $this;
    }
    
    /**
     * @param ApplicationInterface $app
     *
     * @return RoutingResult
     * @throws CliActionException
     */
    public function route(ApplicationInterface $app): RoutingResult
    {
        if ($app->getRequest() instanceof CliRequest) {
            $app->setExceptionHandler(new CliExceptionHandler());
            $c = new CLImate();
            
            // just in time command registering
            $commands = $app->getConfig()->get(CliCommand::class);
            if ($commands) {
                foreach ($commands as $command) {
                    $this->registerCommand($command);
                }
            }
            foreach ($this->registeredCommands as $command) {
                if ($command instanceof ServiceReference) {
                    try {
                        $commandInstance = $app->getServicesFactory()->get($command);
                    } catch (Exception $e) {
                        $this->warn('The command referencing service "' . $command->getId() . '" is not registered in the ServicesFactory');
                        continue;
                    }
                    
                    if (!$commandInstance instanceof CliActionInterface) {
                        $this->warn('The command referencing service "' . $command->getId() . '" is not an instance of ' . CliActionInterface::class);
                        continue;
                    }
                    
                    $command = $commandInstance;
                    
                } elseif (is_string($command)) {
                    if (!class_exists($command)) {
                        $this->warn('The command class "' . $command . '" does not exist');
                        continue;
                    }
                    
                    $commandInstance = new $command;
                    
                    if (!$commandInstance instanceof CliActionInterface) {
                        $this->warn('The command class "' . $command . '" is not an instance of ' . CliActionInterface::class);
                        continue;
                    }
                    
                    $app->getServicesFactory()->injectDependencies($commandInstance);
                    
                    $command = $commandInstance;
                    
                }
                
                $this->commands[] = $command;
                
            }
            if ($this->warningMessageDisplayed) {
                $c->br();
            }
            
            
            $requestedCommand = ltrim($app->getRequest()->getRoute());
            
            route:
            // redirect to usage if no command has been provided
            if (!$requestedCommand) {
                $requestedCommand = 'usage';
            }
            
            
            try {
                foreach ($this->getCommands() as $command) {
                    if (!$command instanceof CliActionInterface) {
                        throw new CliActionException('Invalid CLI command registered');
                    }
                    
                    if ($command->getCommand() == $requestedCommand) {
                        // init parameters
                        /** @var CliParameterContainer $parameters */
                        $parameters    = $app->getRequest()->getParameters();
                        $cliParameters = [];
                        $argv          = $_SERVER['argv'];
                        
                        array_shift($argv); // remove script
                        array_shift($argv); // remove command
                        $handledParameters = [];
                        $arguments         = [];
                        
                        
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
                            
                            $argv  = $parameter->hydrate($argv);
                            $value = $parameter->getValue();
                            
                            if (!is_null($value)) {
                                $longName  = $parameter->getLongName();
                                $shortName = $parameter->getShortName();
                                
                                if ($shortName) {
                                    $cliParameters[$shortName] = $value;
                                }
                                
                                if ($longName && $shortName) {
                                    $cliParameters[$longName] = &$cliParameters[$shortName];
                                } else if ($longName) {
                                    $cliParameters[$longName] = $value;
                                }
                            } else if ($parameter->getOptions() & ParameterInterface::MANDATORY) {
                                $c->br();
                                $c->error(sprintf('Mandatory parameter "%s" is missing',
                                    $parameter->getLongName() ?: $parameter->getShortName()));
                                $c->br();
                                echo $command->getUsage();
                                $c->br();
                                exit;
                                
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
                            
                            $argv  = $argument->hydrate($argv);
                            $value = $argument->getValue();
                            if (is_null($value) && ($argument->getOptions() & Argument::MANDATORY)) {
                                $c->br();
                                $c->out(sprintf('Mandatory argument "<error>%s</error>" is missing',
                                    $argument->getLongName()));
                                $c->br();
                                echo $command->getUsage();
                                $c->br();
                                exit;
                            }
                            $cliParameters[$argument->getLongName()] = $value;
                        }
                        
                        $parameters->setCli($cliParameters);
                        
                        return new RoutingResult(new MatchedRoute($this, $command->getCommand(), $command));
                    }
                    
                }
            } catch
            (CliActionException $e) {
                if (empty($c)) {
                    $c = new CLImate();
                }
                $c->out(sprintf('<error>%s</error>', $e->getMessage()));
                exit;
            }
            
            // looks like no command matched...
            // redirect to 'usage' command while keeping original requested command in Request
            $requestedCommand = 'usage';
            goto route;
        }
        
        return new RoutingResult();
        
    }
    
    protected function warn($message)
    {
        $c = new CLImate();
        if (!$this->warningMessageDisplayed) {
            $c->br();
            $c->backgroundLightGray('<red>Warning:</red>');
            $c->br();
            $this->warningMessageDisplayed = true;
        }
        $c->tab()->out(' - ' . $message);
    }
    
    public function getCommands()
    {
        return $this->commands;
    }
    
    /**
     * @return array
     */
    public function getRegisteredCommands(): array
    {
        return $this->registeredCommands;
    }
    
    public function url($route, $params = [])
    {
        // TODO: Implement url() method.
    }
}
