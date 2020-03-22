<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pho\Server\Rest\Router;

class Select extends Invoke
{

    protected $store = []; 
    protected $selected = [];
    protected $routes = [];

    protected function route(string $method, string $pattern, string $key): void
    {
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
        $this->routes[\strtoupper($method)][$pattern] = $key;
    }

    public function add(string $method = 'GET', string $path, /*?callable*/ $next): void
    {
        $key = self::key($method, $path);
        $this->store[$key] = [$method, $path, $next];
        $this->selected[$key] = [$method, $path, $next];
        //$this->collector->addRoute($method, $path, $next);
        $this->route($method, $path, $key);
    }

    protected function dispatch(string $uri)
    {
        foreach ($this->store as $pattern => $callback) {
            $pattern = $store[1];
            $callback = $store[2];
            if (preg_match($pattern, $uri, $params) === 1) {
                array_shift($params);
                return call_user_func_array($callback, array_values($params));
            }
        }
    }

    public function all(): self
    {
        $this->selected = $this->store;
        return $this;
    }

    public function none(): self
    {
        $this->selected = [];
        return $this;
    }

    public function only(...$routes): self
    {
        $this->none();
        foreach($routes as $route) {
            $key = self::key($route[0], $route[1]);
            if(isset($this->store[$key]))
                $this->selected[$key] = $this->store[$key];
        }
        return $this;
    }

    public function but(...$routes): self
    {
        foreach($routes as $route) {
            $key = self::key($route[0], $route[1]);
            if(isset($this->selected[$key]))
                unset($this->selected[$key]);
        }
        return $this;
    }

}