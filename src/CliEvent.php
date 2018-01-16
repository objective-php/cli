<?php

namespace ObjectivePHP\Cli;

/**
 * Interface CliEvent
 * @package ObjectivePHP\Cli
 */
interface CliEvent
{
    const BEFORE_RUN_ACTION = 'cli.action.run.before';

    const AFTER_RUN_ACTION = 'cli.action.run.after';
}
