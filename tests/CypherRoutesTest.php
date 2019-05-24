<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class CypherRoutesTest extends TestCase
{

    public function testMatchNodesBySingleAttribute()
    {
        $email = $this->faker->email;
        $this->post('/' . $this->founder_id . '/attribute/email', ['value' => $email]);
        sleep(1);
        $res = $this->get('/nodes?email='.urlencode($email));
        $this->assertCount(1, $res);
        $body = $res[0];
        $this->assertEquals($body["n.udid"], $this->founder_id);
    }

    public function testMatchNodesByMultipleAttribute()
    {
        
        $email = $this->faker->email;
        $about_me = $this->faker->text;
        // address
        $this->post('/' . $this->founder_id . '/attribute/email', ['value' => $email]);
        $this->post('/' . $this->founder_id . '/attribute/About', ['value' => $about_me]);
        sleep(1);
        $res = $this->get('/nodes?email='.urlencode($email)."&About=".urlencode($about_me));
        $this->assertCount(1, $res);
        $body = $res[0];
        $this->assertEquals($body["n.udid"], $this->founder_id);
    }

    public function testMatchEdgesByAdjacentNodes()
    {
        $tweet = $this->faker->realText(130);
        $post_res = $this->post('/' . $this->founder_id . '/post', ['param1' => $tweet]);
        $res = $this->get('/edges?tail='.urlencode($this->founder_id)."&head=".urlencode($post_res));
        $this->assertCount(1, $res);
     //   eval(\Psy\sh());
    }

}
