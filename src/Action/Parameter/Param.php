<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Cli\Action\Parameter;


class Param extends AbstractParameter
{
    
    
    public function hydrate(array $argv): array
    {
        $multiple = $this->getOptions() & self::MULTIPLE;
        $value    = $multiple ? [] : '';
    
        // look for short name occurrences
        if ($short = $this->getShortName())
        {
            foreach ($argv as $i => $arg)
            {
                if (strpos($arg, '-' . $short . '=') === 0)
                {
                    if ($multiple)
                    {
                        $value[] = explode('=', $arg, 2)[1];
                    }
                    else $value = explode('=', $arg, 2)[1];
                
                    unset($argv[$i]);
                }
                elseif ($arg == '-' . $short)
                {
                    if (!isset($argv[$i + 1]))
                    {
                        throw new ParameterException(sprintf('Missing value for parameter "-%s"', $short));
                    }
                    if ($multiple)
                    {
                        $value[] = $argv[$i + 1];
                    }
                    else $value = $argv[$i + 1];
                    unset($argv[$i], $argv[$i + 1]);
                }
            }
        
        }
        
        // look for long name occurrences
        if ($long = $this->getLongName())
        {
            foreach ($argv as $i => $arg)
            {
                if (strpos($arg, '--' . $long . '=') === 0)
                {
                    if ($multiple)
                    {
                        $value[] = explode('=', $arg, 2)[1];
                    }
                    else $value = explode('=', $arg, 2)[1];
                    
                    unset($argv[$i]);
                }
                else if ($arg == '--' . $long)
                {
                    if (!isset($argv[$i + 1]))
                    {
                        throw new ParameterException(sprintf('Missing value for parameter "--%s"', $long));
                    }
                    if ($multiple)
                    {
                        $value[] = $argv[$i + 1];
                    }
                    else $value = $argv[$i + 1];
                    
                    unset($argv[$i], $argv[$i + 1]);
                }
            }
        }
        
        $this->setValue($value);
        
        return array_values($argv);
    }
}
