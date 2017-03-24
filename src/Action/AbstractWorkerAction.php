<?php

/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Cli\Action;

use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Cli\Action\Parameter\Param;

/**
 * Class AbstractWorkerAction
 *
 * @package ObjectivePHP\Cli\Action
 */
abstract class AbstractWorkerAction extends AbstractCliAction
{
    /**
     * AbstractWorkerAction constructor.
     */
    public function __construct()
    {
        $this->expects(new Param(['d' => 'delay'], 'Delay between two loop (default 2 second)'));
        $this->expects(new Param(['i' => 'iteration'], 'Number of iterations (infinite loop by default)'));
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(...$args)
    {
        $app = array_shift($args);

        if (!$app instanceof ApplicationInterface) {
            throw new CliActionException(
                'Action must be invoked with an ApplicationInterface instance as first parameter'
            );
        }

        $this->setApplication($app);
        $this->setServicesFactory($app->getServicesFactory());
        $this->setEventsHandler($app->getEventsHandler());

        $iteration = $this->getParam('i', null);
        $delay = $this->getParam('d', 2);

        $this->loop($app, $delay, $iteration);
    }

    /**
     * Run the action in a loop
     *
     * @param ApplicationInterface $application
     * @param int                  $delay
     * @param null                 $iteration
     *
     * @return int|mixed
     */
    public function loop(ApplicationInterface $application, $delay = 1, $iteration = null)
    {
        $result = 0;

        $i = 0;
        while (true && (is_null($iteration) || $i < $iteration)) {
            $result = $this->run($application);

            $i++;

            if ($i < $iteration || is_null($iteration)) {
                sleep($delay);
            }
        }

        return $result;
    }
}
