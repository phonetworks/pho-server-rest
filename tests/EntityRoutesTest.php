<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class EntityRoutesTest extends TestCase
{
    public function testEntityGetAttributes()
    {
        $res = $this->get('/' . $this->founder_id . '/attributes', true);
        $this->assertEquals(200, $res->getStatusCode());

        $body = json_decode($res->getBody(), true);
        $this->assertContains("Password", $body);
        $this->assertContains("JoinTime", $body);
        $this->assertContains("Birthday", $body);
        $this->assertContains("About", $body);
    }

    public function testEntityType()
    {
        $res = $this->get('/' . $this->founder_id . '/type');
        $this->assertEquals("Actor Node", $res);
    }

    public function testGetUnexistingAttribute()
    {
        $res = $this->get('/' . $this->founder_id . '/attribute/NewAttribute');
        $this->assertEquals(null, $res['NewAttribute']);
    }

    /**
     * @depends testEntityGetAttributes
     */
    public function testEntitySetAttribute()
    {
        $text = 'Some new text';
        $this->post('/' . $this->founder_id . '/attribute/NewAttribute', ['value' => $text]);

        $body = $this->get('/' . $this->founder_id . '/attributes');
        $this->assertContains("NewAttribute", $body);
        $body = $this->get('/' . $this->founder_id . '/attribute/NewAttribute');
        $this->assertSame($text, $body);
    }

    /**
     * @depends testEntityGetAttributes
     */
    public function testEntitySetEmptyAttribute()
    {
        $this->expectException('\GuzzleHttp\Exception\ServerException');
        $this->post('/' . $this->founder_id . '/attribute/NewAttribute2', ['value' => '']);
    }

    /**
     * @depends testEntityGetAttributes
     */
    public function testEntityChangeAttribute()
    {
        $text = 'Changed text in the attribute';
        $this->post('/' . $this->founder_id . '/attribute/NewAttribute', ['value' => $text]);

        $body = $this->get('/' . $this->founder_id . '/attributes');
        $this->assertContains("NewAttribute", $body);
        $body = $this->get('/' . $this->founder_id . '/attribute/NewAttribute');
        $this->assertSame($text, $body);
    }

    /**
     * @depends testEntityGetAttributes
     */
    public function testDeleteAttribute()
    {
        $this->delete('/' . $this->founder_id . '/attribute/NewAttribute');

        $body = $this->get('/' . $this->founder_id . '/attributes');
        $this->assertFalse(array_key_exists('NewAttribute', $body));
    }

}
