<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 19/03/2017
 * Time: 16:35
 */

namespace ObjectivePHP\Cli\Action;


use League\CLImate\CLImate;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Cli\Action\Parameter\Argument;
use ObjectivePHP\Cli\Action\Parameter\Toggle;
use ObjectivePHP\Cli\Router\CliRouter;

class Usage extends AbstractCliAction
{
    protected $command = 'usage';
    protected $description = 'List available commands and parameters';

    /**
     * @var CliRouter
     */
    protected $router;

    /**
     *
     */
    public function __construct()
    {
        $this->expects(new Toggle('v', 'Detailed output'));
        $this->expects(new Argument('command', 'Command to get usage of'));
    }


    /**
     * @param ApplicationInterface $app
     */
    public function run(ApplicationInterface $app)
    {
        $requestedCommand = ltrim($app->getRequest()->getRoute());

        $c = new CLImate();

        $c->underline('<bold>Objective PHP</bold> Command Line Interface')->br();
        if ($requestedCommand !== 'usage') {
            if ($requestedCommand) $c->out(sprintf("Unknown command <red>%s</red>. List of available commands:", $requestedCommand));
            else $c->out(sprintf("<red>No command</red> has been specified. List of available commands:", $requestedCommand));
        } elseif(!$this->getParam('command')) $c->bold('List of available commands');

        $c->br();

        $verbose = ($this->getParam('command') || $this->getParam('v'));
        if (!$verbose) {
            // compute padding width
            $maxCommandLength = 0;
            /** @var CliActionInterface $command */
            foreach ($this->getRouter()->getRegisteredCommands() as $command) {
                if (!is_object($command)) $command = new $command;
                $commandLength = strlen($command->getCommand());
                $maxCommandLength = max($commandLength, $maxCommandLength);
            }
            $p = $c->padding($maxCommandLength + 15, ' ');
        }

        /** @var CliActionInterface $command */
        foreach ($this->getRouter()->getRegisteredCommands() as $command)
        {
            if (!is_object($command)) $command = new $command;

            if ($this->getParam('command') && ($this->getParam('command') != $command->getCommand())) continue;

            if ($verbose) {
                echo $command->getUsage() . PHP_EOL;
            } else {
                $p->label("\t - <green>" . $command->getCommand() . "</green> ")->result($command->getDescription());
            }
        }

        $c->br();
    }

    /**
     * @return CliRouter
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param $this $router
     */
    public function setRouter(CliRouter $router)
    {
        $this->router = $router;

        return $this;
    }

}
