<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Cli\Router;


use ObjectivePHP\Cli\Action\CliActionException;
use ObjectivePHP\Cli\Action\CliActionInterface;
use ObjectivePHP\Cli\Action\Parameter\Argument;
use ObjectivePHP\Cli\Action\Parameter\ParameterInterface;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Cli\Request\CliRequest;
use ObjectivePHP\Cli\Request\Parameter\Container\CliParameterContainer;
use ObjectivePHP\Router\MatchedRoute;
use ObjectivePHP\Router\RouterInterface;
use ObjectivePHP\Router\RoutingResult;

class CliRouter implements RouterInterface
{
    protected $registeredCommands = [];
    
    public function route(ApplicationInterface $app): RoutingResult
    {
        if ($app->getRequest() instanceof CliRequest)
        {
            $requestedCommand = ltrim($app->getRequest()->getRoute());
            
            foreach ($this->registeredCommands as $command)
            {
                if (is_string($command))
                {
                    if (class_exists($command))
                    {
                        $command = new $command($app);
                    }
                    else
                    {
                        throw new CliActionException('Unknown CLI command registered');
                    }
                }
                
                if (!$command instanceof CliActionInterface)
                {
                    throw new CliActionException('Invalid CLI command registered');
                }
                
                if ($command->getCommand() == $requestedCommand)
                {
                    // init parameters
                    /** @var CliParameterContainer $parameters */
                    $parameters = $app->getRequest()->getParameters();
                    $cliParameters = [];
                    $argv = $_SERVER['argv'];
                    
                    array_shift($argv); // remove script
                    array_shift($argv); // remove command
                    $handledParameters = [];
                    $arguments = [];
                    
                    /** @var ParameterInterface $parameter */
                    foreach($command->getExpectedParameters() as $parameter)
                    {
                        
                        // skip aliased parameters that are already handled
                        if (in_array($parameter, $handledParameters)) continue;
    
                        // keep arguments apart - they should be processed last
                        if($parameter instanceof Argument)
                        {
                            $arguments[] = $parameter;
                            continue;
                        }
                        
                        $argv = $parameter->hydrate($argv);
                        $value = $parameter->getValue();
                        
                        if($value)
                        {
                            $longName = $parameter->getLongName();
                            $shortName = $parameter->getShortName();
                            
                            if($shortName)
                            {
                                $cliParameters[$shortName] = $value;
                            }
                            
                            if($longName && $shortName)
                            {
                                $cliParameters[$longName] = &$cliParameters[$shortName];
                            }
                            else if($longName)
                            {
                                $cliParameters[$longName] = $value;
                            }
                        } else if($parameter->getOptions() & ParameterInterface::MANDATORY)
                        {
                            printf("Mandatory parameter '%s' is missing", $parameter->getLongName() ?: $parameter->getShortName());
                            echo PHP_EOL;
                            die($command->getUsage());
                        }
                        
                        $handledParameters[] = $parameter;
                    }
                    
                    
                    // look for unexpected params or toggles
                    foreach($argv as $argument)
                    {
                        if (strpos($argument, '-') === 0)
                        {
                            echo sprintf('Unexpected parameter: %s' . PHP_EOL, $argument);
                            echo $command->getUsage();
                            exit;
                        }
                    }
                    
                    /** @var Argument $argument */
                    foreach($arguments as $argument)
                    {
                        $argv = $argument->hydrate($argv);
                        $value = $argument->getValue();
                        if (!$value && ($argument->getOptions() & Argument::MANDATORY))
                        {
                            printf("Mandatory parameter '%s' is missing", $parameter->getLongName() ?: $parameter->getShortName());
                            echo PHP_EOL;
                            die($command->getUsage());
                        }
                        $cliParameters[$argument->getLongName()] = $value;
                    }
                    
                    $parameters->setCli($cliParameters);
                    return new RoutingResult(new MatchedRoute($this, $command->getCommand(), $command));
                }
            }
        }
        
        return new RoutingResult();
    }
    
    public function url($route, $params = [])
    {
        // TODO: Implement url() method.
    }
    
    public function registerCommand($command)
    {
        $this->registeredCommands[] = $command;
        $this->registeredCommands   = array_unique($this->registeredCommands);
        
        return $this;
    }
}
