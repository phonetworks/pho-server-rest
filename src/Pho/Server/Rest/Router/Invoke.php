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

use Psr\Http\Message\ServerRequestInterface;
use Pho\Server\Rest\Server;
use React\Http\Response;
use FastRoute\Dispatcher;

class Invoke extends Bootstrap
{
    public function __invoke(ServerRequestInterface $request)
    {
        echo "basladik\n";
        $method = $request->getMethod(); // GET or POST
        $path = $request->getUri()->getPath(); // the path
        echo $method."\n";
        echo $path."\n";
        $routeInfo = $this->dispatcher->dispatch($method, $path);   
        print_r($routeInfo);
        print_r(array_keys($this->controllers));
        echo Dispatcher::NOT_FOUND."\n";
        echo Dispatcher::METHOD_NOT_ALLOWED."\n";
        echo Dispatcher::FOUND."\n";
        switch ($routeInfo[0]) {
            
            case Dispatcher::NOT_FOUND:
                $response = $this->controllers["KernelController"]->fail(null, "Not Found", 404);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                $response = $this->controllers["KernelController"]->fail(null, "Allowed Methods: ".join(', ', array_unique($allowedMethods)), 405);
                break;
                
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $this->kernel->logger()->info("Found: ".$handler . " --- ". print_r($vars, true));
                // ... call $handler with $vars
                // @todo This must be implemented
                list($controllerName, $methodName) = explode('::', $routeInfo[1]);
                if (null === $controller = $this->controllers[$controllerName] ?? null) {
                    error_log("Controller $controllerName not found");
                    return $this->controllers['kernel']->fail();
                }
                if (! method_exists($controller, $methodName)) {
                    error_log("Method $methodName does not exist in controller $controllerName");
                    return $this->controllers['kernel']->fail();
                }
                $response = new Response;
                $response = call_user_func_array(
                    [ 
                        $controller->setExceptionHandler($response), 
                        $methodName 
                    ], 
                    array_merge([ $request, $response ], $vars)
                );
                break;
        }

        $response = $response->withHeader('Server', Server::NAME);

        return $response;
    }
}