<?php
/**
 * @todo reminder. this was not used, but it is closer to the new format we seek.
 * 
 * @todo v2
 * convert Router.php into this.
 * 
 */
return array(
    ['GET', '/{uuid}/attributes',"getAttributes"],
    ['PUT', '/{uuid}/attribute/{key}', "setAttribute"],
    ['GET', '/{uuid}/type',"getEntityType"],
    ['GET', '/{uuid}/attribute/{key}',"getAttribute"],
    ['DELETE', '/{uuid}',"delete"],
    ['DELETE', '/{uuid}/attribute/{key}',"deleteAttribute"],
    ['POST', '/{uuid}/attribute/{key}',"setAttribute_POST"],
);
