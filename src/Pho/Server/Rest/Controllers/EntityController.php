<?php

namespace Pho\Server\Rest\Controllers;

use CapMousse\ReactRestify\Http\Request;
use CapMousse\ReactRestify\Http\Response;

class EntityController extends AbstractController 
{

    public function get(Request $request, Response $response, string $uuid)
    {
        try {
            $res = $this->kernel->gs()->entity($uuid);
        }
        catch(\Exception $e) {
            $this->fail($response);
            return;
        }
        $response->writeJson($res->toArray())->end();
    }

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
            $this->cache[$uuid]->attributes()->toArray()
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

        $response->writeJson([
            $key => $this->cache[$uuid]->attributes()->$key
        ])->end();
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

        unset($this->cache[$uuid]->attributes()->$key);
        $this->succeed($response);
    }

}