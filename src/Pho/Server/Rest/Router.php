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

use FastRoute\simpleDispatcher;
use Pho\Server\Rest\Controllers\AbstractController;
use Psr\Http\Message\ServerRequestInterface;
use Pho\Kernel\Kernel;
use FastRoute\Dispatcher;
use React\Http\Response;

/**
 * Determines routes
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class Router
{

    protected $kernel;
    protected $dispatcher; 
    protected $routes = [];
    protected $controllers = [];

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $routes_dir = __DIR__ . DIRECTORY_SEPARATOR . "Routes" . DIRECTORY_SEPARATOR;
        $this->initRoutes($routes_dir);
    }

    public function initRoutes(string $routes_dir): void
    {
        $reformat = function(string $file): string 
        {
            return str_replace(".php", "", lcfirst($file));
        };
        $route_files = scandir($routes_dir);
        foreach ($route_files as $file) {
            if(!in_array($file, [".", ".."])) {
                $this->routes[$reformat($file)] = include($routes_dir.$file);
            }
        }
    }

    public function compile(array $controllers): self
    {
        $this->controllers = $controllers;
        $routes = $this->routes;
        $resolvePath = function(string $path): string
        {
            // this lambda converts some common usages such as uuid
            // for unique cases, regex must be kept at the 
            // route level.
            return str_replace("{uuid}", "{uuid:[0-9a-fA-F]{32}}", $path);
        };
        $this->dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) use ($routes, $resolvePath) {
            foreach($routes as $controller => $handlers) {
                foreach($handlers as $handler => $route) {
                    $method = $route[0];
                    $path = $resolvePath($route[1]);
                    $r->addRoute($method, $path, "{$controller}::{$handler}");
                }
            }
        });
        return $this;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $method = $request->getMethod(); // GET or POST
        $path = $request->getUri()->getPath(); // the path
        $routeInfo = $this->dispatcher->dispatch($method, $path);   
        switch ($routeInfo[0]) {
            
            case Dispatcher::NOT_FOUND:
                $response = $this->controllers["kernel"]->fail("Not Found", 404);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                $response = $this->controllers["kernel"]->fail("Allowed Methods: ".join(', ', array_unique($allowedMethods)), 405);
                break;
                
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $this->kernel->logger()->info("Found: ".$handler . " --- ". print_r($vars, true));
                // ... call $handler with $vars
                // @todo This must be implemented
                list($controllerName, $methodName) = explode('::', $routeInfo[1]);
                if (null === $controller = $this->controllers[$controllerName] ?? null) {
                    throw new \RuntimeException("Controller $controllerName not found");
                }
                if (! method_exists($controller, $methodName)) {
                    throw new \RuntimeException("Method $methodName does not exist in controller $controllerName");
                }
                $response = new Response(200, AbstractController::HEADERS, null);
                $response = call_user_func_array([ $controller, $methodName ], array_merge([ $request, $response ], $vars));
                break;
        }

        return $response;
    }
}