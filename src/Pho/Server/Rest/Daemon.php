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
use React\EventLoop\LoopInterface;

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

    public function __construct(Kernel $kernel, LoopInterface $loop = null)
    {
        $this->server = new Server($loop);
        $this->kernel = $kernel;
        $this->initControllers();
        Router::init($this->server, $this->controllers);
    }

    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @todo I'm not sure what this is, find out!
     *
     * @param array $pairs
     * @return void
     */
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

    /**
     * @todo this is very dirty. routes and controllers must work in sync.
     *
     * @param string $base
     * @param boolean $jsonp
     * @return void
     */
    protected function initControllers(string $base = __DIR__,  bool $jsonp = false): void
    {
        $build = function(array $classes) use ($jsonp): void
        {
            foreach($classes as $class) {
                $ref = new \ReflectionClass($class);
                if(!$ref->isSubclassOf(Controllers\AbstractController::class) || $ref->isAbstract() /*$class == Controllers\AbstractController::class */)
                    continue;
                $controller_key = strtolower(str_replace("Controller", "", $ref->getShortName()));
                $this->controllers[$controller_key] = new $class($this->kernel, $jsonp);
            }
        };
        $controller_dir = $base . DIRECTORY_SEPARATOR . "Controllers";
        $locator = new \Zend\File\ClassFileLocator($controller_dir);
        foreach ($locator as $file) {
            $filename = str_replace($controller_dir . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $build($file->getClasses());
        }
    }

    public function serve(bool $blocking = true)
    {
        $this->server->setPort($this->port)->serve($blocking);
    }
}

