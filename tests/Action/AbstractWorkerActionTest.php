<?php

/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace Tests\ObjectivePHP\Application\Action;

use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Cli\Action\AbstractWorkerAction;
use ObjectivePHP\Cli\Action\CliActionException;
use ObjectivePHP\Cli\Request\CliRequest;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractWorkerTest
 *
 * @package Tests\Fei\Worker
 */
class AbstractWorkerActionTest extends TestCase
{
    /**
     * @dataProvider dataForTestLoopIteration
     */
    public function testLoopIteration($iteration, $result)
    {
        $application = $this->createMock(ApplicationInterface::class);

        global $i;
        $i = 0;

        /** @var AbstractWorkerAction $worker */
        $worker = new class extends AbstractWorkerAction {
            public function run(ApplicationInterface $app)
            {
                global $i;
                return ++$i;
            }
        };

        $this->assertEquals($result, $worker->loop($application, 0, $iteration));
    }

    public function dataForTestLoopIteration()
    {
        return [
            [1, 1],
            [2, 2],
            [3, 3],
            [4, 4],
            [5, 5],
            [6, 6],
            [7, 7],
            [8, 8],
            [9, 9],
            [10, 10]
        ];
    }

    public function testDefaultParameters()
    {
        global $parameters;

        /** @var AbstractWorkerAction $worker */
        $worker = new class extends AbstractWorkerAction {
            public function run(ApplicationInterface $app)
            {
                global $parameters;
                $parameters = $this->getExpectedParameters();
            }
        };

        $worker->loop($this->createMock(ApplicationInterface::class), 0, 1);

        $this->assertArrayHasKey('d', $parameters);
        $this->assertArrayHasKey('delay', $parameters);
        $this->assertArrayHasKey('i', $parameters);
        $this->assertArrayHasKey('iteration', $parameters);

        $this->assertEquals(0, $parameters['d']->getOptions());
        $this->assertEquals(0, $parameters['i']->getOptions());
    }

    public function testLoopInfiniteIteration()
    {
        global $i;
        $i = 0;

        /** @var AbstractWorkerAction $worker */
        $worker = new class extends AbstractWorkerAction {
            public function run(ApplicationInterface $app)
            {
                global $i;
                if ($i >= 2) {
                    throw new \Exception();
                }
                return ++$i;
            }
        };

        $begin = microtime(true);
        try {
            $worker->loop($this->createMock(ApplicationInterface::class), 1);
        } catch (\Exception $e) {
        }
        $time = microtime(true) - $begin;

        $this->assertGreaterThanOrEqual(2, $time);
    }

    public function testInvoke()
    {
        $request = (new CliRequest('test'));
        $request->getParameters()->setCli(['delay' => 0, 'iteration' =>1, 'd' => 0, 'i' =>1]);

        $application = $this->createMock(ApplicationInterface::class);
        $application->method('getRequest')->willReturn($request);

        global $delay, $iteration;

        $delay = 0;
        $iteration = 0;

        /** @var AbstractWorkerAction $worker */
        $worker = new class extends AbstractWorkerAction {
            public function run(ApplicationInterface $app)
            {
                global $delay, $iteration;

                $delay = $this->getParam('delay', 'test');
                $iteration = $this->getParam('iteration', 'test');
            }
        };

        $worker($application);

        $this->assertEquals(0, $delay);
        $this->assertEquals(1, $iteration);
    }

    public function testInvokeFailWithNoApplicationAsFirstParam()
    {
        /** @var AbstractWorkerAction $worker */
        $worker = new class extends AbstractWorkerAction {
            public function run(ApplicationInterface $app)
            {
            }
        };

        $this->expectException(CliActionException::class);
        $this->expectExceptionMessage(
            'Action must be invoked with an ApplicationInterface instance as first parameter'
        );

        $worker(0);
    }
}
