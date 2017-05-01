<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Cli\Action;


use ObjectivePHP\Invokable\InvokableInterface;

/**
 * Interface CliActionInterface
 * @package ObjectivePHP\Cli\Action
 */
interface CliActionInterface extends InvokableInterface
{
    /**
     * @return array
     */
    public function getExpectedParameters();

    /**
     * @return string
     */
    public function getCommand() : string;

    /**
     * @return string
     */
    public function getUsage() : string;

    /**
     * @return bool
     */
    public function areUnexpectedParametersAllowed() : bool;
    
}
