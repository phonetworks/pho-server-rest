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
    public $founder_id = '';

    public function testEntityGetAttributes()
    {

        $res = $this->get('/' . $this->founder_id . '/attributes', true);
        $this->assertEquals(200, $res->getStatusCode());

        $body = json_decode($res->getBody(), true);
        $this->assertArrayHasKey("Password", $body);
        $this->assertArrayHasKey("JoinTime", $body);
        $this->assertArrayHasKey("Birthday", $body);
        $this->assertArrayHasKey("About", $body);
    }

    public function testGetAttribute()
    {
        $body = $this->get('/' . $this->founder_id . '/attribute/About');
        $this->assertArrayHasKey("About", $body);
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
        $this->assertArrayHasKey("NewAttribute", $body);
        $this->assertSame($text, $body['NewAttribute']);
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
        $this->assertArrayHasKey("NewAttribute", $body);
        $this->assertSame($text, $body['NewAttribute']);
    }

    /**
     * @depends testEntityGetAttributes
     */
    public function testDeleteAttribute()
    {
        $this->delete('/' . $this->founder_id . '/attribute/NewAttribute');

        $body = $this->get('/' . $this->founder_id . '/attributes');
        $this->assertNull($body['NewAttribute']);
    }

    public function testDeleteEntity()
    {
        $this->delete('/' . $this->founder_id . '/attribute/NewAttribute');

        $body = $this->get('/' . $this->founder_id . '/attributes');
        $this->assertNull($body['NewAttribute']);
    }
}
