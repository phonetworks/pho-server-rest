<?php

namespace Pho\Server\Rest\Router;

class Foundation
{
    protected static function key(string $method, string $path): string
    {
        return sprintf("%s:%s", strtoupper($method), strtolower($path));
    }
}