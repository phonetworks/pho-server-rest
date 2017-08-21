<?php

namespace Pho\Server\Rest\Controllers;

use CapMousse\ReactRestify\Http\Request;
use CapMousse\ReactRestify\Http\Response;

class NodeController extends AbstractController 
{
    private $cache = [];

    private function getEdges(string $type="all", Request $request, Response $response, string $uuid)
    {
        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->node($uuid);
            }
            catch(\Exception $e) {
                $this->fail($response);
                return;
            }
            $this->cache[$uuid] = $res;
        }
        if($type=="all")
            $response->writeJson($this->cache[$uuid]->edges()->toArray())->end();
        else
            $response->writeJson($this->cache[$uuid]->edges()->toArray()[$type])->end();
    }

    public function getAllEdges(Request $request, Response $response, string $uuid)
    {
        $this->getEdges("all", ...func_get_args());
    }

    public function getIncomingEdges(Request $request, Response $response, string $uuid)
    {
        $this->getEdges("in", ...func_get_args());
    }

    public function getOutgoingEdges(Request $request, Response $response, string $uuid)
    {
        $this->getEdges("out", ...func_get_args());
    }

    public function getEdgesByClass(Request $request, Response $response, string $uuid, string $edge)
    {
        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->node($uuid);
            }
            catch(\Exception $e) {
                $this->fail($response);
                return;
            }
            $this->cache[$uuid] = $res;
        }

        $cargo = $this->cache[$uuid]->exportCargo();
        
        if(in_array($edge, $cargo["in"]->labels)) {
            $method = "get".ucfirst($edge);
            $res = $this->cache[$uuid]->$method();
            $return = [];
            foreach($res as $entity) {
                $return[] = (string) $entity->id();
            }
            $response->writeJson($return)->end();
            return;
        }
        elseif(in_array($edge, $cargo["out"]->labels)) {
            // reads
            $method = "get".ucfirst($edge);
            $res = $this->cache[$uuid]->$method();
            $return = [];
            foreach($res as $entity) {
                $return[] = (string) $entity->id();
            }
            $response->writeJson($return)->end();
            return;
        }

        $this->fail($response);
    }

    public function createEdge(Request $request, Response $response, string $uuid, string $edge) 
    {
        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->node($uuid);
            }
            catch(\Exception $e) {
                $this->fail($response);
                return;
            }
            $this->cache[$uuid] = $res;
        }
        
        if(!$request->param1) {
            $this->fail($response);
            return;
        }
        
        $cargo = $this->cache[$uuid]->exportCargo();
        if(in_array($edge, $cargo["out"]->setter_labels)||in_array($edge, $cargo["out"]->formative_labels)) {
            $params = [];
            for($i=1;$i<50;$i++) {
                $param = sprintf("param%s", (string) $i);
                if(!$request->$param)
                    continue;
                if(preg_match('/^[0-9a-fA-F\-]{36}$/', $request->$param)) {
                    try {
                        $tmp = $this->kernel->gs()->entity($request->$param);
                        $params[] = $tmp;
                        continue;
                    }
                    catch(\Exception $e) {}
                }
                $params[] = $request->$param;
            }
            $this->cache[$uuid]->$edge(...$params);
            $this->succeed($response);
            return;
        }
        
        $this->fail($response);
    }

}