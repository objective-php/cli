<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace Tests\ObjectivePHP\Application\Action\Paramter\Cli;


use ObjectivePHP\Cli\Action\Parameter\Argument;

class ArgumentTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @dataProvider getDataForTestHydration
     */
    public function testHydration($argv, $name, $expectedValue, $remainingArgv)
    {
        if ($expectedValue instanceof \Exception)
        {
            $this->expectException($expectedValue);
        }
        
        $param = new Argument($name);
        
        $cliAfterHydration = $param->hydrate($argv);
        
        $this->assertEquals($expectedValue, $param->getValue());
        $this->assertEquals($remainingArgv, $cliAfterHydration);
    }
    
    public function getDataForTestHydration()
    {
        return
            [
                [['filename.php', 'other'], 'file', 'filename.php', ['other']],
            ];
    }
    
}
