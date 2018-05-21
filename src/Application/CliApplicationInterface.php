<?php
/**
 * Created by PhpStorm.
 * User: gde
 * Date: 15/05/2018
 * Time: 11:57
 */

namespace ObjectivePHP\Cli\Application;

use Composer\Autoload\ClassLoader;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Application\Middleware\MiddlewareRegistry;
use ObjectivePHP\Primitives\Collection\Collection;
use ObjectivePHP\Router\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;


/**
 * Class AbstractApplication
 *
 * @package ObjectivePHP\Application
 */
interface CliApplicationInterface extends ApplicationInterface
{


}