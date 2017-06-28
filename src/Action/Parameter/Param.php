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
        $args     = $argv;
        $length   = count($args);
        
        // look for short name occurrences
        if ($short = $this->getShortName()) {
            for ($i = 0; $i < $length; $i++) {
                $arg = $args[$i];
                
                if (strpos($arg, '-' . $short . '=') === 0) {
                    if ($multiple) {
                        $value[] = explode('=', $arg, 2)[1];
                    } else {
                        $value = explode('=', $arg, 2)[1];
                    }
                    unset($argv[$i]);
                    
                } elseif ($arg === '-' . $short) {
                    if (!array_key_exists($i + 1, $argv)) {
                        throw new ParameterException(sprintf('Missing value for parameter "-%s"', $short));
                    }
                    
                    if ($multiple) {
                        $value[] = $argv[$i + 1];
                    } else {
                        $value = $argv[$i + 1];
                    }
                    unset($argv[$i], $argv[$i + 1]);
                    
                    // skip next entry, as it is the value for this param
                    $i++;
                }
            }
            
        }
        
        // look for long name occurrences
        if ($long = $this->getLongName()) {
            $args   = array_values($argv);
            $length = count($args);
            
            for ($i = 0; $i < $length; $i++) {
                
                $arg = $args[$i];
                
                if (strpos($arg, '--' . $long . '=') === 0) {
                    if ($multiple) {
                        $value[] = explode('=', $arg, 2)[1];
                    } else {
                        $value = explode('=', $arg, 2)[1];
                    }
                    
                    unset($argv[$i]);
                    
                } elseif ($arg === '--' . $long) {
                    
                    if (!array_key_exists($i + 1, $argv)) {
                        if ($long == 'offset') {
                            var_dump($arg);
                            var_dump($value);
                        }
                        throw new ParameterException(sprintf('Missing value for parameter "--%s"', $long));
                    }
                    
                    if ($multiple) {
                        $value[] = $argv[$i + 1];
                    } else {
                        $value = $argv[$i + 1];
                    }
                    
                    unset($argv[$i], $argv[$i + 1]);
                    // skip next entry, as it is the value for this param
                    $i++;
                }
            }
        }
        
        
        $this->setValue($value);
        
        return array_values($argv);
    }
}
