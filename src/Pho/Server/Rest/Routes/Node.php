<?php

return array(
    ['GET', '/{uuid}',"get"],
    ['GET', '/{uuid}/edges/getters',"getGetterEdges"],
    ['GET', '/{uuid}/edges/setters',"getSetterEdges"],
    ['GET', '/{uuid}/edges/all',"getAllEdges"],
    ['GET','/{uuid}/edges/in',"getIncomingEdges"],
    ['GET', '/{uuid}/edges/out',"getOutgoingEdges"],
    ['GET', '/{uuid}/([a-zA-Z_]+)', "getEdgesByClass"],
    ['POST', '/{uuid}/([a-zA-Z_]+)', "createEdge"],
);