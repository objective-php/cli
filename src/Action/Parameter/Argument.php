<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Cli\Action\Parameter;

/**
 * Class Argument
 *
 * Arguments are positional parameters. They should be handled after all Toggles and Params.
 *
 * @package ObjectivePHP\Application\Action\Parameter\Cli
 */
class Argument extends AbstractParameter
{
    /**
     * Argument constructor.
     *
     * @param string $name the argument (long) name
     * @param string $description
     * @param int    $options
     */
    public function __construct($name, $description = '', $options = 0)
    {
        $this->setName($name);
        $this->setDescription($description);
        $this->setOptions($options);
    }
    
    
    public function hydrate(array $argv): array
    {
        $value = null;
     
        
        if($this->getOptions() & self::MULTIPLE)
        {
            // assign all remaining arguments to the current one
            $this->setValue($argv);
            return [];
        }
        
        // assign first positional argument to current one
        if (isset($argv[0]))
        {
            $this->setValue($argv[0]);
            unset($argv[0]);
        }

        return array_values($argv);
    }
    
}
