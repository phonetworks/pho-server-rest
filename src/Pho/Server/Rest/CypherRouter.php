<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pho\Server\Rest;

/**
 * Router Helper with Cypher commands
 * 
 * Cypher commands enable advanced graph queries. For more information
 * check out http://www.opencypher.org 
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class CypherRouter
{

    protected static function initCypherGet(Server $server, array $controllers): void
    {
        $server->get('edges', [$controllers["edge"], "get"])
            ->where('uuid', '[0-9a-fA-F]{32}');

    }
}