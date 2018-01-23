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
 * Class Toggle
 * @package ObjectivePHP\Cli\Action\Parameter
 */
class Toggle extends AbstractParameter
{
    /**
     * @param array $argv
     * @return array
     */
    public function hydrate(array $argv): array
    {
        $value = 0;
        
        // look for short name occurrences
        if ($short = $this->getShortName())
        {
            foreach ($argv as $i => $arg)
            {
                
                if (strpos($arg, '-') === 0 && $arg[1] != '-')
                {
                    $arg = substr($arg, 1);
                    $newArg = '';
                    
                    for($j = 0; $j < strlen($arg); $j++)
                    {
                        if($arg[$j] == $short)
                        {
                            $value += 1;
                        }
                        else {
                            $newArg .= $arg[$j];
                        }
                        
                    }
                    
                    if($newArg)
                    {
                        $argv[$i] = '-' . $newArg;
                    } else {
                        unset($argv[$i]);
                    }
                }
            }
        }
    
        // look for long name occurrences
        if ($long = $this->getLongName())
        {
            $pattern = '--' . $long;
            foreach ($argv as $i => $arg)
            {
                if ($arg == $pattern)
                {
                    $value += 1;
                    unset($argv[$i]);
                }
            }
        
        }
        
        $this->setValue($value);
        
        return array_values($argv);
        
    }
}
