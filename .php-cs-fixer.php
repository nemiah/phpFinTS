<?php

// This is based on the `@Symfony` rule set documented in `vendor/friendsofphp/php-cs-fixer/doc/ruleSets/Symfony.rst`.

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/lib');

return (new PhpCsFixer\Config())
    ->setRules([
        // We essentially use the Symfony style guide.
        '@Symfony' => true,

        // But then we have some exclusions, i.e. we disable some of the checks/rules from Symfony:
        // Logic
        'yoda_style' => FALSE, // Allow both Yoda-style and regular comparisons.

        // Whitespace
        'blank_line_before_statement' => FALSE, // Don't put blank lines before `return` statements.
        'concat_space' => FALSE, // Allow spaces around string concatenation operator.
        'blank_line_after_opening_tag' => FALSE, // Allow file-level @noinspection suppressions to live on the `<?php` line.
        'single_line_throw' => FALSE, // Allow `throw` statements to span multiple lines.

        // phpDoc
        'phpdoc_align' => FALSE, // Don't add spaces within phpDoc just to make parameter names / descriptions align.
        'phpdoc_annotation_without_dot' => FALSE, // Allow terminating dot on @param and such.
        'phpdoc_no_alias_tag' => FALSE, // Allow @link in addition to @see.
        'phpdoc_separation' => FALSE, // Don't put blank line between @params, @throws and @return.
        'phpdoc_summary' => FALSE, // Don't force terminating dot on the first line.
    ])
    ->setFinder($finder);
