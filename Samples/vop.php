<?php

use Fhp\CurlException;
use Fhp\Protocol\ServerException;
use Fhp\Protocol\UnexpectedResponseException;

/**
 * SAMPLE - Helper functions for Verification of Payee. To be used together with init.php.
 */

/** @var \Fhp\FinTs $fints */
$fints = require_once 'init.php';

/**
 * To be called after the $action was already executed, this function takes care of asking the user for a TAN and VOP
 * confirmation, if necessary.
 * @param \Fhp\BaseAction $action The action, which must already have been run through {@link \Fhp\FinTs::execute()}.
 * @throws CurlException|UnexpectedResponseException|ServerException See {@link FinTs::execute()} for details.
 */
function handleVopAndAuthentication(\Fhp\BaseAction $action): void
{
    // NOTE: This is implemented as a `while` loop here, because this sample script runs entirely in one PHP process.
    // If you want to make real use of the serializations demonstrated below, in order to resume processing in a new
    // PHP process later (once the user has responded via your browser/client-side application), then you won't have a
    // loop like this, but instead you'll just run the code within each time you get a new request from the user.
    while (!$action->isDone()) {
        if ($action->needsTan()) {
            handleStrongAuthentication($action); // See login.php for the implementation.
        } elseif ($action->needsPollingWait()) {
            handlePollingWait($action);
        } elseif ($action->needsVopConfirmation()) {
            handleVopConfirmation($action);
        } else {
            throw new \AssertionError(
                'Action is not done but also does not need anything to be done. Did you execute() it?'
            );
        }
    }
}

/**
 * Waits for the amount of time that the bank prescribed and then polls the server for a status update.
 * @param \Fhp\BaseAction $action An action for which {@link \Fhp\BaseAction::needsPollingWait()} returns true.
 * @throws CurlException|UnexpectedResponseException|ServerException See {@link FinTs::execute()} for details.
 */
function handlePollingWait(\Fhp\BaseAction $action): void
{
    global $fints, $options, $credentials; // From login.php

    // Tell the user what the bank had to say (if anything).
    $pollingInfo = $action->getPollingInfo();
    if ($infoText = $pollingInfo->getInformationForUser()) {
        echo $infoText . PHP_EOL;
    }

    // Optional: If the wait is too long for your PHP process to remain alive (i.e. your server would kill the process),
    // you can persist the state as shown here and instead send a response to the client-side application indicating
    // that the operation is still ongoing. Then after an appropriate amount of time, the client can send another
    // request, spawning a new PHP process, where you can restore the state as shown below.
    if ($optionallyPersistEverything = false) {
        $persistedAction = serialize($action);
        $persistedFints = $fints->persist();

        // These are two strings (watch out, they are NOT necessarily UTF-8 encoded), which you can store anywhere.
        // This example code stores them in a text file, but you might write them to your database (use a BLOB, not a
        // CHAR/TEXT field to allow for arbitrary encoding) or in some other storage (possibly base64-encoded to make it
        // ASCII).
        file_put_contents(__DIR__ . '/state.txt', serialize([$persistedFints, $persistedAction]));
    }

    // Wait for (at least) the prescribed amount of time. --------------------------------------------------------------
    // Note: In your real application, you may be doing this waiting on the client and then send a fresh request to your
    // server.
    $waitSecs = $pollingInfo->getNextAttemptInSeconds() ?: 5;
    echo "Waiting for $waitSecs seconds before polling the bank server again..." . PHP_EOL;
    sleep($waitSecs);

    // Optional: If the state was persisted above, we can restore it now (imagine this is a new PHP process).
    if ($optionallyPersistEverything) {
        $restoredState = file_get_contents(__DIR__ . '/state.txt');
        list($persistedInstance, $persistedAction) = unserialize($restoredState);
        $fints = \Fhp\FinTs::new($options, $credentials, $persistedInstance);
        $action = unserialize($persistedAction);
    }

    $fints->pollAction($action);
    // Now the action is in a new state, which the caller of this function (handleVopAndAuthentication) will deal with.
}

/**
 * Asks the user to confirm
 * @param \Fhp\BaseAction $action An action for which {@link \Fhp\BaseAction::needsVopConfirmation()} returns true.
 * @throws CurlException|UnexpectedResponseException|ServerException See {@link FinTs::execute()} for details.
 */
function handleVopConfirmation(\Fhp\BaseAction $action): void
{
    global $fints, $options, $credentials; // From login.php

    $vopConfirmationRequest = $action->getVopConfirmationRequest();
    if ($infoText = $vopConfirmationRequest->getInformationForUser()) {
        echo $infoText . PHP_EOL;
    }
    echo match ($vopConfirmationRequest->getVerificationResult()) {
        \Fhp\Model\VopVerificationResult::CompletedFullMatch =>
            'The bank says the payee information matched perfectly, but still wants you to confirm.',
        \Fhp\Model\VopVerificationResult::CompletedCloseMatch =>
            'The bank says the payee information does not match exactly, so please confirm.',
        \Fhp\Model\VopVerificationResult::CompletedPartialMatch =>
            'The bank says the payee information does not match for all transfers, so please confirm.',
        \Fhp\Model\VopVerificationResult::CompletedNoMatch =>
            'The bank says the payee information does not match, but you can still confirm the transfer if you want.',
        \Fhp\Model\VopVerificationResult::NotApplicable =>
            $vopConfirmationRequest->getVerificationNotApplicableReason() == null
                ? 'The bank did not provide any information about payee verification, but you can still confirm.'
                : 'The bank says: ' . $vopConfirmationRequest->getVerificationNotApplicableReason(),
        default => 'The bank failed to provide information about payee verification, but you can still confirm.',
    } . PHP_EOL;

    // Just like in handleTan(), handleDecoupledSubmission() or handlePollingWait(), we have the option to interrupt the
    // PHP process at this point, so that we can ask the user in a client application for their confirmation.
    if ($optionallyPersistEverything = false) {
        $persistedAction = serialize($action);
        $persistedFints = $fints->persist();
        // See handlePollingWait() for how to deal with this in practice.
        file_put_contents(__DIR__ . '/state.txt', serialize([$persistedFints, $persistedAction]));
    }

    echo "In light of the information provided above, do you want to confirm the execution of the transfer?" . PHP_EOL;
    // Note: We currently have no way canceling the transfer; the only thing we can do is never to confirm it.
    echo "If so, please type 'confirm' and hit Return. Otherwise, please kill this PHP process." . PHP_EOL;
    while (trim(fgets(STDIN)) !== 'confirm') {
        echo "Try again." . PHP_EOL;
    }
    echo "Confirming the transfer." . PHP_EOL;
    $fints->confirmVop($action);
    echo "Confirmed" . PHP_EOL;
    // Now the action is in a new state, which the caller of this function (handleVopAndAuthentication) will deal with.
}
