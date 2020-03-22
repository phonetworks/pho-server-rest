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
use Pho\Server\Rest\Utils;

class CypherController extends AbstractController 
{

    private const Q_NODES = "MATCH (n%s {%s}) RETURN n";
    private const Q_EDGES = "MATCH (%s)-[r%s {%s}]-(%s) RETURN r";

    public function matchNodes(ServerRequestInterface $request, ResponseInterface $response)
    {

        $label = ""; // the label of the query, if exists
        $q = ""; // query part of the cypher
        $vals = []; // values to query (key=>value)
        $cypher = ""; // final cypher query

        $data = $request->getQueryParams();
        
        error_log("data is as follows: ".print_r($data, true));
        
        if(isset($data["label"]) && !empty($data["label"])) {
            $label = ":" . $data["label"];
            unset($data["label"]);
        }

        foreach($data as $key=>$val)
        {
            $q .= sprintf("%s: '%s', ", $key, addslashes($val));
        }

        if(count($data)>0) {
            $q = substr($q, 0, -2);
        }

        $cypher = sprintf(self::Q_NODES, $label, $q);

        //error_log("query will be as follows: ". $cypher);
        //error_log("params will be as follows: ". print_r($data, true));

        $res = $this->kernel->index()->query($cypher, $data);

        return $this->succeed($response, ["results"=>$res->results()]);
    }


    public function matchEdges(ServerRequestInterface $request, ResponseInterface $response)
    {

        $label = ""; // the label of the query, if exists
        $q = ""; // query part of the cypher
        $vals = []; // values to query (key=>value)
        $cypher = ""; // final cypher query
        $tail_node = "";
        $head_node = "";

        $data = $request->getQueryParams();
        
        if(isset($data["head"]) && !empty($data["head"])) {
            $head_node = sprintf("{udid: '%s'}", $data["head"]);
            unset($data["head"]);
        }

        if(isset($data["tail"]) && !empty($data["tail"])) {
            $tail_node = sprintf("{udid: '%s'}", $data["tail"]);
            unset($data["tail"]);
        }

        if(isset($data["label"]) && !empty($data["label"])) {
            $label = ':' . $data["label"];
            unset($data["label"]);
        }

        foreach($data as $key=>$val)
        {
            $q .= sprintf("%s: '%s', ", $key, addslashes($val));
        }

        if(count($data)>0) {
            $q = substr($q, 0, -2);
        }

        
        $cypher = sprintf(self::Q_EDGES, $tail_node, $label, $q, $head_node);

        error_log("query will be as follows: ". $cypher);
        error_log("params will be as follows: ". print_r($data, true));

        $res = $this->kernel->index()->query($cypher, $data);

        return $this->succeed($response, ["results"=>($res->results())]);
    }

}
