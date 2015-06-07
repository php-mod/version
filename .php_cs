<?php
$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in('src')
    ->in('tests')
    ->filter(function (SplFileInfo $file) {
        if (strstr($file->getPath(), 'compatibility')) {
            return false;
        }
    });
$config = Symfony\CS\Config\Config::create();
$config->level(null);
$config->fixers(
    array(
    // PSR-0
        'psr0',
    // PSR-1
        'encoding',
        'short_tag',
    // PSR-2
        'braces',
        'elseif',
        'eof_ending',
        'function_call_space',
        'function_declaration',
        'indentation',
        'line_after_namespace',
        'linefeed',
        'lowercase_constants',
        'lowercase_keywords',
        'method_argument_space',
        'multiple_use',
        'parenthesis',
        'php_closing_tag',
        'single_line_after_imports',
        'trailing_spaces',
        'visibility',
    // Symfony
        'duplicate_semicolon',
        'empty_return',
        'extra_empty_lines',
        'join_function',
        'object_operator',
        'remove_lines_between_uses',
        'standardize_not_equal',
        'unused_use',
        'whitespacy_lines',
    // Contrib
        'long_array_syntax',
    )
);
$config->finder($finder);
return $config;
