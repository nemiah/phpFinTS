<?php

namespace Tests\Fhp;

use Fhp\Credentials;
use Fhp\FinTsOptions;
use Fhp\Model\Account;
use Fhp\Model\SEPAAccount;
use Fhp\Syntax\Serializer;

/**
 * A logger that prints log messages to the console (just PHP `echo`) but attempts to remove confidential information
 * like usernames, passwords/PINs and so on. This class is designed to be used only for testing purposes, specifically
 * to record real traffic for integration tests, which ends up hard-coded in the Git repository.
 *
 * Example usage:
 * $credentials = \Fhp\Credentials::create('username', 'sensitivePIN');
 * $options = new \Fhp\FinTsOptions();
 * $options->productName = 'YourProductRegistrationID';
 * $options->productVersion = '1.0';
 * $options->logger = new \Fhp\SanitizingCLILogger([$options, $credentials]);
 * ...
 * $sepaAccount = new SEPAAccount();
 * $sepaAccount->setIban('DE45SENSITIVEIBAN');
 * $options->logger->addSensitiveMaterial([$sepaAccount])
 */
class SanitizingCLILogger extends \Psr\Log\AbstractLogger
{
    /** @var string[] */
    private $needles;

    /**
     * @param array $sensitiveMaterial An array of various objects typically used with the phpFinTS library that contain
     *     some sensitive information. This array may also contain plain strings, which are themselves interpreted as
     *     sensitive.
     */
    public function __construct($sensitiveMaterial)
    {
        $this->needles = static::computeNeedles($sensitiveMaterial);
    }

    /**
     * @param array $sensitiveMaterial See the constructor.
     */
    public function addSensitiveMaterial($sensitiveMaterial)
    {
        $this->needles = array_merge($this->needles, static::computeNeedles($sensitiveMaterial));
    }

    /** @noinspection PhpLanguageLevelInspection */

    /** @noinspection PhpUndefinedClassInspection */
    public function log($level, $message, array $context = []): void
    {
        $message .= empty($context) ? '' : ' ' . implode(', ', $context);
        $sanitizedMessage = static::sanitizeForLogging($message, $this->needles);
        echo "$level: $sanitizedMessage\n";
    }

    /**
     * @param array $sensitiveMaterial An array of various objects typically used with the phpFinTS library that contain
     *     some sensitive information. This array may also contain plain strings, which are themselves interpreted as
     *     sensitive.
     * @return string[] An array of search-replacement "needles" that should be replaced in log messages.
     */
    public static function computeNeedles($sensitiveMaterial)
    {
        $needles = [];
        foreach ($sensitiveMaterial as $item) {
            if (is_string($item)) {
                $needles[] = $item;
            } elseif ($item instanceof Credentials) {
                $needles[] = $item->benutzerkennung;
                $needles[] = $item->pin;
            } elseif ($item instanceof FinTsOptions) {
                $needles[] = $item->bankCode;
                $needles[] = $item->productName;
            } elseif ($item instanceof Account) {
                $needles[] = $item->getIban();
                $needles[] = $item->getAccountNumber();
                $needles[] = $item->getAccountOwnerName();
                $needles[] = $item->getCustomerId();
            } elseif ($item instanceof SEPAAccount) {
                $needles[] = $item->getIban();
                $needles[] = $item->getAccountNumber();
            } else {
                throw new \InvalidArgumentException('Unsupported type of sensitive material ' . gettype($item));
            }
        }
        $needles = array_filter($needles); // Filter out empty entries.
        $escapedNeedles = array_map(function ($needle) {
            // The wire format is ISO-8859-1, so thats what will be logged and thats what needs to looked for when replacing
            return utf8_decode(Serializer::escape($needle));
        }, $needles);
        return array_merge($needles, $escapedNeedles);
    }

    /**
     * Removes sensitive values from the given string, while preserving its overall length, so that wrappers like FinTS
     * messages or Bin containers, which declare the length of their contents, remain parsable.
     * @param string $str Some string.
     * @param string[] The sensitive values to be replaced, usually from {@link #computeNeedles()}.
     * @return string The same string, but with sensitive values removed.
     */
    public static function sanitizeForLogging($str, $needles)
    {
        $replacements = array_map(function ($needle) {
            $len = strlen($needle) - 1;
            $prefix = '<PRIVATE';
            return substr($prefix, 0, $len) . str_repeat('_', max(0, $len - strlen($prefix))) . '>';
        }, $needles);
        return str_replace($needles, $replacements, $str);
    }
}
