<?php

// This is based on the `@Symfony` rule set in `vendor/friendsofphp/php-cs-fixer/src/RuleSet.php`.

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/lib');

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,

        // Logic
        'yoda_style' => null, // Allow both Yoda-style and regular comparisons.

        // Whitespace
        'blank_line_before_statement' => null, // Don't put blank lines before `return` statements.
        'concat_space' => null, // Allow spaces around string concatenation operator.
        'blank_line_after_opening_tag' => null, // Allow file-level @noinspection suppressions to live on the `<?php` line.
        'single_line_throw' => null, // Allow `throw` statements to span multiple lines.

        // phpDoc
        'phpdoc_align' => null, // Don't add spaces within phpDoc just to make parameter names / descriptions align.
        'phpdoc_annotation_without_dot' => null, // Allow terminating dot on @param and such.
        'phpdoc_no_alias_tag' => null, // Allow @link in addition to @see.
        'phpdoc_separation' => null, // Don't put blank line between @params, @throws and @return.
        'phpdoc_summary' => null, // Don't force terminating dot on the first line.
    ])
    ->setFinder($finder);
