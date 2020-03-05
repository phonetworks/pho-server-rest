<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pho\Server\Rest\Controllers;

use React\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Pho\Server\Rest\Utils;

abstract class AbstractController
{

    protected $kernel;
    protected $jsonp = false;

    public function __construct(\Pho\Kernel\Kernel $kernel, bool $jsonp = false) {
        $this->kernel = $kernel;
        $this->jsonp = $jsonp;
    } 

    public function respondInJsonp()
    {
        $this->jsonp = true;
    }

    public function respondInJson()
    {
        $this->jsonp = false;
    }

    protected function isAuthenticated(ServerRequestInterface $request): boolean
    {
        if(!$request->hasHeader('Authentication'))
            return false;

        $path = strtolower($request->getUri()->getPath());
        $method = strtoupper($request->getMethod());
        $header = trim($request->getHeader('Authentication')[0]);

        if(!preg_match('/^hmac (.+?)\:(.+)$/i', $header, $matches))
            return false;

        $username = $matches[1];
        $digest = $matches[2];

        //error_log($username);
        //error_log($digest);

        $res = $this->kernel->index()->query("MATCH (n) WHERE n.Username = {username} RETURN n.Password", ["username"=>$username]);
        $password = $res->results();
        //error_log(print_r($password, true));
        if(!\is_array($password)||count($password)!=1)
            return false;
        $password = $password[0]['n.Password'];
        //error_log($password);
        $verify = base64_encode(hash_hmac("sha256", "{$method}+{$path}", $password));
        //error_log($verify);
        return ($digest==$verify);

    }
    
    protected function getWriteMethod(): string
    {
        return $this->jsonp ? "writeJsonP" : "writeJson";
    }

    protected function succeed(?ResponseInterface $response = null, array $data = []): Response
    {
        if(is_null($response)) {
            $response = new Response(
                200,
                Utils::HEADERS
            );
        }
        
        $response->getBody()->write(
            $this->respond(true, $data)
        );

        return $response->withStatus(200);
    }

    public function fail(?ResponseInterface $response = null, string $message = "", int $code = 500): Response
    {
        if(is_null($response)) {
            $response = new Response(
                $code,
                Utils::HEADERS
            );
        }

        $response->getBody()->write(
            $this->respond(false, ["reason"=>$message])
        );
        
        return $response->withStatus($code);
    }

    private function respond(bool $success, array $data = []): string
    {
        $response = json_encode(
                        array_merge(
                            ["success" =>  $success], 
                            $data
                        )
        );

        if($this->jsonp) {
            $response = "p($response)";
        }
        
        return $response;
    }

    protected function failAdminRequired($response): Response
    {
        return $this->fail($response, "Admin Digest Required", 400);
    }


    protected function handleException(ResponseInterface $response, /*\Exception|\Error*/ $e): void
    {
        $this->fail($response, sprintf(
            "An exception occurred: %s",
            $e->getMessage()
        ));
    }

    public function setExceptionHandler(ResponseInterface $response): self
    {
        @set_exception_handler(function(/*\Exception|\Error*/ $e) use ($response) {
            $this->handleException($response, $e);
        });
        return $this;
    }
}
