<?php

namespace Pho\Server\Rest\Controllers;

use CapMousse\ReactRestify\Http\Request;
use CapMousse\ReactRestify\Http\Response;

class EntityController extends AbstractController 
{

    public function get(Request $request, Response $response, string $uuid)
    {
        try {
            $res = $this->kernel->gs()->edge($uuid);
        }
        catch(\Exception $e) {
            $this->fail($response);
            return;
        }
        $res = $res->toArray();
        $edge = array();
        $edge["id"]  = $res["id"];
        $edge["head"]  = $res["id"];
        $edge["tail"]  = $res["tail"];
        //$edge["id"]  = $res["id"];
        // predicate?
        $response->writeJson($edge)->end();
    }

}