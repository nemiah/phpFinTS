<?php

namespace Fhp;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Holds options for FinTS connections and operations. These options are independent of the user and depend only on the
 * bank system and the client system that uses this library.
 */
class FinTsOptions
{
    /**
     * Identifies the product (i.e. the application in which the phpFinTS library is being used). This is used to show
     * users which products/applications have access to their bank account. Note that this shouldn't just be an
     * arbitrary string, but rather the registration number obtained from the registration with the DK-Verband.
     *
     * @see https://www.hbci-zka.de/register/prod_register.htm
     *
     * @var string
     */
    public $productName;

    /**
     * The product version, which can be an arbitrary string, though if your the application displays a version number
     * somewhere on its own user interface, it should match that.
     *
     * @var string
     */
    public $productVersion;

    /**
     * The bank code (Bankleitzahl) of the bank. Note that this library uses a fixed country code of 280. TODO.
     *
     * @var string
     */
    public $bankCode;

    /**
     * The URL where the bank server can be reached. Should be HTTPS, otherwise the traffic is not encrypted. May
     * include a port number.
     * Example: "https://my-bank.de/fints".
     *
     * @var string
     */
    public $url;

    /** @var int */
    public $timeoutConnect = 15;
    /** @var int */
    public $timeoutResponse = 30;

    /** @var LoggerInterface */
    public $logger;

    /**
     * @throws \InvalidArgumentException if the options are invalid
     */
    public function validate()
    {
        $this->productName = trim($this->productName);
        $this->productVersion = trim($this->productVersion);
        $this->bankCode = trim($this->bankCode);
        $this->url = trim($this->url);
        if (empty($this->productName)) {
            throw new \InvalidArgumentException('Product name required!');
        }
        if (empty($this->productVersion)) {
            throw new \InvalidArgumentException('Product version required!');
        }
        if (empty($this->bankCode)) {
            throw new \InvalidArgumentException('Bank code required!');
        }
        if (empty($this->url)) {
            throw new \InvalidArgumentException('Server URL required!');
        }
        if (null === $this->logger) {
            $this->logger = new NullLogger();
        }
    }
}
