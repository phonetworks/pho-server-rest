<?php

namespace Pho\Server\Rest;

class Router
{
    public static function init(Server $server, array $controllers): void
    {
        
        self::initPut($server, $controllers);
        self::initPost($server, $controllers);
        self::initDelete($server, $controllers);
        self::initGet($server, $controllers);
    }

    public static function initGet(Server $server, array $controllers): void
    {
        $server->get('{uuid}', [$controllers["entity"], "get"])
            ->where('uuid', '[0-9a-fA-F\-]{36}');
        $server->get('{uuid}/edges', [$controllers["node"], "getAllEdges"])
            ->where('uuid', '[0-9a-fA-F\-]{36}');
        $server->get('{uuid}/edges/all', [$controllers["node"], "getAllEdges"])
            ->where('uuid', '[0-9a-fA-F\-]{36}');
        $server->get('{uuid}/edges/in', [$controllers["node"], "getIncomingEdges"])
            ->where('uuid', '[0-9a-fA-F\-]{36}');
        $server->get('{uuid}/edges/out', [$controllers["node"], "getOutgoingEdges"])
            ->where('uuid', '[0-9a-fA-F\-]{36}');
        $server->get('{uuid}/attributes', [$controllers["entity"], "getAttributes"])
            ->where('uuid', '[0-9a-fA-F\-]{36}');
        $server->get('{uuid}/attribute/{key}', [$controllers["entity"], "getAttribute"])
            ->where('uuid', '[0-9a-fA-F\-]{36}');
        $server->get('{uuid}/{edge}', [$controllers["node"], "getEdgesByClass"])
            ->where('uuid', '[0-9a-fA-F\-]{36}')
            ->where('edge', '[a-z]+');
        $server->get('{method}', [$controllers["kernel"], "getStatic"])
            ->where('method', '[a-zA-Z]+');
    }

    public static function initPut(Server $server, array $controllers): void
    {
        $server->put('{uuid}/attribute/{key}', [$controllers["entity"], "setAttribute"])
            ->where('uuid', '[0-9a-fA-F\-]{36}');
        
    }

    public static function initPost(Server $server, array $controllers): void
    {
        $server->post('{uuid}/attribute/{key}', [$controllers["entity"], "setAttribute"])
            ->where('uuid', '[0-9a-fA-F\-]{36}');
        $server->post('{uuid}/{edge}', [$controllers["node"], "createEdge"])
            ->where('uuid', '[0-9a-fA-F\-]{36}')
            ->where('edge', '[a-z]+');
    }

    public static function initDelete(Server $server, array $controllers): void
    {
        $server->delete('{uuid}', [$controllers["entity"], "delete"])
            ->where('uuid', '[0-9a-fA-F\-]{36}');
        $server->delete('{uuid}/attribute/{key}', [$controllers["entity"], "deleteAttribute"])
            ->where('uuid', '[0-9a-fA-F\-]{36}');
    }
}