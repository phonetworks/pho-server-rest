<?php

namespace Pho\Server\Rest;

use React\Http\Response;

class Utils
{
    const HEADERS = [
        'Content-Type' => 'application/json',
        'Charset'      => 'utf-8'
    ];

    public static function adminLocked(): boolean
    {
        $admin_lock = getenv("ADMIN_LOCK");
        return ($admin_lock === 1);
    }

    public static function isAdmin(ServerRequestInterface $request): boolean
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

    public static function failAdmin(): Response
    {
        $response = new Response(
            400,
            static::HEADERS
        );

        $response->getBody()->write(
            json_encode(["success" =>  false, "reason" => "Admin Digest Required"])
        );
        
        return $response;
    }
}