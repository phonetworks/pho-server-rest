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

use React\EventLoop\LoopInterface;

/**
 * REST Server
 * 
 * Extends \CapMousse\ReactRestify\Server with version and server
 * info. Also, wraps route add calls.
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class Server
{
    const VERSION = "2.0.0";
    const NAME = "PhoNetworks";

    protected $loop;
    protected $port = 80;

    public function __construct(LoopInterface $loop = null)
    {
        if(isset($loop)) {
            $loop = \React\EventLoop\Factory::create();    
        }
        $this->loop = $loop;
        
    }


    public function apiVersion(): string
    {
        if(!preg_match("/^([0-9]+\.[0-9]+)\.[0-9]+$/", self::VERSION, $matches))
            throw new \Exception("Invalid Version");
        return $matches[1];
    }

    public function setPort(int $port): Server
    {
        $this->port = $port;
        return $this;
    }

    public function serve(bool $blocking = true): void
    {
        $server = new \React\Http\Server($requestHandler);
        $socket = new \React\Socket\Server($this->port, $this->loop);
        $server->listen($socket);
        if($blocking)
            $this->loop->run();
    }


    /**
     * Amplifies router add methods
     *
     * Wraps them so that they are added not only with the given
     * query, but also with a query that adds version info.
     * 
     * Currently handling; GET, POST, PUT, DELETE
     * 
     * @param string $method Method name.
     * @param string $matcher The query to match with.
     * @param Callable $callable Callable to call with a match.
     * 
     * @return mixed
     */
  /*  private function query(string $method, string $matcher, Callable $callable) //: mixed
    {
        $method = strtolower($method);
        $this->$method($matcher, $callable);
        $version = sprintf("v%s/", $this->apiVersion());
        return $this->$method($version.$matcher, $callable);
    }
*/
}