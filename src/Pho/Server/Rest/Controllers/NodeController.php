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

use Pho\Lib\Graph\ID;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stringy\StaticStringy;
use Pho\Lib\Graph\EntityInterface;
use Pho\Server\Rest\Utils;

class NodeController extends AbstractController 
{
    private $cache = [];

    public function get(ServerRequestInterface $request, ResponseInterface $response, string $uuid)
    {
        if(Utils::adminLocked()&&!Utils::isAdmin($request)) 
            return $this->failAdminRequired($response);

        if((int) $uuid[0] > 5) {
            return $this->fail();
        }

        try {
            $res = $this->kernel->gs()->node($uuid);
        }
        catch(\Exception $e) {
            return $this->fail();
        }
        $node = $res->toArray();
        unset($node["acl"]);
        unset($node["attributes"]);
        unset($node["edge_list"]);
        unset($node["notifications"]);
        unset($node["registered_edges"]);
        $node["class"] = get_class($res);

        return $this->succeed($response, $node);
    }

    private function getEdges(string $type="all", ServerRequestInterface $request, ResponseInterface $response, string $uuid)
    {
        if(Utils::adminLocked()&&!Utils::isAdmin($request)) 
            return $this->failAdminRequired($response);

        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->node($uuid);
            }
            catch(\Exception $e) {
                return $this->fail();
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
            return $this->succeed($response, $return);
        }
        
