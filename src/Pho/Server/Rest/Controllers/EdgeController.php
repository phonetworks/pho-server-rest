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

class EdgeController extends AbstractController 
{

    public function get(ServerRequestInterface $request, ResponseInterface $response, string $uuid)
    {
        if((int) $uuid[0] < 6) {
            return $this->fail();
        }

        try {
            $res = $this->kernel->gs()->edge($uuid);
        }
        catch(\Exception $e) {
            return $this->fail();
        }
        
        $edge = array();
        $edge["class"] = get_class($res);
        $res = $res->toArray();
        $edge["id"]  = $res["id"];
        $edge["head"]  = $res["head"];
        $edge["tail"]  = $res["tail"];
        
        //$edge["id"]  = $res["id"];
        // predicate?
        return $this->succeed($response, ($edge));
    }

}