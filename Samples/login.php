<?php

/** @noinspection PhpUnhandledExceptionInspection */

/**
 * SAMPLE - Creates a new FinTs instance (init.php) and makes sure its logged in.
 */

/** @var \Fhp\FinTs $fints */
$fints = require_once 'init.php';

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
    global $fints, $options, $credentials;

    // Find out what sort of TAN we need, tell the user about it.
    $tanRequest = $action->getTanRequest();
    echo 'The bank requested a TAN, asking: ' . $tanRequest->getChallenge() . "\n";
    if ($tanRequest->getTanMediumName() !== null) {
        echo 'Please use this device: ' . $tanRequest->getTanMediumName() . "\n";
    }

    // Challenge Image for PhotoTan/ChipTan
    if ($tanRequest->getChallengeHhdUc()) {
        $challengeImage = new \Fhp\Model\TanRequestChallengeImage(
            $tanRequest->getChallengeHhdUc()
        );
        echo 'There is a challenge image.' . PHP_EOL;
        // Save the challenge image somewhere
        // Alternative: HTML sample code
        echo '<img src="data:' . htmlspecialchars($challengeImage->getMimeType()) . ';base64,' . base64_encode($challengeImage->getData()) . '" />' . PHP_EOL;
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
        $fints = \Fhp\FinTs::new($options, $credentials, $persistedInstance);
        $action = unserialize($persistedAction);
    }

    echo "Submitting TAN: $tan\n";
    $fints->submitTan($action, $tan);
}

// Select TAN mode and possibly medium. If you're not sure what this is about, read and run tanModesAndMedia.php first.
$tanMode = 900; // This is just a placeholder you need to fill!
$tanMedium = null; // This is just a placeholder you may need to fill.
$fints->selectTanMode($tanMode, $tanMedium);

// Log in.
$login = $fints->login();
if ($login->needsTan()) {
    handleTan($login);
}

// Usage:
// $fints = require_once 'login.php';
return $fints;
