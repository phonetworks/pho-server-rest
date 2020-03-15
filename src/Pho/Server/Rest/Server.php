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
use Psr\Http\Message\ServerRequestInterface;

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
        $this->router = new Router\Router($kernel);
        if(!isset($loop)) {
            $loop = \React\EventLoop\Factory::create();    
        }
        $this->loop = &$loop;
        
    }

    public function port(int $port = 0): int
    {
        if($port!=0)
            $this->port = $port;
        return $this->port;
    }

    public function host(string $host = ""): string
    {
        if(!empty($host))
            $this->host = $host;
        $this->host = $host;
    }

    public function version(): string
    {
        if(!preg_match("/^([0-9]+\.[0-9]+)\.[0-9]+$/", self::VERSION, $matches))
            throw new \Exception("Invalid Version");
        return $matches[1];
    }

    public function use(/*\Object*/ $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function serve(bool $blocking = true): void
    {
        $this->middlewares[] = $this->router;
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