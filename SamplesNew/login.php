<?php

/** @noinspection PhpUnhandledExceptionInspection */

/**
 * SAMPLE - Creates a new FinTs instance and makes sure its logged in.
 */
require '../vendor/autoload.php';

// Some configuration data needed to use the phpFinTS library.
// The configuration options up here are considered static wrt. the library's internal state and its requests.
// That is, even if you persist the FinTs instance, you need to be able to reproduce all this information from some
// application-specific storage (e.g. your database) in order to use the phpFinTS library.
$options = new \Fhp\FinTsOptions();
$url = ''; // HBCI / FinTS Url can be found here: https://www.hbci-zka.de/institute/institut_auswahl.htm (use the PIN/TAN URL)
$bankCode = ''; // Your bank code / Bankleitzahl
$productName = ''; // The number you receive after registration / FinTS-Registrierungsnummer
$productVersion = '1.0'; // Your own Software product version
$username = 'username';
$pin = 'pin'; // This is NOT the PIN of your bank card!
$fints = new \Fhp\FinTsNew($url, $bankCode, $username, $pin, $productName, $productVersion);
$fints->setLogger(new \Tests\Fhp\CLILogger());

/**
 * This function is key to how FinTS works in times of PSD2 regulations. Most actions like wire transfers, getting
 * statements and even logging in can require a TAN, but won't always. Whether a TAN is required depends on the kind of
 * action, when it was last executed, other parameters like the amount (of a wire transfer) or time span (of a statement
 * request) and generally the security concept of the particular bank. The TAN requirements may or may not be consistent
 * with the TAN that the same bank requires for the same action in the web-based online banking interface. Also, banks
 * may change these requirements over time, so just because your particular bank does not need a TAN for login today
 * does not mean that it stays that way.
 *
 * The TAN can be provided it many different ways. Each application that uses the phpFinTS library has to implement
 * its own way of asking users for a TAN, depending on its user interfaces. The implementation does not have to be in a
 * function like this, it can be inlined with the calling code, or live elsewhere. The TAN can be obtained while the
 * same PHP script is still running (i.e. handleTan() is a blocking function that only returns once the TAN is known),
 * but it is also possible to interrupt the PHP execution entirely while asking for the TAN.
 *
 * @param \Fhp\BaseAction $action Some action that requires a TAN.
 * @throws \Fhp\CurlException
 * @throws \Fhp\Protocol\ServerException
 */
function handleTan(\Fhp\BaseAction $action)
{
    global $fints, $url, $bankCode, $username, $pin, $productName, $productVersion;

    // Find out what sort of TAN we need, tell the user about it.
    $tanRequest = $action->getTanRequest();
    echo 'The bank requested a TAN, asking: ' . $tanRequest->getChallenge() . "\n";
    if ($tanRequest->getTanMediumName() !== null) {
        echo 'Please use this device: ' . $tanRequest->getTanMediumName() . "\n";
    }

    // Optional: Instead of printing the above to the console, you can relay the information (challenge and TAN medium)
    // to the user in any other way (through your REST API, a push notification, ...). If waiting for the TAN requires
    // you to interrupt this PHP session and the TAN will arrive in a fresh (HTTP/REST/...) request, you can do so:
    if ($optionallyPersistEverything = false) {
        $persistedAction = serialize($action);
        $persistedFints = $fints->persist();

        // These are two strings (watch out, they are NOT necessarily UTF-8 encoded), which you can store anywhere.
        // This example code stores them in a text file, but you might write them to your database (use a BLOB, not a
        // CHAR/TEXT field to allow for arbitrary encoding) or in some other storage (possibly base64-encoded to make it
        // ASCII).
        file_put_contents(__DIR__ . 'state.txt', serialize([$persistedFints, $persistedAction]));
    }

    echo "Please enter the TAN:\n";
    $tan = trim(fgets(STDIN));

    // Optional: If the state was persisted above, we can restore it now (imagine this is a new PHP session).
    if ($optionallyPersistEverything) {
        $restoredState = file_get_contents('state.txt');
        list($persistedInstance, $persistedAction) = unserialize($restoredState);
        $fints = new \Fhp\FinTsNew($url, $bankCode, $username, $pin, $productName, $productVersion, $persistedInstance);
        $action = unserialize($persistedAction);
    }

    echo "Submitting TAN: $tan\n";
    $fints->submitTan($action, $tan);
}

// Log in.
$login = $fints->login();
if ($login->needsTan()) {
    handleTan($login);
}

// Usage:
// $fints = require_once 'login.php';
return $fints;
