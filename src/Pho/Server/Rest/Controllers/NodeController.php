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

use CapMousse\ReactRestify\Http\Request;
use CapMousse\ReactRestify\Http\Response;
use Pho\Lib\Graph\ID;
use Stringy\StaticStringy;
use Pho\Lib\Graph\EntityInterface;

class NodeController extends AbstractController 
{
    private $cache = [];

    public function get(Request $request, Response $response, string $uuid)
    {
        if((int) $uuid[0] > 5) {
            $this->fail($response);
            return;
        }

        try {
            $res = $this->kernel->gs()->node($uuid);
        }
        catch(\Exception $e) {
            $this->fail($response);
            return;
        }
        $node = $res->toArray();
        unset($node["acl"]);
        unset($node["attributes"]);
        unset($node["edge_list"]);
        unset($node["notifications"]);
        unset($node["registered_edges"]);
        $node["class"] = get_class($res);
        $response->writeJson($node)->end();
    }

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
        if($type=="all") {
            $edges = $this->cache[$uuid]->edges()->toArray();
            $return = array();
            $return["from"] = $return["to"] = $return["in"] = $return["out"] = array();
            $return["in"] = array_values($edges["in"]);
            $return["out"] = array_values($edges["out"]);
            foreach($edges["from"] as $id=>$from) {
                $return["from"][$id] = array_values($from);
            }
            foreach($edges["to"] as $id=>$to) {
                $return["to"][$id] = array_values($to);
            }
            $response->writeJson($return)->end();
        }
        else
            $response->writeJson(
                array_values(
                    $this->cache[$uuid]->edges()->toArray()[$type]
                )
            )->end();
    }

    private function getDirectionalEdges(string $type="from", Request $request, Response $response, string $uuid1, string $uuid2)
    {
        if(!isset($this->cache[$uuid1])) {
            try {
                $res = $this->kernel->gs()->node($uuid1);
            }
            catch(\Exception $e) {
                $this->fail($response);
                return;
            }
            $this->cache[$uuid1] = $res;
        }

        if(!in_array($type, ["from", "to"]))
            $type = "from";
        
        $edges = array_keys(
            iterator_to_array($this->cache[$uuid1]->edges()->$type(ID::fromString($uuid2)))
        );
        $response->writeJson($edges)->end();
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

    public function getEdgesFrom(Request $request, Response $response, string $uuid1, string $uuid2)
    {
        $this->getDirectionalEdges("from", ...func_get_args());
    }

    public function getEdgesTo(Request $request, Response $response, string $uuid1, string $uuid2)
    {
        $this->getDirectionalEdges("to", ...func_get_args());
    }

    public function getGetterEdges(Request $request, Response $response, string $uuid)
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
        $getters = array_merge(
            $cargo["in"]->labels,
            $cargo["out"]->labels
        );
        $getters = array_values(
            array_unique(
                array_diff($getters, 
                    [ 
                    // from pho-framework
                    "readers", "subscribers", "reads", "subscriptions",
                    "writes", "writers", "mentions", "mentioners"
                    ]
                )
            )
        );
        $response->writeJson($getters)->end();
    }

    public function getSetterEdges(Request $request, Response $response, string $uuid)
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
        $setters = array_merge(
            $cargo["out"]->setter_labels,
            $cargo["out"]->formative_labels
        );
        $setters = array_values(
            array_unique(
                array_diff($setters, 
                    [ 
                    // from pho-framework
                    "read", "subscribe", "write", "mention"
                    ]
                )
            )
        );
        $response->writeJson($setters)->end();
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

        $checkEdgeName = function(array $haystack) use (/*string*/ $edge): bool
        {
            return \in_array($edge, $haystack);
        };

        $handlePlural = function() use(/*string*/ $edge, /*string*/ $uuid, /*Response*/ $response): void
        {
            $method = "get" . StaticStringy::upperCamelize($edge);
            $res = $this->cache[$uuid]->$method();
            $return = [];
            foreach($res as $entity) {
                $return[] = (string) $entity->id();
            }
            $response->writeJson($return)->end();
        };

        $handleSingular = function() use(/*string*/ $edge, /*string*/ $uuid, /*Response*/ $response): bool
        {
            $method = "get" . StaticStringy::upperCamelize($edge);
            $res = $this->cache[$uuid]->$method();
            if($res instanceof EntityInterface)
            {
                $response->writeJson((string) $res->id())->end();
                return true;
            }
            return false;
        };
        
        if(
            $checkEdgeName($cargo["in"]->labels) ||
            $checkEdgeName($cargo["out"]->labels) ||
            $checkEdgeName($edge, $cargo["in"]->callable_edge_labels) ||
            $checkEdgeName($edge, $cargo["out"]->callable_edge_labels)
        ) {
            $handlePlural();
            return;
        }
        
        if(
            ($checkEdgeName($cargo["in"]->singularLabels) && $handleSingular()) ||
            ($checkEdgeName($edge, $cargo["out"]->singularLabels) && $handleSingular()) ||
            ($checkEdgeName($edge, $cargo["in"]->callable_edge_singularLabels) && $handleSingular()) ||
            ($checkEdgeName($edge, $cargo["out"]->callable_edge_singularLabels) && $handleSingular())
        )
        {
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
            $res = $this->cache[$uuid]->$edge(...$params);
            $response->writeJson($res->id()->toString())->end();
            return;
        }
        
        $this->fail($response);
    }

}