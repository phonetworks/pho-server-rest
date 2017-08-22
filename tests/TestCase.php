<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TestCase extends \PHPUnit\Framework\TestCase 
{
    protected $client;
    const HOST = "http://localhost:1337";

    public function setUp() {
        `php ../run.php`;
        sleep(0.1);
        $this->client = new \GuzzleHttp\Client();
    }

    public function tearDown() {
        //unset($this->graph);
    }
    
    protected function get(string $path, bool $headers=false) {
        $res = $this->client->request('GET', self::HOST.$path);
        if($headers)
            return $res;
        $body = json_decode($res->getBody(), true);
        return $body;
    }

    protected function post(string $path, array $postData) {
        $res = $this->client->request('POST', self::HOST.$path, ['form_params' => $postData]);
        return $res;
    }

    protected function delete(string $path, array $postData = []) {
        $res = $this->client->request('DELETE', self::HOST.$path, ['form_params' => $postData]);
        return $res;
    }

}