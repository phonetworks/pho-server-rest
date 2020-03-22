<?php
return array(
    ['GET', '/{uuid}/attributes',"getAttributes"],
    ['PUT', '/{uuid}/attribute/(\w+)', "setAttribute"],
    ['GET', '/{uuid}/type',"getEntityType"],
    ['GET', '/{uuid}/attribute/(\w+)',"getAttribute"],
    ['DELETE', '/{uuid}',"delete"],
    ['DELETE', '/{uuid}/attribute/(\w+)',"deleteAttribute"],
    ['POST', '/{uuid}/attribute/(\w+)',"setAttribute_POST"],
);
