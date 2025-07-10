<?php

$header = <<<HEADER
This file is part of the BoShurikTelegramBotBundle.

(c) Alexander Borisov <boshurik@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
HEADER;

$finder = \PhpCsFixer\Finder::create()
    ->in([
        'src',
        'tests',
    ])
    ->name('*.php')
;

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP70Migration' => true,
        '@PHP70Migration:risky' => true,
        '@PHP71Migration' => true,
        '@PHP71Migration:risky' => true,
        '@PHP73Migration' => true,
        'list_syntax' => ['syntax' => 'short'],
        'array_syntax' => ['syntax' => 'short'],
        'compact_nullable_typehint' => true,
        'logical_operators' => true,
        'no_null_property_initialization' => true,
        'no_php4_constructor' => true,
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'combine_consecutive_issets' => true,
        'blank_line_before_statement' => ['statements' => [
            'break',
            'continue',
            'return',
            'throw',
        ]],

        'header_comment' => [
            'header' => $header,
            'comment_type' => 'comment',
            'separate' => 'both',
        ],
        'phpdoc_summary' => false,
        'yoda_style' => false,
        'declare_strict_types' => false,
        'void_return' => false,
        'phpdoc_align' => [],
        'phpdoc_to_comment' => false,
        'single_line_comment_spacing' => false,
        'nullable_type_declaration_for_default_null_value' => true,
    ])
;
