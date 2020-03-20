<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RouteSetupTest extends \PHPUnit\Framework\TestCase 
{

    const ROUTE_COUNT = 25;

    public function testBasic() {
        $loop = \React\EventLoop\Factory::create();
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();
        include(dirname(__DIR__) . "/kernel/kernel.php");
        $server = new \Pho\Server\Rest\Server($kernel, $loop);
        $routes = $server->routes();
        $all = $routes->all();
        $this->assertCount(self::ROUTE_COUNT, $all->list());
        $rand = rand(0, self::ROUTE_COUNT-1);
        $all_values = array_values($all->list());
        $minus_one = $all->but($all_values[$rand]);
        $this->assertCount(self::ROUTE_COUNT-1, $minus_one->list());
        $rand = rand(0, self::ROUTE_COUNT-1);
        $minus_two = $minus_one->but($all_values[$rand]);
        $this->assertCount(self::ROUTE_COUNT-2, $minus_two->list());
        $this->assertCount(0, $minus_two->none()->list());
        $this->assertCount(1, $minus_two->none()->only($all_values[$rand])->list());
        $this->assertCount(self::ROUTE_COUNT, $minus_two->all()->list());
        return $server;
    }

    /**
     * @depends testBasic
     */
    public function testNewRoute($server) {
        $server->routes()->add(
            "PUT", "/new_route", function($req, $res) {
                return $res;
            }
        );
        $routes = $server->routes();
        $all = $routes->all();
        $this->assertCount(self::ROUTE_COUNT+1, $all->list());
        return $server;
    }

    /**
     * @depends testNewRoute
     */
    public function testRouteFunctions($server)
    {
        $rand = rand(1, self::ROUTE_COUNT-3);
        $routes = $server->routes();
        $all = $routes->all();
        $all_values = array_values($all->list());
        $all->but($all_values[$rand], $all_values[$rand+1])->lock();
        $server->routes()->only($all_values[$rand+2])->disable();
        $exported = $server->routes()->export();
        $this->assertCount(1, $exported["Disabled"]);
        $locked_count = self::ROUTE_COUNT+1-2; // +1 (new route) -2 (but...)
        $this->assertCount($locked_count, $exported["Locked"]);
        $all->only($all_values[$rand-1])->unlock();
        $exported = $server->routes()->export();
        $this->assertCount($locked_count-1, $exported["Locked"]);
        $server->routes()->only($all_values[$rand+2])->enable();
        $exported = $server->routes()->export();
        $this->assertCount(0, $exported["Disabled"]);

    }
}