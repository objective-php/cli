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
 * Interface ParameterInterface
 * @package ObjectivePHP\Cli\Action\Parameter
 */
interface ParameterInterface
{
    const MANDATORY = 1;
    const MULTIPLE = 2;

    /**
     * @return string
     */
    public function getDescription() : string;

    /**
     * @return string
     */
    public function getShortName() : string;

    /**
     * @return string
     */
    public function getLongName() : string;

    /**
     * @param array $argv
     * @return array
     */
    public function hydrate(array $argv) : array;

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return int
     */
    public function getOptions() : int;
    
}
