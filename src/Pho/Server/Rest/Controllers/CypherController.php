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

class CypherController extends AbstractController 
{

    private const Q_NODES = "MATCH (n%s %s) RETURN n";
    private const Q_EDGES = "MATCH (%s)-[r%s %s]-(%s) RETURN r";

    public function matchNodes(Request $request, Response $response) 
    {
        //error_log("matching nodes");
        //$response->writeJson(["ok"=>"nene"])->end();
        //return; 

        $label = ""; // the label of the query, if exists
        $q = ""; // query part of the cypher
        $vals = []; // values to query (key=>value)
        $cypher = ""; // final cypher query

        $data = $request->httpRequest->getQueryParams();
        
        error_log("data is as follows: ".print_r($data, true));
        
        if(isset($data["label"]) && !empty($data["label"])) {
            $label = ":" . $data["label"];
            unset($data["label"]);
        }

        foreach($data as $key=>$val)
        {
            $q .= sprintf("%s: {%s}, ", $key, $key);
        }

        if(count($data)>0) {
            $q = sprintf("{%s}",
                 substr($q, 0, -2) // rtrim ", "
            ); 
        }

        $cypher = sprintf(self::Q_NODES, $label, $q);

        //error_log("query will be as follows: ". $cypher);
        //error_log("params will be as follows: ". print_r($data, true));

        $res = $this->kernel->index()->query($cypher, $data);
        
        $response->writeJson($res->results())->end();

        // $this->fail($response);  
    }


    public function matchEdges(Request $request, Response $response) 
    {
        //error_log("matching edges");
        //$response->writeJson(["ok"=>"dede"])->end();
        //return; 

        $label = ""; // the label of the query, if exists
        $q = ""; // query part of the cypher
        $vals = []; // values to query (key=>value)
        $cypher = ""; // final cypher query
        $tail_node = "";
        $head_node = "";

        $data = $request->httpRequest->getQueryParams();
        
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
            $q .= sprintf("%s: {%s}, ", $key, $key);
        }

        if(count($data)>0) {
            $q = sprintf("{%s}",
                 substr($q, 0, -2) // rtrim ", "
            ); 
        }

        
        $cypher = sprintf(self::Q_EDGES, $tail_node, $label, $q, $head_node);

        error_log("query will be as follows: ". $cypher);
        error_log("params will be as follows: ". print_r($data, true));

        $res = $this->kernel->index()->query($cypher, $data);
        
        $response->writeJson($res->results())->end();

        // $this->fail($response);  
    }

}