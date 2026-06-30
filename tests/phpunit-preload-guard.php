<?php

declare(strict_types=1);

$phpParserNodeInterface = dirname(__DIR__) . '/vendor/nikic/php-parser/lib/PhpParser/Node.php';

if (!interface_exists(\PhpParser\Node::class, false) && is_file($phpParserNodeInterface)) {
    require_once $phpParserNodeInterface;
}
