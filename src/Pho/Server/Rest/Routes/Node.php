<?php
/**
 * @todo reminder. this was not used, but it is closer to the new format we seek.
 * 
 * @todo v2
 * convert Router.php into this.
 * 
 */
return array(
    "get" => ['GET', '/{uuid}'],
    "getGetterEdges" => ['GET', '/{uuid}/edges/getters'],
    "getSetterEdges" => ['GET', '/{uuid}/edges/setters'],
    "getAllEdges" => ['GET', '/{uuid}/edges/all'],
    "getIncomingEdges" => ['GET','/{uuid}/edges/in'],
    "getOutgoingEdges" => ['GET', '/{uuid}/edges/out'],
    "getEdgesByClass"  => ['GET', '/{uuid}/{edge:[a-zA-Z_]+}'],
    "createEdge"  => ['POST', '/{uuid}/{edge:[a-zA-Z_]+}'],
);