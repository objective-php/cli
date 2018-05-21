<?php

namespace Tests\ObjectivePHP\Application\Action;

use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Cli\Action\AbstractCliAction;
use ObjectivePHP\Cli\Event\CliEvent;
use ObjectivePHP\Events\EventsHandler;

/**
 * Class AbstractCliActionTest
 */
class AbstractCliActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function runMustTriggerBeforeAndAfterEvent()
    {
        $application = $this->createMock(ApplicationInterface::class);

        $action = new class extends AbstractCliAction
        {
            public function run(ApplicationInterface $app)
            {
            }
        };

        $spy = $this->createMock(EventsHandler::class);
        $spy
            ->expects($this->exactly(2))
            ->method('trigger')
            ->withConsecutive(
                [CliEvent::BEFORE_RUN_ACTION, $action],
                [CliEvent::AFTER_RUN_ACTION, $action]
            );

        $application
            ->method('getEventsHandler')
            ->willReturn($spy);

        $action->run($application);
    }
}
