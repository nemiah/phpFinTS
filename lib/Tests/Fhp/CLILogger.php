<?php

namespace Tests\Fhp;

/**
 * A logger that prints log messages to the console (just PHP `echo`). This class is designed to be used only for
 * testing purposes.
 */
class CLILogger extends \Psr\Log\AbstractLogger
{
    public function log($level, $message, array $context = []): void
    {
        $message .= count($context) === 0 ? '' : ' ' . implode(', ', $context);
        echo "$level: $message\n";
    }
}
