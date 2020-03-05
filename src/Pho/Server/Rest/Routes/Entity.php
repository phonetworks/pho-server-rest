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
if(Utils::isAllowed("EntityController::getAttributes"))
    $res[] = ['GET', '/{uuid}/attributes',"getAttributes"];
if(Utils::isAllowed("EntityController::setAttribute"))
    $res[] = ['PUT', '/{uuid}/attribute/{key}', "setAttribute"];
if(Utils::isAllowed("EntityController::getEntityType"))
    $res[] = ['GET', '/{uuid}/type',"getEntityType"];
if(Utils::isAllowed("EntityController::getAttribute"))
    $res[] = ['GET', '/{uuid}/attribute/{key}',"getAttribute"];
if(Utils::isAllowed("EntityController::delete"))
    $res[] = ['DELETE', '/{uuid}',"delete"];
if(Utils::isAllowed("EntityController::deleteAttribute"))
    $res[] = ['DELETE', '/{uuid}/attribute/{key}',"deleteAttribute"];
if(Utils::isAllowed("EntityController::setAttribute_POST"))
    $res[] = ['POST', '/{uuid}/attribute/{key}',"setAttribute_POST"];

return $res;