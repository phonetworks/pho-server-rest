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

abstract class AbstractController
{

    const HEADERS = [
        'Content-Type' => 'application/json',
        'Charset'      => 'utf-8'
    ];

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
    
    protected function getWriteMethod(): string
    {
        return $this->jsonp ? "writeJsonP" : "writeJson";
    }

    protected function succeed(?ResponseInterface $response = null, array $data = []): Response
    {
        if(is_null($response)) {
            $response = new Response(
                200,
                self::HEADERS
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
                self::HEADERS
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
}
