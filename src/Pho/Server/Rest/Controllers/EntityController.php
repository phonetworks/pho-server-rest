<?php

namespace Pho\Server\Rest\Controllers;

use CapMousse\ReactRestify\Http\Request;
use CapMousse\ReactRestify\Http\Response;

class EntityController extends AbstractController 
{

    public function delete(Request $request, Response $response, string $uuid)
    {
        try {
            $res = $this->kernel->gs()->entity($uuid);
        }
        catch(\Exception $e) {
            $this->fail($response);
            return;
        }
        $res->destroy();
        $this->succeed($response);
    }

     public function getAttributes(Request $request, Response $response, string $uuid)
    {
        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->entity($uuid);
            }
            catch(\Exception $e) {
                $this->fail($response);
                return;
            }
            $this->cache[$uuid] = $res;
        }

        $response->writeJson(
            array_keys($this->cache[$uuid]->attributes()->toArray())
        )->end();
    }

    public function getAttribute(Request $request, Response $response, string $uuid, string $key)
    {
        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->entity($uuid);
            }
            catch(\Exception $e) {
                $this->fail($response);
                return;
            }
            $this->cache[$uuid] = $res;
        }

        $response->writeJson($this->cache[$uuid]->attributes()->$key)->end();
    }

    public function setAttribute(Request $request, Response $response, string $uuid, string $key)
    {
        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->entity($uuid);
            }
            catch(\Exception $e) {
                $this->fail($response);
                return;
            }
            $this->cache[$uuid] = $res;
        }

        if(!$request->value) {
            $this->fail($response);
            return;
        }

        $this->cache[$uuid]->attributes()->$key = $request->value;
        $this->succeed($response);
    }

    public function getEntityType(Request $request, Response $response, string $uuid)
    {
        $type = "";
        switch($uuid[0]) {
            case 0:
                $type = "Space"; break;
            case 1:
                $type = "Node"; break;
            case 2:
                $type = "Graph Node"; break;
            case 3:
                $type = "Graph"; break;
            case 4:
                $type = "Actor Node"; break;
            case 5:
                $type = "Object Node"; break;
            case 6:
                $type = "Edge"; break;
            case 7:
                $type = "Read Edge"; break;
            case 8:
                $type = "Write Edge"; break;
            case 9:
                $type = "Subscribe Edge"; break;
            case "a":
                $type = "Mention Edge"; break;
            default:
                $type = "Unidentified"; break;
        }
        $response->writeJson($type)->end();
    }

    public function deleteAttribute(Request $request, Response $response, string $uuid, string $key)
    {
        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->entity($uuid);
            }
            catch(\Exception $e) {
                $this->fail($response);
                return;
            }
            $this->cache[$uuid] = $res;
        }

        if(!isset($this->cache[$uuid]->attributes()->$key)) {
            $this->fail($response);
            return;
        }
        unset($this->cache[$uuid]->attributes()->$key);
        $this->succeed($response);
    }

}