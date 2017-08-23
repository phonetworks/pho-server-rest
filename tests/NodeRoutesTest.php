<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class NodeRoutesTest extends TestCase
{
    
    public function testGetEdges()
    {

        $res = $this->get('/' . $this->founder_id . '/edges', true);
        $this->assertEquals(200, $res->getStatusCode());

        $body = json_decode($res->getBody(), true);
        $this->assertArrayHasKey("to", $body);
        $this->assertArrayHasKey("from", $body);
        $this->assertArrayHasKey("in", $body);
        $this->assertArrayHasKey("out", $body);
        $this->assertSame($body['to'], []);
        $this->assertSame($body['from'], []);
        $this->assertSame($body['in'], []);
        $this->assertSame($body['out'], []);
    }

    public function testGetAllEdges()
    {

        $res = $this->get('/' . $this->founder_id . '/edges/all', true);
        $this->assertEquals(200, $res->getStatusCode());

        $body = json_decode($res->getBody(), true);
        $this->assertArrayHasKey("to", $body);
        $this->assertArrayHasKey("from", $body);
        $this->assertArrayHasKey("in", $body);
        $this->assertArrayHasKey("out", $body);
        $this->assertSame($body['to'], []);
        $this->assertSame($body['from'], []);
        $this->assertSame($body['in'], []);
        $this->assertSame($body['out'], []);
    }

    public function testGetInEdge()
    {
        $res = $this->get('/' . $this->founder_id . '/edges/in', true);
        $this->assertEquals(200, $res->getStatusCode());

        $body = json_decode($res->getBody(), true);
        $this->assertSame($body, []);
    }

    public function testGetOutEdge()
    {
        $res = $this->get('/' . $this->founder_id . '/edges/in', true);
        $this->assertEquals(200, $res->getStatusCode());

        $body = json_decode($res->getBody(), true);
        $this->assertSame($body, []);
    }

    public function testGetNonExsitingEdge()
    {
        $this->expectException('\GuzzleHttp\Exception\ServerException');
        $res = $this->get('/' . $this->founder_id . '/edges/nonExist', true);
    }

    public function testCreateEdge()
    {
        $this->markTestSkiped('Do not know how add new Edge to the graph');
        $this->post('/' . $this->founder_id . '/from', ['param1' => 'Some new param']);
        $res = $this->get('/' . $this->founder_id . '/edges/in', true);
        $this->assertEquals(200, $res->getStatusCode());

        $body = json_decode($res->getBody(), true);
        $this->assertSame($body, []);
    }

}
