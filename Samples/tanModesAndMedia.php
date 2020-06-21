<?php

/** @noinspection PhpUnhandledExceptionInspection */

/**
 * SAMPLE - Creates a new FinTs instance (init.php) and lets the user select the TAN mode they want to use.
 */

/** @var \Fhp\FinTs $fints */
$fints = require_once 'init.php';

// First, the user has to decide which TAN mode they want to use.
// NOTE: There is a special case for banks that do not support PSD2, use NoPsd2TanMode for those.
$tanModes = $fints->getTanModes();
if (empty($tanModes)) {
    echo 'Your bank does not support any TAN modes!';
    return;
}

echo "Here are the available TAN modes:\n";
$tanModeNames = array_map(function (\Fhp\Model\TanMode $tanMode) {
    return $tanMode->getName();
}, $tanModes);
print_r($tanModeNames);

echo "Which one do you want to use? Index:\n";
$tanModeIndex = trim(fgets(STDIN));
if (!is_numeric($tanModeIndex) || !array_key_exists(intval($tanModeIndex), $tanModes)) {
    echo 'Invalid index!';
    return;
}
$tanMode = $tanModes[intval($tanModeIndex)];
echo 'You selected ' . $tanMode->getName() . "\n";

// In case the selected TAN mode requires a TAN medium (e.g. if the user picked mTAN, they may have to pick the mobile
// device on which they want to receive TANs), let the user pick that too.
if ($tanMode->needsTanMedium()) {
    $tanMedia = $fints->getTanMedia($tanMode);
    if (empty($tanMedia)) {
        echo 'Your bank did not provide any TAN media, even though it requires selecting one!';
        return;
    }

    echo "Here are the available TAN media:\n";
    $tanMediaNames = array_map(function (\Fhp\Model\TanMedium $tanMedium) {
        return $tanMedium->getName();
    }, $tanMedia);
    print_r($tanMediaNames);

    echo "Which one do you want to use? Index:\n";
    $tanMediumIndex = trim(fgets(STDIN));
    if (!is_numeric($tanMediumIndex) || !array_key_exists(intval($tanMediumIndex), $tanMedia)) {
        echo 'Invalid index!';
        return;
    }
    $tanMedium = $tanMedia[intval($tanMediumIndex)];
    echo 'You selected ' . $tanMedium->getName() . "\n";
} else {
    $tanMedium = null;
}

// Announce the selection to the FinTS library.
$fints->selectTanMode($tanMode, $tanMedium);

// Within your application, you should persist these choices somewhere (e.g. database), so that the user does not have
// to select them again the future. Note that it is sufficient to persist the ID/name, i.e. this is equivalent:
$fints->selectTanMode($tanMode->getId(), $tanMedium->getName());

// Now you could do $fints->login(), see login.php for that. For this example, we'll just close the connection.
$fints->close();
echo 'Done';
