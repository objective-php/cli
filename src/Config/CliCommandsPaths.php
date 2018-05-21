<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 28/08/2017
 * Time: 16:31
 */

namespace ObjectivePHP\Cli\Config;


use ObjectivePHP\Config\Directive\AbstractScalarDirective;
use ObjectivePHP\Config\Directive\MultiValueDirectiveInterface;
use ObjectivePHP\Config\StackedValuesDirective;

class CliCommandsPaths extends AbstractScalarDirective implements MultiValueDirectiveInterface
{
    const KEY = 'cli.commands.paths';

    protected $key = self::KEY;


}
