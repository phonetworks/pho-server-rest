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

use CapMousse\ReactRestify\Http\Response;

abstract class AbstractController
{
    protected $kernel;
    protected $jsonp = false;

    public function __construct(\Pho\Kernel\Kernel $kernel, bool $jsonp = false) {
        $this->kernel = $kernel;
        $this->jsonp = $jsonp;
    } 
    
    private function getWriteMethod(): string
    {
        return $this->jsonp ? "writeJsonP" : "writeJson";
    }

    protected function succeed(Response $response): void
    {
        $method = $this->getWriteMethod();
        $response
            ->$method([
                "success"=>true
            ])
            ->end();
    }

    protected function fail(Response $response, string $message = ""): void
    {
        if(empty($message))
            $response
                    ->setStatus(500)
                    ->end();
        else {
            $method = $this->getWriteMethod();
            $response
                    ->setStatus(400)
                    ->$method([
                        "success" => false,
                        "reason"   => $message
                    ])
                    ->end();
        }
    }
}
