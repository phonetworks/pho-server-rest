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

}