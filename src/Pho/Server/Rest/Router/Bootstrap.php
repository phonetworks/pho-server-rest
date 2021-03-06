<?php

namespace Pho\Server\Rest\Router;

use Pho\Server\Rest\Controllers\AbstractController;
use Pho\Kernel\Kernel;
use Pho\Server\Rest\Dispatcher;

class Bootstrap {

    public const NOT_FOUND = 0;
    public const FOUND = 1;
    public const METHOD_NOT_ALLOWED = 2;

    protected $kernel;
    protected $jsonp = false;

    protected $controllers = [];

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->startControllers()->startRoutes();
    }

    protected static function key(string $method, string $path): string
    {
        return sprintf("%s:%s", strtoupper($method), strtolower($path));
    }

    private function startControllers(): self
    {
        $controller_dir = dirname(__DIR__). DIRECTORY_SEPARATOR . "Controllers" . DIRECTORY_SEPARATOR;
        $locator = new \Zend\File\ClassFileLocator($controller_dir);
        foreach ($locator as $file) {
            $classes = $file->getClasses();
            foreach($classes as $class) {
                $ref = new \ReflectionClass($class);
                if(!$ref->isSubclassOf(AbstractController::class) || $ref->isAbstract())
                    continue;
                //$controller_key = strtolower(str_replace("Controller", "", $ref->getShortName()));
                $controller_key = $ref->getShortName();
                $this->controllers[$controller_key] = new $class($this->kernel, $this->jsonp);
            }
        }
        return $this;
    }

    private function startRoutes(): void
    {
        $routes = [];
        $routes_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . "Routes" . DIRECTORY_SEPARATOR;
        $route_files = scandir($routes_dir);
        // load kernel routes at last
        usort($route_files, function ($a, $b) {
            return $a === 'Kernel.php';
        });
        foreach ($route_files as $file) {
            if(in_array($file, [".", ".."])) {
               continue; 
            }
            $controller = substr($file, 0, -4)."Controller";
            $routes = include($routes_dir.$file);
            //print_r($routes);
            foreach($routes as $route) {
                $handler = $route[2];
                $callable = "{$controller}::{$handler}";
                echo $route[1]." -- ".$callable."\n";
                $this->add($route[0], static::resolveCommonRouteScenarios($route[1]), $callable);
            }
        }
    }

    // this lambda converts some common usages such as uuid
    // for unique cases, regex must be kept at the 
    // route level.
    protected static function resolveCommonRouteScenarios(string $path): string
    {
        return str_replace("{uuid}", "([0-9a-fA-F]{32})", $path);
    }

}