        return $this->succeed($response, array_values(
            $this->cache[$uuid]->edges()->toArray()[$type]
        ));
    }

    private function getDirectionalEdges(string $type="from", ServerRequestInterface $request, ResponseInterface $response, string $uuid1, string $uuid2)
    {
        if(Utils::adminLocked()&&!Utils::isAdmin($request)) 
            return $this->failAdminRequired($response);

        if(!isset($this->cache[$uuid1])) {
            try {
                $res = $this->kernel->gs()->node($uuid1);
            }
            catch(\Exception $e) {
                return $this->fail();
            }
            $this->cache[$uuid1] = $res;
        }

        if(!in_array($type, ["from", "to"]))
            $type = "from";
        
        $edges = array_keys(
            iterator_to_array($this->cache[$uuid1]->edges()->$type(ID::fromString($uuid2)))
        );

        return $this->succeed($response, $edges);
    }

    public function getAllEdges(ServerRequestInterface $request, ResponseInterface $response, string $uuid)
    {
        return $this->getEdges("all", ...func_get_args());
    }

    public function getIncomingEdges(ServerRequestInterface $request, ResponseInterface $response, string $uuid)
    {
        return $this->getEdges("in", ...func_get_args());
    }

    public function getOutgoingEdges(ServerRequestInterface $request, ResponseInterface $response, string $uuid)
    {
        return $this->getEdges("out", ...func_get_args());
    }

    public function getEdgesFrom(ServerRequestInterface $request, ResponseInterface $response, string $uuid1, string $uuid2)
    {
        return $this->getDirectionalEdges("from", ...func_get_args());
    }

    public function getEdgesTo(ServerRequestInterface $request, ResponseInterface $response, string $uuid1, string $uuid2)
    {
        return $this->getDirectionalEdges("to", ...func_get_args());
    }

    public function getGetterEdges(ServerRequestInterface $request, ResponseInterface $response, string $uuid)
    {
        if(Utils::adminLocked()&&!Utils::isAdmin($request)) 
            return $this->failAdminRequired($response);

        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->node($uuid);
            }
            catch(\Exception $e) {
                return $this->fail();
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
        return $this->succeed($response, $getters);
    }

    public function getSetterEdges(ServerRequestInterface $request, ResponseInterface $response, string $uuid)
    {
        if(Utils::adminLocked()&&!Utils::isAdmin($request)) 
            return $this->failAdminRequired($response);

        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->node($uuid);
            }
            catch(\Exception $e) {
                return $this->fail();
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
        return $this->succeed($response, $setters);
    }

    public function getEdgesByClass(ServerRequestInterface $request, ResponseInterface $response, string $uuid, string $edge)
    {
        if(Utils::adminLocked()&&!Utils::isAdmin($request)) 
            return $this->failAdminRequired($response);

        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->node($uuid);
            }
            catch(\Exception $e) {
                return $this->fail();
            }
            $this->cache[$uuid] = $res;
        }

        $cargo = $this->cache[$uuid]->exportCargo();
        $edge_camelized = StaticStringy::camelize($edge);
        $checkEdgeName = function(array $haystack) use (/*string*/ $edge_camelized): bool
        {
            error_log("checking {$edge_camelized} in ".print_r($haystack, true));
            return \in_array($edge_camelized, $haystack);
        };

        $handlePlural = function() use(/*string*/ $edge, /*string*/ $uuid, /*Response*/ &$response): void
        {
            $method = "get" . StaticStringy::upperCamelize($edge);
            error_log("method would be: ".$method);
            $res = $this->cache[$uuid]->$method();
            $return = [];
            foreach($res as $entity) {
                $return[] = (string) $entity->id();
            }
            $response = $this->succeed($response, $return);
        };

        $handleSingular = function() use(/*string*/ $edge, /*string*/ $uuid, /*Response*/ &$response): bool
        {
            $method = "get" . StaticStringy::upperCamelize($edge);
            error_log("singular method would be: ".$method);
            $res = $this->cache[$uuid]->$method();
            if($res instanceof EntityInterface)
            {
                $response = $this->succeed($response, [(string) $res->id()]);
                return true;
            }
            return false;
        };
        
        if(
            $checkEdgeName($cargo["in"]->labels) ||
            $checkEdgeName($cargo["out"]->labels) ||
            $checkEdgeName($cargo["in"]->callable_edge_labels) ||
            $checkEdgeName($cargo["out"]->callable_edge_labels)
        ) {
            $handlePlural();
            return $response;
        }
        
        if(
            ($checkEdgeName($cargo["in"]->singularLabels) && $handleSingular()) ||
            ($checkEdgeName($cargo["out"]->singularLabels) && $handleSingular()) ||
            ($checkEdgeName($cargo["in"]->callable_edge_singularLabels) && $handleSingular()) ||
            ($checkEdgeName($cargo["out"]->callable_edge_singularLabels) && $handleSingular())
        )
        {
                return $response;
        }
        //error_log("nothing found for {$edge} while \$cargo is: ".print_r($cargo, true));
        return $this->fail();
    }

    public function createEdge(ServerRequestInterface $request, ResponseInterface $response, string $uuid, string $edge)
    {
        if(Utils::adminLocked()&&!Utils::isAdmin($request)) 
            return $this->failAdminRequired($response);

        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->node($uuid);
            }
            catch(\Exception $e) {
                return $this->fail();
            }
            $this->cache[$uuid] = $res;
        }

        $json = json_decode($request->getBody()->getContents(), true);
        if(! isset($json['param1'])) {
            return $this->fail();
        }
        
        $cargo = $this->cache[$uuid]->exportCargo();
        if(in_array($edge, $cargo["out"]->setter_labels)||in_array($edge, $cargo["out"]->formative_labels)) {
            $params = [];
            for($i=1;$i<50;$i++) {
                $param = sprintf("param%s", (string) $i);
                if(! isset($json[$param]))
                    continue;
                if(preg_match('/^[0-9a-fA-F\-]{36}$/', $json[$param])) {
                    try {
                        $tmp = $this->kernel->gs()->entity($json[$param]);
                        $params[] = $tmp;
                        continue;
                    }
                    catch(\Exception $e) {}
                }
                $params[] = $json[$param];
            }
            $res = $this->cache[$uuid]->$edge(...$params);
            return $this->succeed($response, ["id"=>$res->id()->toString()]);
        }
        return $this->fail();
    }

}