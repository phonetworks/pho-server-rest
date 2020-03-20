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

class Router extends Invoke
{

    protected $store = []; 
    protected $selected = [];

    protected static function key(string $method, string $path): string
    {
        return sprintf("%s:%s", strtoupper($method), strtolower($path));
    }

    public function add(string $method = 'GET', string $path, /*?callable*/ $next)
    {
        $key = self::key($method, $path);
        $this->store[$key] = [$method, $path, $next];
        $this->selected[$key] = [$method, $path, $next];
        $this->collector->addRoute($method, $path, $next);
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

    public function list(): array
    {
        return $this->selected;
    }

    public function print(): void
    {
        print_r($this->selected);
    }

}