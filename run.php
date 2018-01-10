<?php

require "vendor/autoload.php";

// include kernel.php here
include("vendor/phonetworks/pho-kernel/kernel.php");

echo "Graph:".(string) $kernel->graph()->id()."\n";
echo "Founder:".(string) $kernel->founder()->id()."\n";

$server = new \Pho\Server\Rest\Daemon($kernel);
$server->setPort(1337);
//eval(\Psy\sh());
$server->serve();
