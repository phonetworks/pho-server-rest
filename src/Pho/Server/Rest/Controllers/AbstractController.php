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

    public function __construct(\Pho\Kernel\Kernel $kernel) {
        $this->kernel = $kernel;
    } 

    protected function succeed(Response $response): void
    {
        $response
            ->writeJson([
                "success"=>true
            ])
            ->end();
    }

    protected function fail(Response $response): void
    {
        $response
                ->setStatus(500)
                ->end();
    }
}