<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class CorsTest extends TestCase 
{

    public function testCors() {
        $res = $this->get('/founder', true);
        //eval(\Psy\sh());
        //$this->assertEquals(200, $res->getStatusCode());
        //$this->assertEquals("PhoNetworks", );
    }
}