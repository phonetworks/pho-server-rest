<?php

require "vendor/autoload.php";

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// include kernel.php here
include(__DIR__ . "/kernel/kernel.php");

echo "Graph:".(string) $kernel->graph()->id()."\n";
echo "Founder:".(string) $kernel->founder()->id()."\n";
echo "Mode: ".(string) getenv("ADMIN_KEY")."\n";

$server = new \Pho\Server\Rest\Server($kernel);
$server->port(1337);

/*
$server->routes()->add("GET","/test", function($req, $res) 
{
    $res = $res->withHeader("X-Sample", "Value");
    $res->getBody()->write(json_encode(["x"=>"y"]));
    return $res->withStatus(200);
});
*/

$server->serve();
