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
if(Utils::isAllowed("KernelController::getStatic"))
    $res[] = ['GET', '/{method:founder}',"getStatic"];
if(Utils::isAllowed("KernelController::getStatic"))
    $res[] = ['GET', '/{method:space}',"getStatic"];
if(Utils::isAllowed("KernelController::getStatic"))
    $res[] = ['GET', '/{method:graph}',"getStatic"];
if(Utils::isAllowed("KernelController::createActor"))
    $res[] = ['POST', '/actor',"createActor"];

return $res;