<?php

declare(strict_types=1);

use PhpCsFixer\Finder;

/** @var PhpCsFixer\Config $rules */
$rules = require __DIR__ . '/php-cs-fixer.php';

$rules->setFinder(
    Finder::create()
        ->in([{% ListText(php_cs_fixer.paths)|EachFormatted("__DIR__ . '/%s'")|Joined(",\n") %}])
        ->exclude([{% ListText(infra.exclude)|EachFormatted("'%s'")|Joined(",") %}]),
)->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');

return $rules;
