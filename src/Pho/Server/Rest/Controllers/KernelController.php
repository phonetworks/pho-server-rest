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

class KernelController extends AbstractController
{
    const STATIC_METHODS = ["founder", "graph", "space"];
    
    public function getStatic(ServerRequestInterface $request, ResponseInterface $response, string $method)
    {
        if(!in_array($method, self::STATIC_METHODS)) {
            throw new \Exception("problem!!");
        }
        $res = $this->kernel->$method();
        $response->getBody()->write(json_encode([
            "id" => (string) $res->id(),
            "class" => get_class($res),
        ]));

        return $response;
    }

    public function createActor(ServerRequestInterface $request, ResponseInterface $response)
    {
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

        $response->getBody()->write(json_encode($actor->id()->toString()));

        return $response;
    }

}
