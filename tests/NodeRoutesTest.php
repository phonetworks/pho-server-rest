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

        $res = $this->get('/' . $this->founder_id . '/edges/all', true);
        $this->assertEquals(200, $res->getStatusCode());

        $body = json_decode($res->getBody(), true);
        $this->assertArrayHasKey("to", $body);
        $this->assertArrayHasKey("from", $body);
        $this->assertArrayHasKey("in", $body);
        $this->assertArrayHasKey("out", $body);
        $this->assertTrue(is_array($body['to']), []);
        $this->assertTrue(is_array($body['from']), []);
        $this->assertTrue(is_array($body['in']), []);
        $this->assertTrue(is_array($body['out']), []);
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
        $this->assertTrue(is_array($body['to']), []);
        $this->assertTrue(is_array($body['from']), []);
        $this->assertTrue(is_array($body['in']), []);
        $this->assertTrue(is_array($body['out']), []);
    }

    public function testGetInEdge()
    {
        $res = $this->get('/' . $this->founder_id . '/edges/in', true);
        $this->assertEquals(200, $res->getStatusCode());

        $body = json_decode($res->getBody(), true);
        $this->assertTrue(is_array($body));
    }

    public function testGetOutEdge()
    {
        $res = $this->get('/' . $this->founder_id . '/edges/out', true);
        $this->assertEquals(200, $res->getStatusCode());

        $body = json_decode($res->getBody(), true);
        $this->assertTrue(is_array($body));
    }

    public function testGetNonExistingEdge()
    {
        $this->expectException('\GuzzleHttp\Exception\ServerException');
        $res = $this->get('/' . $this->founder_id . '/edges/nonExist', true);
    }

    public function testCreateEdge()
    {
        $post_res = $this->post('/' . $this->founder_id . '/post', ['param1' => 'This is new tweet']);
        //eval(\Psy\sh());
        $this->assertTrue($post_res["success"]);
        $this->assertTrue((bool)preg_match('/^[0-9a-fA-F]{32}$/', $post_res["id"]));
        //$this->assertSame($post_res['success'], true);
    }

    public function testCreatedEdgeInOut()
    {
        $res = $this->get('/' . $this->founder_id . '/edges/all', true);
        $this->assertEquals(200, $res->getStatusCode());

        $body = json_decode($res->getBody(), true);
        $this->assertSame(false, empty($body));

        $keys = array_keys($body['to']);
        $tweet_id = array_pop($keys);
        $this->assertNotNull($tweet_id);
        $edge_id = array_column($body['to'][$tweet_id], "id")[0];
        $this->assertNotNull($edge_id);
        $return = [$tweet_id, $edge_id];

        return $return;
    }

    /**
     * @depends testCreatedEdgeInOut
     */
    public function testPostExits(array $ids)
    {
        list($tweet_id, $edge_id) = $ids;

        $res = $this->get('/' . $tweet_id, true);
        $this->assertEquals(200, $res->getStatusCode());

        $body = json_decode($res->getBody(), true);
        $this->assertSame(false, empty($body));
    }

    /**
     * @depends testCreatedEdgeInOut
     */
    public function testPostFrom(array $ids)
    {
        list($tweet_id, $edge_id) = $ids;

        $res = $this->get('/' . $tweet_id . '/edges/all', true);
        $this->assertEquals(200, $res->getStatusCode());

        $body = json_decode($res->getBody(), true);
        $this->assertSame(false, empty($body));
        $keys       = array_keys($body['from']);
        $founder_id = array_pop($keys);
        $this->assertSame($founder_id, $this->founder_id);
        $this->edge_id = array_column($body['from'][$founder_id], "id")[0];
        $this->assertSame($this->edge_id, $edge_id);
    }

    /**
     * @depends testCreatedEdgeInOut
     */
    public function testPostEdge(array $ids)
    {
        list($tweet_id, $edge_id) = $ids;

        $res = $this->get('/edge/' . $edge_id, true);
        $this->assertEquals(200, $res->getStatusCode());

        $body = json_decode($res->getBody(), true);
        $this->assertSame(false, empty($body));
        $this->assertArrayHasKey("tail", $body);
        $this->assertArrayHasKey("head", $body);
        $this->assertSame($body['tail'], $this->founder_id);
        $this->assertSame($body['head'], $tweet_id);

    }

}
