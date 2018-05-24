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
use PHPUnit\Framework\TestCase;

class ArgumentTest extends TestCase
{

    /**
     * @dataProvider getDataForTestHydration
     */
    public function testHydration($argv, $name, $options, $expectedValue, $remainingArgv)
    {
        if ($expectedValue instanceof \Exception)
        {
            $this->expectException($expectedValue);
        }

        $param = new Argument($name, '', $options);

        $cliAfterHydration = $param->hydrate($argv);

        $this->assertEquals($expectedValue, $param->getValue());
        $this->assertEquals($remainingArgv, $cliAfterHydration);
    }

    public function getDataForTestHydration()
    {
        return
            [
                [['filename.php', 'other'], ['f' => 'file'], 0, 'filename.php', ['other']],
                [['filename.php', 'other.php'], 'file', Argument::MULTIPLE, ['filename.php', 'other.php'], []],
            ];
    }

}
