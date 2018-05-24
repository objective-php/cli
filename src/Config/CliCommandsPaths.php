<?php

namespace ObjectivePHP\Cli\Config;

use ObjectivePHP\Config\Directive\AbstractScalarDirective;
use ObjectivePHP\Config\Directive\MultiValueDirectiveInterface;
use ObjectivePHP\Config\Directive\MultiValueDirectiveTrait;

/**
 * Class CliCommandsPaths
 *
 * @package ObjectivePHP\Cli\Config
 */
class CliCommandsPaths extends AbstractScalarDirective implements MultiValueDirectiveInterface
{
    use MultiValueDirectiveTrait;

    const KEY = 'cli.commands.paths';

    protected $key = self::KEY;
}
