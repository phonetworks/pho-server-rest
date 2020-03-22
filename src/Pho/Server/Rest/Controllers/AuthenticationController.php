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
use Pho\Server\Rest\{Session, Utils};

class AuthenticationController extends AbstractController 
{

    public function login(ServerRequestInterface $request, ResponseInterface $response)
    {       
        $data = $request->getQueryParams();

        $username = $password = "";
        if(isset($data["username"]))
            $username = $data["username"];
        if(isset($data["password"]))
            $password = $data["password"];

        if(empty($username)||empty($password))
            return $this->fail($response, "Username and/or Password required", 400);

        $res = $this->kernel->index()->query("MATCH (n) WHERE n.Username = {username} AND n.Password = {password} RETURN n.udid", ["username"=>$username, "password"=>$password]);
        $udid = $res->results();
        //error_log(print_r($password, true));
        if(!\is_array($udid)||count($udid)!=1)
            return $this->fail($response, "Username and/or Password do not match", 400);

        $udid = $udid[0]['n.udid'];

        Session::begin($response, $udid);
        
        return $this->succeed(
            $response
        );
    }

    public function logout(ServerRequestInterface $request, ResponseInterface $response)
    {

        $id = Session::depend($request);
        if(is_null($id)) {
            return $this->fail($response, "Not authenticated", 400);
        }

        Session::destroy($response);

        return $this->succeed($response);
    }

    // http://localhost:1337/signup?param1=esokullu&param2=burak@groups-inc.com&param3=123456&param4=01/15/1983&param5=&param6=http://google.com/emre.gif&param7=0
    public function signup(ServerRequestInterface $request, ResponseInterface $response)
    {

        $actor_class = "";
        $default_objects = $this->kernel->config()->default_objects->toArray();
        if(isset($default_objects["actor"]))
            $actor_class = $default_objects["actor"];
        elseif(isset($default_objects["founder"]))
            $actor_class = $default_objects["founder"];
        else {
            // throw new \Exception("No Actor class defined.");
            return $this->fail();
        }
         
        $data = $request->getQueryParams();
        $params = [];
        for($i=1;$i<50;$i++) {
            $param = sprintf("param%s", (string) $i);
            if(! isset($data[$param]))
                continue;
            $params[] = $data[$param];
        }

        try {
            $actor = new $actor_class($this->kernel, $this->kernel->graph(), ...$params);
        }
        catch(\Exception $e) {
            return $this->fail();
        }
        catch(\ArgumentCountError $e) {
            return $this->fail();
        }

        Session::begin($response, $actor->id()->toString());

        return $this->succeed(
            $response, 
            ["id"=>$actor->id()->toString()]
        );
    }

}