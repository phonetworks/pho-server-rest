<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class CoreTest extends TestCase 
{

    public function test404() {
        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        $res = $this->get('/');
        //$this->assertEquals(500, $res->getStatusCode());
    }

    public function testHeaders() {
        $res = $this->get('/founder', true);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals("PhoNetworks", $res->getHeaderLine('server'));
    }
}