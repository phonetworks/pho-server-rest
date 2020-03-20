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
    protected $cors = [
        'allow_credentials' => true,
        'allow_origin'      => ["*"],
        'allow_methods'     => ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS', 'PATCH'],
        'allow_headers'     => ['DNT','X-Custom-Header','Keep-Alive','User-Agent','X-Requested-With','If-Modified-Since','Cache-Control','Content-Type','Content-Range','Range', 'Origin', 'Accept', 'Authorization'],
        'expose_headers'    => ['DNT','X-Custom-Header','Keep-Alive','User-Agent','X-Requested-With','If-Modified-Since','Cache-Control','Content-Type','Content-Range','Range', 'Origin', 'Accept', 'Authorization'],
        'max_age'           => 60 * 60 * 24 * 14, // preflight request is valid for 14 days
    ];

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

    public function cors(?array $params = null): array
    {
        if(!is_null($params))
            $this->cors = array_merge($this->cors, $params);
        return $this->cors;
    }

    public function routes(): Router\Router
    {
        return $this->router;
    }

    public function serve(bool $blocking = true): void
    {
        $server = new \React\Http\Server(
            array_merge($this->middlewares, [$this->router->bootstrap()])
        );
        $uri = sprintf("%s:%s", $this->host, (string) $this->port);
        $socket = new \React\Socket\Server($uri, $this->loop);
        error_log("Starting on {$uri}");
        $server->listen($socket);
        if($blocking)
            $this->loop->run();
    }
}