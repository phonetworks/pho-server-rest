<?php

namespace Pho\Server\Rest\Controllers;

use CapMousse\ReactRestify\Http\Request;
use CapMousse\ReactRestify\Http\Response;

class EdgeController extends AbstractController 
{

    public function get(Request $request, Response $response, string $uuid)
    {
        if((int) $uuid[0] < 6) {
            $this->fail($response);
            return;
        }

        try {
            $res = $this->kernel->gs()->edge($uuid);
        }
        catch(\Exception $e) {
            $this->fail($response);
            return;
        }
        
        $edge = array();
        $edge["class"] = get_class($res);
        $res = $res->toArray();
        $edge["id"]  = $res["id"];
        $edge["head"]  = $res["head"];
        $edge["tail"]  = $res["tail"];
        
        //$edge["id"]  = $res["id"];
        // predicate?
        $response->writeJson($edge)->end();
    }

}