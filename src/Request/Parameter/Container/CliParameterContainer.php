<?php

namespace ObjectivePHP\Cli\Request\Parameter\Container;

use ObjectivePHP\Message\Request\Parameter\Container\ParameterContainerInterface;
use ObjectivePHP\Message\Request\RequestInterface;
use ObjectivePHP\Primitives\Collection\Collection;

/**
 * Class CliParameterContainer
 *
 * @package ObjectivePHP\Message\Request\Parameter\Container
 */
class CliParameterContainer implements ParameterContainerInterface
{
    
    /**
     * @var RequestInterface
     */
    protected $request;
    
    /**
     * @var Collection
     */
    protected $params;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->params = new Collection([
            'cli' => new Collection(),
            'env' => new Collection()
        ]);
        
        $this->setEnv($_ENV);
    }
    
    /**
     * @param $envVars
     */
    public function setEnv($envVars)
    {
        $this->params['env'] = Collection::cast($envVars);
        
        return $this;
    }
    
    public function setCli($cliVars)
    {
        $this->params['cli'] = Collection::cast($cliVars);
    }
    
    /**
     * @param      $param
     * @param null $default
     *
     * @return mixed
     */
    public function fromCli($param, $default = null)
    {
        return $this->get($param, $default, 'cli');
    }
    
    /**
     * @param        $param
     * @param null   $default
     * @param string $origin
     *
     * @return mixed
     */
    public function get($param, $default = null, $origin = 'cli')
    {
        return $this->params->get($origin)->get($param, $default);
    }
    
    /**
     * @param      $var
     * @param null $default
     *
     * @return mixed
     */
    public function fromEnv($var, $default = null)
    {
        return $this->get($var, $default, 'env');
    }
    
}
