
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
if(Utils::isAllowed("EdgeController::get"))
    $res[] = ['GET', '/edge/{uuid}',"get"];

return $res;