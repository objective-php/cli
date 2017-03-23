<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace Tests\ObjectivePHP\Application\Action\Paramter\Cli;


use ObjectivePHP\Cli\Action\Parameter\Param;

class ParamTest extends \PHPUnit_Framework_TestCase
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
        
        $param = new Param($name);
        
        $argvAfterHydration = $param->hydrate($argv);
        
        $this->assertEquals($expectedValue, $param->getValue());
        $this->assertEquals($remainingArgv, $argvAfterHydration);
    }
    
    public function getDataForTestHydration()
    {
        return
            [
                [['-e', 'value1'], 'e', 'value1', []],
                [['-e', 'value1', '-v'], 'e', 'value1', ['-v']],
                [['--param', 'value1', '-v'], ['p' => 'param'], 'value1', ['-v']],
                [['--param=value1', '-v'], ['p' => 'param'], 'value1', ['-v']],
                [['--param=value1', '-p', 'value2', '-v'], ['p' => 'param'], 'value1', ['-v']],
            ];
    }
    
    public function testParamWithMultipleOption()
    {
        $param = new Param(['t' => 'test'], 'Test param', Param::MULTIPLE);
        
        $param->hydrate(['-t', 'test']);
        
        $this->assertInternalType('array', $param->getValue());
        $this->assertEquals(['test'], $param->getValue());
        
        $param->hydrate(['-t', 'test', '-t', 'other']);
        
        $this->assertInternalType('array', $param->getValue());
        $this->assertEquals(['test', 'other'], $param->getValue());
        
        
    }
    
}
