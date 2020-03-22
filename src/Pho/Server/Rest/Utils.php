<?php

namespace Pho\Server\Rest;

use React\Http\Response;
use Psr\Http\Message\ServerRequestInterface;

class Utils
{
    const HEADERS = [
        'Server' => Server::NAME,
        'Content-Type' => 'application/json',
        'Charset'      => 'UTF-8',
        "Access-Control-Allow-Origin" => "*",
        'Access-Control-Allow-Credentials' => "true",
        "Access-Control-Expose-Headers" => "DNT",
        "Access-Control-Expose-Headers" => "X-Custom-Header",
        "Access-Control-Expose-Headers" => "Keep-Alive",
        "Access-Control-Expose-Headers" => "User-Agent",
        "Access-Control-Expose-Headers" => "X-Requested-With",
        "Access-Control-Expose-Headers" => "If-Modified-Since",
        "Access-Control-Expose-Headers" => "Cache-Control",
        "Access-Control-Expose-Headers" => "Content-Type",
        "Access-Control-Expose-Headers" => "Content-Range",
        "Access-Control-Expose-Headers" => "Range",
        "Access-Control-Expose-Headers" => "Origin",
        "Access-Control-Expose-Headers" => "Accept",
        "Access-Control-Expose-Headers" => "Authorization",
        "Access-Control-Allow-Headers" => 'GET, POST, PUT, DELETE, HEAD, OPTIONS, PATCH',
        "Access-Control-Max-Age" => 60 * 60 * 24 * 14, // preflight request is valid for 14 days
    ];

    public static function injectHeaders(Response $response): Response
    {
        foreach(static::HEADERS as $key=>$value) {
            $response = $response->withHeader($key, $value);
        }
        return $response;
    }

    public static function isAdmin(ServerRequestInterface $request, ?\Pho\Kernel\Kernel $kernel = null): bool
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

        $status = ($digest==$verify);
        if($status)
            return true;

        // Starting checking Session
        if(is_null($kernel))
            return false;

        $id = Session::depend($request);
        if(is_null($id)) // no session
            return false;
        
        return $kernel->founder()->id()->equals($id);
    }
    
}