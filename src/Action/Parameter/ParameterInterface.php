<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Cli\Action\Parameter;


interface ParameterInterface
{
    
    const MANDATORY = 1;
    const MULTIPLE = 2;
    
    public function getDescription() : string;
    
    public function getShortName() : string;
    
    public function getLongName() : string;
    
    public function hydrate(array $argv) : array;
    
    public function getValue();
    
    public function getOptions() : int;
    
}
