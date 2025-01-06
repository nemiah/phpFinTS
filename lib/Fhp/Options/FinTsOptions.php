<?php

namespace Fhp\Options;

/**
 * Holds options for FinTS connections and operations. These options are independent of the user and depend only on the
 * bank system and the client system that uses this library. This class mostly serves to pass the data around internally
 * within the library.
 */
class FinTsOptions
{
    /**
     * Identifies the product (i.e. the application in which the phpFinTS library is being used). This is used to show
     * users which products/applications have access to their bank account. Note that this shouldn't just be an
     * arbitrary string, but rather the registration number obtained from the registration with the DK-Verband.
     * @link https://www.hbci-zka.de/register/prod_register.htm
     * @var string
     */
    public $productName;

    /**
     * The product version, which can be an arbitrary string, though if your the application displays a version number
     * somewhere on its own user interface, it should match that.
     * @var string
     */
    public $productVersion;

    /**
     * The bank code (Bankleitzahl) of the bank. Note that this library uses a fixed country code of 280, i.e. it only
     * works with German banks.
     * @var string
     */
    public $bankCode;

    /**
     * The URL where the bank server can be reached. Should be HTTPS, otherwise the traffic is not encrypted. May
     * include a port number.
     * Example: "https://my-bank.de/fints".
     * @var string
     */
    public $url;

    /** @var int */
    public $timeoutConnect = 15;
    /** @var int */
    public $timeoutResponse = 30;

    /**
	 * The Kundensystem-Id as returned by the bank and persisted by the application code
	 * Prevents having to re-authenticate every time on login
	 * Use DialogInitialization::getKundensystemId() on the return-object of FinTs::login(), to get the new one
	 * @var string
	 */
	public $kundensystemId;
	
    /**
     * @throws \InvalidArgumentException If the options are invalid.
     */
    public function validate()
    {
        $this->productName = trim($this->productName);
        $this->productVersion = trim($this->productVersion);
        $this->bankCode = trim($this->bankCode);
        $this->url = trim($this->url);
        if (strlen($this->productName) === 0) {
            throw new \InvalidArgumentException('Product name required!');
        }
        if (strlen($this->productVersion) === 0) {
            throw new \InvalidArgumentException('Product version required!');
        }
        if (strlen($this->bankCode) === 0) {
            throw new \InvalidArgumentException('Bank code required!');
        }
        if (strlen($this->url) === 0) {
            throw new \InvalidArgumentException('Server URL required!');
        }
    }
}
