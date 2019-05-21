<?php
/**
 * @todo reminder. this was not used, but it is closer to the new format we seek.
 * 
 * @todo v2
 * convert Router.php into this.
 * 
 */
return array(
    "getAttributes" => ['GET', '/{uuid}/attributes'],
    "setAttribute"  => ['PUT', '/{uuid}/attribute/{key}'],
    "getEntityType" => ['GET', '/{uuid}/type'],
    "getAttribute" => ['GET', '/{uuid}/attribute/{key}'],
    "delete" => ['DELETE', '/{uuid}'],
    "deleteAttribute" => ['DELETE', '/{uuid}/attribute/{key}'],
    "setAttribute" => ['POST', '/{uuid}/attribute/{key}'],
);