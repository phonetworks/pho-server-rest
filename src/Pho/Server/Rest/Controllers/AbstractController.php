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

    protected function succeed(): Response
    {
        return new Response(
            200,
            self::HEADERS,
            $this->respond(true, "")
        );
    }

    private function respond(bool $success, string $message = ""): string
    {
        $response = json_encode([
            "success" =>  $success,
            "reason"   => $message
        ]);
        if($this->jsonp) {
            $response = "p($response)";
        }
        return $response;
    }

    protected function fail(string $message = "", int $code = 500): Response
    {
        return new Response(
            $code,
            self::HEADERS,
            $this->respond(false, $message)
        );
    }
}
