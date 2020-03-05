<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pho\Server\Rest\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Pho\Server\Rest\Utils;

class KernelController extends AbstractController
{
    const STATIC_METHODS = ["founder", "graph", "space"];
    
    public function getStatic(ServerRequestInterface $request, ResponseInterface $response, string $method)
    {
        if(Utils::adminLocked(__METHOD__)&&!Utils::isAdmin($request)) 
            return $this->failAdminRequired($response);

        //$this->isAuthenticated($request);
        if(!in_array($method, self::STATIC_METHODS)) {
            error_log("problem!!");
            return $this->fail();
        }
        $res = $this->kernel->$method();
        return $this->succeed($response, [
            "id" => (string) $res->id(),
            "class" => get_class($res),
        ]);
    }

    public function createActor(ServerRequestInterface $request, ResponseInterface $response)
    {
        if(Utils::adminLocked(__METHOD__)&&!Utils::isAdmin($request)) 
            return $this->failAdminRequired($response);

        $actor_class = "";
        $default_objects = $this->kernel->config()->default_objects->toArray();
        if(isset($default_objects["actor"]))
            $actor_class = $default_objects["actor"];
        elseif(isset($default_objects["founder"]))
            $actor_class = $default_objects["founder"];
        else {
            // throw new \Exception("No Actor class defined.");
            return $this->fail();
        }
            
        $params = [];
        $json = json_decode($request->getBody()->getContents(), true);
        for($i=1;$i<50;$i++) {
            $param = sprintf("param%s", (string) $i);
            if(! isset($json[$param]))
                continue;
            $params[] = $json[$param];
        }

        try {
            $actor = new $actor_class($this->kernel, $this->kernel->graph(), ...$params);
        }
        catch(\Exception $e) {
            return $this->fail();
        }
        catch(\ArgumentCountError $e) {
            return $this->fail();
        }

        return $this->succeed(
            $response, 
            ["id"=>$actor->id()->toString()]
        );
    }

}
