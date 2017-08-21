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

/**
 * REST Server
 * 
 * Extends \CapMousse\ReactRestify\Server with version and server
 * info. Also, wraps route add calls.
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class Server extends \CapMousse\ReactRestify\Server
{
    const VERSION = "0.1.0";
    const NAME = "PhoNetworks";

    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);
    }

/*
    protected function apiVersion(): string
    {
        if(!preg_match("/^([0-9]+\.[0-9]+)\.[0-9]+$/", self::VERSION, $matches))
            throw new \Exception("Invalid Version");
        return $matches[1];
    }

    public function get(string $matcher, Callable $callable) //: mixed
    {
        return $this->query
    }
*/
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