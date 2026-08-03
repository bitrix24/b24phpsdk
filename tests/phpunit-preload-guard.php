<?php

declare(strict_types=1);

/**
 * Rector's bootstrap.php checks interface_exists(\PHPParser\Node::class, false) — with ALL-CAPS "PHP" —
 * to decide whether to preload its own bundled phpstan/phpdoc-parser (v2.x).
 * That bundled Lexer requires a ParserConfig argument, which breaks typhoon/reflection ^0.4
 * that creates new Lexer() with no arguments (v1.x API).
 *
 * Defining the interface here (via auto_prepend_file=tests/phpunit-preload-guard.php, see Makefile)
 * makes rector's condition evaluate to false before vendor/autoload.php is loaded,
 * so rector's preload.php is never executed and the class-conflict is avoided.
 */
namespace PHPParser {
    if (!interface_exists(\PHPParser\Node::class, false)) {
        interface Node {}
    }
}

namespace {
    $phpParserNodeInterface = dirname(__DIR__) . '/vendor/nikic/php-parser/lib/PhpParser/Node.php';

    if (!interface_exists(\PhpParser\Node::class, false) && is_file($phpParserNodeInterface)) {
        require_once $phpParserNodeInterface;
    }
}
