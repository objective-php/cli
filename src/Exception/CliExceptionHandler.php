<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 30/08/2017
 * Time: 13:49
 */

namespace ObjectivePHP\Cli\Exception;


use League\CLImate\CLImate;

class CliExceptionHandler
{
    function handle(\Throwable $exception)
    {
        $c = new CLImate();

        $c->br();
        $c->border();
        $c->bold('Latest exception: ');
        $c->border();
        $previous = false;
        do {
            $this->renderException($exception, $previous);
            $previous = true;
        } while ($exception = $exception->getPrevious());
    }


    protected function renderException(\Throwable $exception, $isPrevious)
    {
        $c = new CLImate();
        $c->br();
        if ($isPrevious) {
            $c->border();
            $c->bold('Previous exception:');
            $c->border();
        }

        $c->bold('Message: ' . $exception->getMessage());
        $c->lightGray('Type: ' . get_class($exception));
        $c->comment('thrown from ' . str_replace(getcwd() . '/', '',
                $exception->getFile()) . ':' . $exception->getLine());
        $c->br();
        foreach ($exception->getTrace() as $i => $step) {
            $call = $step['class'] ? $step['class'] . $step['type'] . $step['function'] : $step['function'];

            $args = [];
            foreach ($step['args'] as $arg) {
                if (is_object($arg)) {
                    $args[] = '<cyan>' . get_class($arg) . '</cyan>';
                } elseif (is_numeric($arg)) {
                    $args[] = '<blue>' . $arg . '</blue>';
                } elseif (is_string($arg)) {
                    $args[] = '<red>"' . $arg . '"</red>';
                } elseif (is_array($arg)) {
                    $args[] = '<light_magenta>array(' . count($arg) . ')</light_magenta>';
                } elseif (is_resource($arg)) {
                    $args[] = '<yellow>ressource (' . get_resource_type($arg) . ')</yellow>';
                } else {
                    $args[] = $arg;
                }
            }


            $call .= '(' . implode(', ', $args) . ')';

            $c->tab()->inline('<bold>#' . $i . '</bold> ')->grey($call);
            $c->tab();
            $c->lightGreen('called from ' . str_replace(getcwd() . '/', '', $step['file']) . ':' . $step['line']);
            $c->br();
        }

    }
}
