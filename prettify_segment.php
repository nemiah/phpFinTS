<?php

$classLoader = require(__DIR__ . '/vendor/autoload.php');

// Read a message from stdin.
echo "Please enter a serialized FinTS segment:\n";
$line = trim(fgets(STDIN));

// Parse it.
$segment = \Fhp\Syntax\Parser::detectAndParseSegment($line);

// Print it. Tip: Put a breakpoint here to inspect it in your IDE.
print_r($segment);
