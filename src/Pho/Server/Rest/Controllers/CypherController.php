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

    private const Q_NODES_WITH_LABEL = "MATCH (n:%s %s) RETURN n";
    private const Q_NODES_WITHOUT_LABEL = "MATCH (n %s) RETURN n";

    //private const Q_EDGES_WITH_LABEL = "MATCH (t)     (h))) RETURN n";
    //private const Q_EDGES_WITHOUT_LABEL = "MATCH (n %s) RETURN n";

    public function matchNodes(Request $request, Response $response) 
    {
        $label = ""; // the label of the query, if exists
        $q = ""; // query part of the cypher
        $vals = []; // values to query (key=>value)
        $cypher = ""; // final cypher query

        $data = $request->getData();
        
        if(isset($data["label"]) && !empty($data["label"])) {
            $label = $data["label"];
            unset($data["label"]);
        }

        foreach($data as $key=>$val)
        {
            $q .= sprintf("%s: {%s}, ", $key, $key);
        }

        if(count($data)>0) {
            $q = substr($q, 0, -2); // rtrim ", "
        }

        if(!empty($label)) {
            $cypher = sprintf(self::Q_NODES_WITH_LABEL, $label, $q);
        }
        else {
            $cypher = sprintf(self::Q_NODES_WITHOUT_LABEL, $q);
        }

        $res = $this->kernel->index()->query($cypher, $data);
        
        $response->writeJson($res->results())->end();

        // $this->fail($response);  
    }


    public function matchEdges(Request $request, Response $response) 
    {
        $label = ""; // the label of the query, if exists
        $q = ""; // query part of the cypher
        $vals = []; // values to query (key=>value)
        $cypher = ""; // final cypher query

        $data = $request->getData();
        
        if(isset($data["label"]) && !empty($data["label"])) {
            $label = $data["label"];
            unset($data["label"]);
        }

        foreach($data as $key=>$val)
        {
            $q .= sprintf("%s: {%s}, ", $key, $key);
        }

        if(count($data)>0) {
            $q = substr($q, 0, -2); // rtrim ", "
        }

        if(!empty($label)) {
            $cypher = sprintf(self::Q_NODES_WITH_LABEL, $label, $q);
        }
        else {
            $cypher = sprintf(self::Q_NODES_WITHOUT_LABEL, $q);
        }

        $res = $this->kernel->index()->query($cypher, $data);
        
        $response->writeJson($res->results())->end();

        // $this->fail($response);  
    }

}