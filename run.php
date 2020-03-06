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
$server->bootstrap()->setPort(1337);
//eval(\Psy\sh());
$server->serve();
