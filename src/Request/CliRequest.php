<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Cli\Request;


use ObjectivePHP\Cli\Request\Parameter\Container\CliParameterContainer;
use ObjectivePHP\Message\Request\Parameter\Container\ParameterContainerInterface;
use ObjectivePHP\Message\Request\RequestInterface;
use ObjectivePHP\Router\MatchedRoute;
use Psr\Http\Message\StreamInterface;

/**
 * Class CliRequest
 * @package ObjectivePHP\Cli\Request
 */
class CliRequest implements RequestInterface
{
    /**
     * @var string Command line
     */
    protected $command;
    
    /**
     * @var CliParameterContainer
     */
    protected $parameters;
    
    /**
     * @var MatchedRoute
     */
    protected $matchedRoute;
    
    /**
     * @param null|string                     $command     URI for the request, if any.
     * @param null|string                     $method  HTTP method for the request, if any.
     * @param string|resource|StreamInterface $body    Message body, if any.
     * @param array                           $headers Headers for the message, if any.
     *
     * @throws \InvalidArgumentException for any invalid value.
     */
    public function __construct($command)
    {
        $this->command = $command;
    }
    
    
    /**
     * @return CliParameterContainer
     */
    public function getParameters() : ParameterContainerInterface
    {
        
        if (is_null($this->parameters))
        {
            // build default parameter container from request
            $this->parameters = new CliParameterContainer();
        }
        
        return $this->parameters;
    }
    
    /**
     * @param CliParameterContainer $parameters
     */
    public function setParameters(ParameterContainerInterface $parameters)
    {
        $this->parameters = $parameters;
        
        return $this;
    }
    
    /**
     * Proxy to ParameterContainerInterface::getParam()
     *
     * @param $param    string      Parameter name
     * @param $param    mixed       Default value
     * @param $origin   string       Source name (for instance 'get' for HTTP param)
     *
     * @return ParameterContainerInterface|mixed
     */
    public function getParam($param = null, $default = null, $origin = 'cli')
    {
        return $this->getParameters()->get($param, $default, $origin);
    }
    
    /**
     * @return mixed HTTP method (GET, POST, PUT, DELETE) or CLI
     *
     * @deprecated
     */
    public function getMethod()
    {
        return 'CLI';
    }
    
    /**
     * Request route
     *
     * @return mixed
     *
     * @deprecated
     */
    public function getRoute()
    {
        return $this->command;
    }
    
    /**
     * @return null|string
     */
    public function getUri()
    {
        return $this->command;
    }
    
    /**
     * @param $route
     *
     * @return mixed
     */
    public function setRoute($route)
    {
        $this->command = $route;
    }
    
    /**
     * @param MatchedRoute $matchedRoute
     *
     * @return mixed
     */
    public function setMatchedRoute(MatchedRoute $matchedRoute)
    {
        $this->matchedRoute = $matchedRoute;
    }
    
    /**
     * @return MatchedRoute
     */
    public function getMatchedRoute()
    {
        return $this->matchedRoute;
    }
    
    
}
