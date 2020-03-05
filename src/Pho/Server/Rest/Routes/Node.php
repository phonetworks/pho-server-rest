<?php
/**
 * @todo reminder. this was not used, but it is closer to the new format we seek.
 * 
 * @todo v2
 * convert Router.php into this.
 * 
 */
return array(
    ['GET', '/{uuid}',"get"],
    ['GET', '/{uuid}/edges/getters',"getGetterEdges"],
    ['GET', '/{uuid}/edges/setters',"getSetterEdges"],
    ['GET', '/{uuid}/edges/all',"getAllEdges"],
    ['GET','/{uuid}/edges/in',"getIncomingEdges"],
    ['GET', '/{uuid}/edges/out',"getOutgoingEdges"],
    ['GET', '/{uuid}/{edge:[a-zA-Z_]+}', "getEdgesByClass"],
    ['POST', '/{uuid}/{edge:[a-zA-Z_]+}', "createEdge"],
);

use Pho\Server\Rest\Utils;

$res = [];
if(Utils::isAllowed("NodeController::get"))
    $res[] = ['GET', '/{uuid}',"get"];
if(Utils::isAllowed("NodeController::getGetterEdges"))
    $res[] = ['GET', '/{uuid}/edges/getters',"getGetterEdges"];
if(Utils::isAllowed("NodeController::getSetterEdges"))
    $res[] = ['GET', '/{uuid}/edges/setters',"getSetterEdges"];
if(Utils::isAllowed("NodeController::getAllEdges"))
    $res[] = ['GET', '/{uuid}/edges/all',"getAllEdges"];
if(Utils::isAllowed("NodeController::getIncomingEdges"))
    $res[] =  ['GET','/{uuid}/edges/in',"getIncomingEdges"];
if(Utils::isAllowed("NodeController::getOutgoingEdges"))
    $res[] = ['GET', '/{uuid}/edges/out',"getOutgoingEdges"];
if(Utils::isAllowed("NodeController::getEdgesByClass"))
    $res[] = ['GET', '/{uuid}/{edge:[a-zA-Z_]+}', "getEdgesByClass"];
if(Utils::isAllowed("NodeController::createEdge"))
    $res[] = ['POST', '/{uuid}/{edge:[a-zA-Z_]+}', "createEdge"];

return $res;