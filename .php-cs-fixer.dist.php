<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->in(__DIR__ . '/public');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'combine_consecutive_unsets' => true,
        'class_attributes_separation' => ['elements' => ['method' => 'one',]],
        'multiline_whitespace_before_semicolons' => false,
        'single_quote' => true,
        'braces' => [
            'allow_single_line_closure' => true,
        ],
        'concat_space' => ['spacing' => 'one'],
        'declare_equal_normalize' => true,
        'function_typehint_space' => true,
        'single_line_comment_style' => ['comment_types' => ['hash']],
        'include' => true,
        'lowercase_cast' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'curly_brace_block',
                'extra',
                'throw',
                'use',
            ]
        ],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_spaces_around_offset' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'object_operator_without_whitespace' => true,
        'ternary_operator_spaces' => true,
        'trim_array_spaces' => true,
        'unary_operator_spaces' => true,
        'whitespace_after_comma_in_array' => true,
        'space_after_semicolon' => true,
        'array_push' => false,
        'backtick_to_shell_exec' => true,
        'date_time_immutable' => true,
        'declare_strict_types' => false,
        'lowercase_keywords' => true,
        'lowercase_static_reference' => true,
        'final_class' => false,
        'final_internal_class' => false,
        'final_public_method_for_abstract_class' => false,
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'mb_str_functions' => true,
        'modernize_types_casting' => false,
        'no_blank_lines_after_class_opening' => true,
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'ordered_interfaces' => false,
        'ordered_traits' => false,
        'protected_to_private' => true,
        'self_accessor' => false,
        'self_static_accessor' => true,
        'strict_comparison' => true,
        'visibility_required' => true,
    ])
    ->setLineEnding("\n")
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/php-cs-fixer.cache');