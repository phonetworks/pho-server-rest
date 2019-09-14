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