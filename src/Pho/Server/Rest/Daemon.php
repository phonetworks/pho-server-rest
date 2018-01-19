<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pho\Server\Rest;

use Pho\Kernel\Kernel;

/**
 * The async/event-driven REST server daemon
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class Daemon
{
    protected $server;
    protected $kernel;
    protected $controllers=[];
    protected $port = 80;
    protected $formable_nodes = [];

    public function __construct(Kernel $kernel)
    {
        $this->server = new Server();
        $this->kernel = $kernel;
        $this->initControllers();
        Router::init($this->server, $this->controllers);
    }

    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    public function setFormableNodes(array $pairs): void
    {
        $set = function(string $key, string $class): void
        {
            $this->formable_nodes[$key] = $class;
        };
        foreach($pairs as $pair) {
            $set($pair[0], $pair[1]);
        }
    }

    protected function initControllers(): void
    {
        $build = function(array $classes): void
        {
            foreach($classes as $class) {
                $ref = new \ReflectionClass($class);
                if(!$ref->isSubclassOf(Controllers\AbstractController::class) || $class == Controllers\AbstractController::class)
                    continue;
                $controller_key = strtolower(str_replace("Controller", "", $ref->getShortName()));
                $this->controllers[$controller_key] = new $class($this->kernel);
            }
        };
        $controller_dir = __DIR__ . DIRECTORY_SEPARATOR . "Controllers";
        $locator = new \Zend\File\ClassFileLocator($controller_dir);
        foreach ($locator as $file) {
            $filename = str_replace($controller_dir . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $build($file->getClasses());
        }
    }

    public function serve()
    {
        $this->server->listen($this->port, "0.0.0.0");
    }
}

