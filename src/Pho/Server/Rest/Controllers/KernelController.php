<?php

namespace Pho\Server\Rest\Controllers;

use CapMousse\ReactRestify\Http\Request;
use CapMousse\ReactRestify\Http\Response;

class KernelController extends AbstractController
{
    const STATIC_METHODS = ["founder", "graph", "space"];
    
    public function getStatic(Request $request, Response $response, string $method)
    {
        if(!in_array($method, self::STATIC_METHODS)) {
            throw new \Exception("problem!!");
        }
        $res = $this->kernel->$method();
        $response->writeJson(
            array(
                "id" => (string) $res->id(),
                "class" => get_class($res)
            )
        )->end();
    }

    public function createActor(Request $request, Response $response)
    {
        $actor_class = "";
        $default_objects = $this->kernel->config()->default_objects->toArray();
        if(isset($default_objects["actor"]))
            $actor_class = $default_objects["actor"];
        elseif(isset($default_objects["founder"]))
            $actor_class = $default_objects["founder"];
        else {
            // throw new \Exception("No Actor class defined.");
            $this->fail($response);
            return;
        }
            
        $params = [];
        for($i=1;$i<50;$i++) {
            $param = sprintf("param%s", (string) $i);
            if(!$request->$param)
                continue;
            $params[] = $request->$param;
        }

        try {
            $actor = new $actor_class($this->kernel, $this->kernel->graph(), ...$params);
        }
        catch(\Exception $e) {
            $this->fail($response);
            return;
        }

        $response->writeJson(
            $actor->id()->toString()
        )->end();

    }

}
