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
class Server
{
    const VERSION = "2.0.0";
    const NAME = "PhoNetworks";

    protected $loop;
    protected $server;
    protected $router;
    protected $kernel;
    protected $controllers=[];
    protected $port = 80;
    protected $formable_nodes = [];
    protected $jsonp = false;
    protected $middlewares=[];
    protected $host = "0.0.0.0";

    public function __construct(Kernel &$kernel, ?LoopInterface &$loop = null)
    {
        $this->kernel = &$kernel;
        $this->router = new Router($kernel);
        if(!isset($loop)) {
            $loop = \React\EventLoop\Factory::create();    
        }
        $this->loop = &$loop;
    }

    public function bootstrap(): self
    {
        $controller_dir = __DIR__ . DIRECTORY_SEPARATOR . "Controllers";
        $this->withControllers($controller_dir);
        $this->router->bootstrap();
        return $this;
    }

    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    public function apiVersion(): string
    {
        if(!preg_match("/^([0-9]+\.[0-9]+)\.[0-9]+$/", self::VERSION, $matches))
            throw new \Exception("Invalid Version");
        return $matches[1];
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
     *
     *
     * @param string $controller_dir
     * @return self
     */
    public function withControllers(string $controller_dir): self
    {
        $jsonp = $this->jsonp;
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
        
        $locator = new \Zend\File\ClassFileLocator($controller_dir);
        foreach ($locator as $file) {
            $filename = str_replace($controller_dir . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $build($file->getClasses());
        }
        return $this;
    }

    public function respondInJsonp(): self
    {
        foreach($this->controllers as $key=>$class) {
            $this->controllers[$key]->respondInJsonp();
        }
        $this->jsonp = true;
    }

    public function respondInJson(): self
    {
        foreach($this->controllers as $key=>$class) {
            $this->controllers[$key]->respondInJson();
        }
        $this->jsonp = false;
    }

    public function withRoutes(string $directory): self
    {
        if(file_exists($directory))
            $this->router->bootstrap($directory);
        return $this;
    }

    public function withMiddleware(/*\Object*/ $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function serve(bool $blocking = true): void
    {
        $this->middlewares[] = $this->router->compile($this->controllers);
        $server = new \React\Http\Server(
            $this->middlewares
        );
        $uri = sprintf("%s:%s", $this->host, (string) $this->port);
        $socket = new \React\Socket\Server($uri, $this->loop);
        error_log("Starting on {$uri}");
        $server->listen($socket);
        if($blocking)
            $this->loop->run();
    }
}