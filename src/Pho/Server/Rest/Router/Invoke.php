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
use Pho\Server\Rest\{Server, Utils};
use React\Http\Response;

class Invoke extends Bootstrap
{
    public function __invoke(ServerRequestInterface $request)
    {
        $method = $request->getMethod(); // GET or POST
        $path = $request->getUri()->getPath(); // the path
        $routeInfo = $this->dispatch($method, $path);   
        switch ($routeInfo[0]) {
            
            case self::NOT_FOUND:
                $response = $this->controllers["KernelController"]->fail(null, "Not Found", 404);
                break;
                
            case self::METHOD_NOT_ALLOWED:
                //$allowedMethods = $routeInfo[1];
                $allowedMethods = [];
                $response = $this->controllers["KernelController"]->fail(null, "Allowed Methods: ".join(', ', array_unique($allowedMethods)), 405);
                break;
                
            case self::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $key = $routeInfo[3]; // static::key($method, $routeInfo[3]);
                echo $key;
                
                if(is_string($routeInfo[1])) {
                    list($controllerName, $methodName) = explode('::', $routeInfo[1]);
                    
                    if (null === $controller = $this->controllers[$controllerName] ?? null) {
                        error_log("Controller $controllerName not found");
                        return $this->controllers['kernel']->fail();
                    }

                    if (! method_exists($controller, $methodName)) {
                        error_log("Method $methodName does not exist in controller $controllerName");
                        return $this->controllers['kernel']->fail();
                    }
                    
                    if($this->disabled($key)) {
                        error_log("Method $methodName is disabled in $controllerName");
                        return $this->controllers['kernel']->fail();
                    }
                    
                    if($this->locked($key)&&!Utils::isAdmin($request, $this->kernel)) {
                        error_log("Method $methodName in $controllerName requires admin priveleges");
                        return $this->controllers['kernel']->failAdminRequired(new Response);
                    }
                    
                    $response = new Response;
                    $response = call_user_func_array(
                        [ 
                            $controller->setExceptionHandler($response), 
                            $methodName 
                        ], 
                        array_merge([ $request, $response ], $vars)
                    );
                }   
                else {
                    $response = new Response;
                    $response = call_user_func_array(
                        $routeInfo[1], 
                        array_merge([ $request, $response ], $vars)
                    );
                }
                break;
        }

        $response = Utils::injectHeaders($response);

        return $response;
    }
}