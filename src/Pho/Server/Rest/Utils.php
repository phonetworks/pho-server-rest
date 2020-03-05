<?php

namespace Pho\Server\Rest;

use React\Http\Response;

class Utils
{
    const HEADERS = [
        'Content-Type' => 'application/json',
        'Charset'      => 'utf-8'
    ];

    public static function isAllowed(string $key): bool
    {
        $disallowed = getenv("DISALLOWED_ROUTES");
        if(empty($disallowed))
            return true;
        $disallowed = explode("&", $disallowed);
        return !\in_array($key, $disallowed);
    }

    public static function adminLocked(string $key): bool
    {
        $admin_lock = getenv("ADMIN_LOCK");
        if ($admin_lock === 1) 
            return true;

        $protected = getenv("ADMIN_PROTECTED_ROUTES");
        if(empty($protected))
            return true;
        $protected = explode("&", $protected);
        return \in_array($key, $protected);

    }

    public static function isAdmin(ServerRequestInterface $request): bool
    {
        $admin_key = getenv("ADMIN_KEY");

        if(empty($admin_key) || !$request->hasHeader('Authentication'))
            return false;

        $path = strtolower($request->getUri()->getPath());
        $method = strtoupper($request->getMethod());
        $header = trim($request->getHeader('Authentication')[0]);

        if(!preg_match('/^hmac (.+)$/i', $header, $matches))
            return false;

        $digest = $matches[1];

        $verify = base64_encode(hash_hmac("sha256", "{$method}+{$path}", $admin_key));

        return ($digest==$verify);
    }
    
}