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
use Psr\Http\Message\ServerRequestInterface;
use Pho\Kernel\Kernel;

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

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $routes_dir = __DIR__ . DIRECTORY_SEPARATOR . "Routes" . DIRECTORY_SEPARATOR;
        $this->initRoutes($routes_dir);
    }

    private function initRoutes(string $routes_dir): void
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

    public function compile(): self
    {
        $routes = $this->routes;
        $resolvePath = function(string $path): string
        {
            // this lambda converts some common usages such as uuid
            // for unique cases, regex must be kept at the 
            // route level.
            return str_replace("{uuid}", "{uuid:[0-9a-fA-F]{32}}");
        };
        $this->dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) use ($routes) {
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
        $routeInfo = $this->dipatcher->dispatch($method, $path);   
        switch ($routeInfo[0]) {
            /*
            case FastRoute\Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                break;
                */
            case FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $this->logger->info("Found: ".$handler . " --- ". print_r($vars, true));
                // ... call $handler with $vars
                break;         
        }
    }


    ///// ANYTHING BELOW THIS BELONGS TO PHO-SERVER-REST 1.0.0
    ///// ONCE THE ROUTES ARE COPIED
    ///// ALL BELOW SHOULD BE REMOVED

    public static function init(Server $server, array $controllers): void
    {
        
        self::initPut($server, $controllers);
        self::initPost($server, $controllers);
        self::initDelete($server, $controllers);
        self::initGet($server, $controllers);
    }

    public static function initGet(Server $server, array $controllers): void
    {
        $server->get('edges', [$controllers["cypher"], "matchEdges"]);
        $server->get('nodes', [$controllers["cypher"], "matchNodes"]);
        
        $server->get('edge/{uuid}', [$controllers["edge"], "get"])
            ->where('uuid', '[0-9a-fA-F]{32}');
        $server->get('{uuid}', [$controllers["node"], "get"])
            ->where('uuid', '[0-9a-fA-F]{32}');
            /*
        $server->get('{uuid}/edges', [$controllers["node"], "getAllEdges"])
            ->where('uuid', '[0-9a-fA-F]{32}');*/
        $server->get('{uuid}/edges/getters', [$controllers["node"], "getGetterEdges"])
            ->where('uuid', '[0-9a-fA-F]{32}');
        $server->get('{uuid}/edges/setters', [$controllers["node"], "getSetterEdges"])
            ->where('uuid', '[0-9a-fA-F]{32}');
        $server->get('{uuid}/edges/all', [$controllers["node"], "getAllEdges"])
            ->where('uuid', '[0-9a-fA-F]{32}');
        $server->get('{uuid}/edges/in', [$controllers["node"], "getIncomingEdges"])
            ->where('uuid', '[0-9a-fA-F]{32}');
        $server->get('{uuid}/edges/out', [$controllers["node"], "getOutgoingEdges"])
            ->where('uuid', '[0-9a-fA-F]{32}');
        /*$server->get('{uuid1}/edges_from/{uuid2}', [$controllers["node"], "getEdgesFrom"])
            ->where('uuid1', '[0-9a-fA-F]{32}')
            ->where('uuid2', '[0-9a-fA-F]{32}');
        $server->get('{uuid1}/edges/to/{uuid2}', [$controllers["node"], "getEdgesTo"])
            ->where('uuid1', '[0-9a-fA-F]{32}')
            ->where('uuid2', '[0-9a-fA-F]{32}');
            */
        $server->get('{uuid}/attributes', [$controllers["entity"], "getAttributes"])
            ->where('uuid', '[0-9a-fA-F]{32}');
        $server->get('{uuid}/type', [$controllers["entity"], "getEntityType"])
            ->where('uuid', '[0-9a-fA-F]{32}');
        $server->get('{uuid}/attribute/{key}', [$controllers["entity"], "getAttribute"])
            ->where('uuid', '[0-9a-fA-F]{32}');
        $server->get('{uuid}/{edge}', [$controllers["node"], "getEdgesByClass"])
            ->where('uuid', '[0-9a-fA-F]{32}')
            ->where('edge', '[a-zA-Z_]+');
        $server->get('{method}', [$controllers["kernel"], "getStatic"])
            ->where('method', '[a-zA-Z]+');

        
    }

    public static function initPut(Server $server, array $controllers): void
    {
        $server->put('{uuid}/attribute/{key}', [$controllers["entity"], "setAttribute"])
            ->where('uuid', '[0-9a-fA-F]{32}');
        
    }

    public static function initPost(Server $server, array $controllers): void
    {
        $server->post('actor', [$controllers["kernel"], "createActor"]);
        $server->post('{uuid}/attribute/{key}', [$controllers["entity"], "setAttribute"])
            ->where('uuid', '[0-9a-fA-F]{32}');
        $server->post('{uuid}/{edge}', [$controllers["node"], "createEdge"])
            ->where('uuid', '[0-9a-fA-F]{32}')
            ->where('edge', '[a-z]+');
    }

    public static function initDelete(Server $server, array $controllers): void
    {
        $server->delete('{uuid}', [$controllers["entity"], "delete"])
            ->where('uuid', '[0-9a-fA-F]{32}');
        $server->delete('{uuid}/attribute/{key}', [$controllers["entity"], "deleteAttribute"])
            ->where('uuid', '[0-9a-fA-F]{32}');
    }
}