<?php

namespace Fhp\Options;

use Fhp\Model\Account;
use Fhp\Model\SEPAAccount;
use Fhp\Syntax\Serializer;
use Psr\Log\LoggerInterface;

/**
 * A logger that forwards to another PSR logger, but attempts to remove confidential information
 * like usernames, passwords/PINs and so on.
 *
 * Note: This is internally used by {@link FinTs#setLogger()}, so application could usually does not need to instantiate
 * this class manually.
 */
class SanitizingLogger extends \Psr\Log\AbstractLogger
{
    /** @var LoggerInterface */
    private $logger;
    /** @var string[] */
    private $needles;

    /**
     * @param LoggerInterface $logger The inner logger, to which the SanitizingLogger forwards its output.
     * @param array $sensitiveMaterial An array of various objects typically used with the phpFinTS library that contain
     *     some sensitive information. This array may also contain plain strings, which are themselves interpreted as
     *     sensitive.
     */
    public function __construct(LoggerInterface $logger, array $sensitiveMaterial)
    {
        $this->logger = $logger;
        $this->needles = static::computeNeedles($sensitiveMaterial);
    }

    /**
     * @param array $sensitiveMaterial See the constructor.
     */
    public function addSensitiveMaterial(array $sensitiveMaterial)
    {
        $this->needles = array_merge($this->needles, static::computeNeedles($sensitiveMaterial));
    }

    public function log($level, $message, array $context = []): void
    {
        $message .= count($context) === 0 ? '' : ' ' . implode(', ', $context);
        $this->logger->log($level, static::sanitizeForLogging($message, $this->needles));
    }

    /**
     * @param array $sensitiveMaterial An array of various objects typically used with the phpFinTS library that contain
     *     some sensitive information. This array may also contain plain strings, which are themselves interpreted as
     *     sensitive.
     * @return string[] An array of search-replacement "needles" that should be replaced in log messages.
     */
    public static function computeNeedles(array $sensitiveMaterial): array
    {
        $needles = [];
        foreach ($sensitiveMaterial as $item) {
            if (is_string($item)) {
                $needles[] = $item;
            } elseif ($item instanceof Credentials) {
                $needles[] = $item->benutzerkennung;
                $needles[] = $item->pin;
            } elseif ($item instanceof FinTsOptions) {
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
        $escapedNeedles = array_map(function (string $needle) {
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
    public static function sanitizeForLogging(string $str, $needles): string
    {
        $replacements = array_map(function ($needle) {
            $len = strlen($needle) - 1;
            $prefix = '<PRIVATE';
            return substr($prefix, 0, $len) . str_repeat('_', max(0, $len - strlen($prefix))) . '>';
        }, $needles);
        return str_replace($needles, $replacements, $str);
    }
}
