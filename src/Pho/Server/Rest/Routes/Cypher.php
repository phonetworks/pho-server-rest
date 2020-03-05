<?php
/**
 * @todo reminder. this was not used, but it is closer to the new format we seek.
 * 
 * @todo v2
 * convert Router.php into this.
 * 
 */

use Pho\Server\Rest\Utils;

$res = [];
if(Utils::isAllowed("CypherController::matchEdges"))
    $res[] = ['GET', '/edges', "matchEdges"];
if(Utils::isAllowed("CypherController::matchNodes"))
    $res[] = ['GET', '/nodes', "matchNodes"];

return $res;
