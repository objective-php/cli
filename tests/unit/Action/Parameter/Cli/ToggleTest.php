<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace Tests\ObjectivePHP\Application\Action\Paramter\Cli;


use ObjectivePHP\Cli\Action\Parameter\Toggle;
use PHPUnit\Framework\TestCase;

class ToggleTest extends TestCase
{

    /**
     * @dataProvider getDataForTestHydration
     */
    public function testHydration($argv, $name, $expectedValue, $remainingArgv)
    {
        if ($expectedValue instanceof \Exception) {
            $this->expectException($expectedValue);
        }

        $param = new Toggle($name);

        $argvAfterHydration = $param->hydrate($argv);

        $this->assertEquals($expectedValue, $param->getValue());
        $this->assertEquals($remainingArgv, $argvAfterHydration);
    }

    public function getDataForTestHydration()
    {
        return
            [
                [['-v'], 'v', 1, []],
                [['-vv'], 'v', 2, []],
                [['-v', 'arg1'], 'v', 1, ['arg1']],
                [['-ev', 'arg1'], 'v', 1, ['-e', 'arg1']],
                [['-evv', 'arg1'], 'v', 2, ['-e', 'arg1']],
                [['-vev', 'arg1'], 'v', 2, ['-e', 'arg1']],
                [['arg1', '-vev', 'arg2', '-vv', '-v'], 'v', 5, ['arg1', '-e', 'arg2']],
                [['-vev', 'arg1', '-v'], 'v', 3, ['-e', 'arg1']],
                [['-vev', 'arg1', '-v', '--verbose', 'arg2'], ['v' => 'verbose'], 4, ['-e', 'arg1', 'arg2']],
                [['-vev', 'arg1', '-v', '--verbose', 'arg 2'], ['v' => 'verbose'], 4, ['-e', 'arg1', 'arg 2']],
            ];
    }

    public function testHydrationChain()
    {
        $param1 = new Toggle(['v' => 'verbose']);
        $param2 = new Toggle('e');

        $argv = ['-vev', 'arg1'];

        $argv = $param1->hydrate($argv);
        $this->assertEquals(['-e', 'arg1'], $argv);
        $this->assertEquals(2, $param1->getValue());
        $argv = $param2->hydrate($argv);
        $this->assertEquals(['arg1'], $argv);

    }

}
