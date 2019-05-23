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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EntityController extends AbstractController 
{

    public function delete(ServerRequestInterface $request, ResponseInterface $response, string $uuid)
    {
        try {
            $res = $this->kernel->gs()->entity($uuid);
        }
        catch(\Exception $e) {
            return $this->fail();
        }
        $res->destroy();

        return $this->succeed();
    }

     public function getAttributes(ServerRequestInterface $request, ResponseInterface $response, string $uuid)
    {
        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->entity($uuid);
            }
            catch(\Exception $e) {
                return $this->fail();
            }
            $this->cache[$uuid] = $res;
        }

        $response->getBody()->write(json_encode(array_keys($this->cache[$uuid]->attributes()->toArray())));

        return $response;
    }

    public function getAttribute(ServerRequestInterface $request, ResponseInterface $response, string $uuid, string $key)
    {
        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->entity($uuid);
            }
            catch(\Exception $e) {
                return $this->fail();
            }
            $this->cache[$uuid] = $res;
        }

        $response->getBody()->write(json_encode($this->cache[$uuid]->attributes()->$key));

        return $response;
    }

    public function setAttribute(ServerRequestInterface $request, ResponseInterface $response, string $uuid, string $key)
    {
        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->entity($uuid);
            }
            catch(\Exception $e) {
                return $this->fail();
            }
            $this->cache[$uuid] = $res;
        }

        $json = json_decode($request->getBody()->getContents(), true);
        if(! isset($json['value'])) {
            return $this->fail();
        }

        $this->cache[$uuid]->attributes()->$key = $json['value'];
        return $this->succeed();
    }

    public function setAttribute_POST()
    {
        return call_user_func_array([ $this, 'setAttribute' ], func_get_args());
    }

    public function getEntityType(ServerRequestInterface $request, ResponseInterface $response, string $uuid)
    {
        $type = "";
        switch($uuid[0]) {
            case 0:
                $type = "Space"; break;
            case 1:
                $type = "Node"; break;
            case 2:
                $type = "Graph Node"; break;
            case 3:
                $type = "Graph"; break;
            case 4:
                $type = "Actor Node"; break;
            case 5:
                $type = "Object Node"; break;
            case 6:
                $type = "Edge"; break;
            case 7:
                $type = "Read Edge"; break;
            case 8:
                $type = "Write Edge"; break;
            case 9:
                $type = "Subscribe Edge"; break;
            case "a":
                $type = "Mention Edge"; break;
            default:
                $type = "Unidentified"; break;
        }
        $response->getBody()->write(json_encode($type));

        return $response;
    }

    public function deleteAttribute(ServerRequestInterface $request, ResponseInterface $response, string $uuid, string $key)
    {
        if(!isset($this->cache[$uuid])) {
            try {
                $res = $this->kernel->gs()->entity($uuid);
            }
            catch(\Exception $e) {
                return $this->fail();
            }
            $this->cache[$uuid] = $res;
        }

        if(!isset($this->cache[$uuid]->attributes()->$key)) {
            return $this->fail();
        }
        unset($this->cache[$uuid]->attributes()->$key);

        return $this->succeed();
    }

}