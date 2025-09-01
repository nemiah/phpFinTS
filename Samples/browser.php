<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpComposerExtensionStubsInspection */

/**
 * SAMPLE - Does the whole login procedure in a browser and then displays the current balance of all accounts.
 *
 * To run it:
 * 1. $ php -S 0.0.0.0:8080 -t ./Samples
 * 2. http://localhost:8080/browser.php
 */

// IMPORTANT: This implementation serves only to demonstrate how the phpFinTS library can be used in a web application
// setting. It follows no coding best practices. Given that these applications handle sensitive data like bank
// credentials and financial information, any real application should follow security-related best practices like XSRF
// protection, encryption, etc., and this application should not be deployed to a publicly accessible web server.

require '../vendor/autoload.php';
function exception_error_handler($errno, $errstr, $errfile, $errline)
{
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('exception_error_handler');

$request = json_decode(file_get_contents('php://input'));

if (isset($request->action)) {
    $options = new \Fhp\Options\FinTsOptions();
    $options->productName = $request->productName;
    $options->productVersion = $request->productVersion;
    $options->url = $request->url;
    $options->bankCode = $request->bankCode;
    $credentials = \Fhp\Options\Credentials::create($request->username, $request->pin);

    $persistedInstance = $persistedAction = null;
    function handleRequest(\stdClass $request, \Fhp\FinTs $fints)
    {
        global $persistedAction;
        switch ($request->action) {
            case 'getTanModes':
                return array_map(function ($mode) {
                    return [
                        'id' => $mode->getId(), 'name' => $mode->getName(), 'isDecoupled' => $mode->isDecoupled(),
                        'needsTanMedium' => $mode->needsTanMedium(),
                    ];
                }, array_values($fints->getTanModes()));
            case 'getTanMedia':
                return array_map(function ($medium) {
                    return ['name' => $medium->getName(), 'phoneNumber' => $medium->getPhoneNumber()];
                }, $fints->getTanMedia(intval($request->tanmode)));
            case 'login':
                $fints->selectTanMode(intval($request->tanmode), $request->tanmedium ?? null);
                $login = $fints->login();
                if ($login->needsTan()) {
                    $tanRequest = $login->getTanRequest();
                    $persistedAction = serialize($login);
                    return ['result' => 'needsTan', 'challenge' => $tanRequest->getChallenge()];
                }
                return ['result' => 'success'];
            case 'submitTan':
                $fints->submitTan(unserialize($persistedAction), $request->tan);
                $persistedAction = null;
                return ['result' => 'success'];
            case 'checkDecoupledSubmission':
                if ($fints->checkDecoupledSubmission(unserialize($persistedAction))) {
                    $persistedAction = null;
                    return ['result' => 'success'];
                } else {
                    // IMPORTANT: If you pull this example code apart in your real application code, remember that after
                    // calling checkDecoupledSubmission(), you need to call $fints->persist() again, just like this
                    // example code will do after we return from handleRequest() here.
                    return ['result' => 'ongoing'];
                }
            case 'getBalances':
                $getAccounts = \Fhp\Action\GetSEPAAccounts::create();
                $fints->execute($getAccounts);
                if ($getAccounts->needsTan()) {
                    throw new \Fhp\UnsupportedException(
                            "This simple example code does not support strong authentication on GetSEPAAccounts calls. " .
                            "But in your real application, you can do so analogously to how login() is handled above."
                    );
                }

                $getBalances = \Fhp\Action\GetBalance::create($getAccounts->getAccounts()[0], true);
                $fints->execute($getBalances);
                if ($getAccounts->needsTan()) {
                    throw new \Fhp\UnsupportedException(
                            "This simple example code does not support strong authentication on GetBalance calls. " .
                            "But in your real application, you can do so analogously to how login() is handled above."
                    );
                }

                $balances = [];
                foreach ($getBalances->getBalances() as $balance) {
                    $sdo = $balance->getGebuchterSaldo();
                    $balances[$balance->getAccountInfo()->getAccountNumber()] =
                        $sdo->getAmount() . ' ' . $sdo->getCurrency();
                }
                return $balances;
            case 'logout':
                $fints->close();
                return ['result' => 'success'];
            default:
                throw new \InvalidArgumentException("Unknown action $request->action");
        }
    }

    $sessionfile = __DIR__ . "/session_$request->sessionid.data";
    if (file_exists($sessionfile)) {
        list($persistedInstance, $persistedAction) = unserialize(file_get_contents($sessionfile));
    }
    $fints = \Fhp\FinTs::new($options, $credentials, $persistedInstance);
    $response = handleRequest($request, $fints);
    file_put_contents($sessionfile, serialize([$fints->persist(), $persistedAction]));

    header('Content-Type: application/json');
    echo json_encode($response);
    return;
}

?>
<!doctype html>
<html lang="de">
<head>
    <title>phpFinTS Beispielanwendung</title>
    <style>
        fieldset { border: none; }
        td:first-child { text-align: right; }
    </style>
</head>
<body>
    <h1>phpFinTS Beispielanwendung</h1>
    <p>Diese Beispielanwendung meldet sich im Onlinebanking an und holt die aktuellen Kontostände ab.</p>
    <p><b>HINWEIS:</b> Wenn sich Bank oder Benutzer ändern, sollte diese Seite erst neu geladen werden!</p>
    <form id="form">
        <input type="hidden" name="sessionid" id="sessionid"/>
        <fieldset id="fieldset">
            <table>
                <tr><td><a target="_blank" href="https://www.hbci-zka.de/register/prod_register.htm">Registrierungsnummer</a>:</td>
                    <td><input type="text" name="productName"/></td></tr>
                <tr><td>Produktversion:</td><td><input type="text" name="productVersion" value="1.0"/></td></tr>
                <tr><td>Bank URL:</td><td><input type="text" name="url" value="https://banking-dkb.s-fints-pt-dkb.de/fints30"/></td></tr>
                <tr><td>Bankleitzahl:</td><td><input type="text" name="bankCode" value="12030000"/></td></tr>
                <tr><td>Benutzerkennung:</td><td><input type="text" name="username"/></td></tr>
                <tr><td>Passwort/PIN:</td><td><input type="password" name="pin"/></td></tr>
                <tr id="tanmodeRow" style="display: none"><td>TAN-Modus:</td><td><select name="tanmode" id="tanmode"></select></td></tr>
                <tr id="tanmediumRow" style="display: none"><td>TAN-Medium:</td><td><select name="tanmedium" id="tanmedium"></select></td></tr>
                <tr><td></td><td><button id="submit">Los geht's</button></td></tr>
            </table>
        </fieldset>
    </form>
    <pre id="output"></pre>
    <script>
        document.getElementById('sessionid').value = new Date().getTime();
        document.getElementById('submit').onclick = async (e) => {
            e.preventDefault();
            const form = document.getElementById('form');
            const formData = Object.fromEntries([...new FormData(form)]);
            const fieldset = document.getElementById('fieldset');
            async function post(action, additionalParams) {
                const response = await fetch('browser.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({action, ...formData, ...additionalParams}),
                });
                if (!response.ok) {
                    throw new Error(`HTTP error ${response.status}: ${response.statusText}`);
                }
                if (response.headers.get('Content-Type').startsWith('text/html')) { // PHP error
                    document.getElementById('output').innerHTML = await response.text();
                    throw new Error('PHP error, click OK to see details below.');
                }
                return response.json();
            }

            fieldset.disabled = true;
            document.getElementById('output').innerText = '';
            try {
                // First the user needs to select a TAN mode. If they haven't already, maybe we need to fetch them first.
                const tanmode = document.getElementById('tanmode');
                if (!tanmode.value) {
                    while (tanmode.firstChild) tanmode.firstChild.remove();
                    for (const mode of await post('getTanModes')) {
                        const option = document.createElement('option');
                        option.setAttribute('value', mode.id);
                        option.appendChild(document.createTextNode(mode.name));
                        option.tanmode = mode;
                        tanmode.appendChild(option);
                    }
                    document.getElementById('tanmodeRow').style.display = '';
                    alert('Bitte einen TAN-Modus auswählen.');
                    return;
                }

                // If the TAN mode requires it, the user also needs to select a TAN medium.
                const selectedMode = tanmode.options[tanmode.selectedIndex].tanmode;
                const tanmedium = document.getElementById('tanmedium');
                if (selectedMode.needsTanMedium && !tanmedium.value) {
                    while (tanmedium.firstChild) tanmedium.firstChild.remove();
                    for (const medium of await post('getTanMedia')) {
                        const option = document.createElement('option');
                        option.setAttribute('value', medium.name);
                        let text = medium.name;
                        if (medium.phoneNumber) text += ` (${medium.phoneNumber})`;
                        option.appendChild(document.createTextNode(text));
                        tanmedium.appendChild(option);
                    }
                    document.getElementById('tanmediumRow').style.display = '';
                    alert('Bitte ein TAN-Medium auswählen.');
                    return;
                }

                // Helper function for TAN/decoupled authentication handling.
                async function handleStrongAuthentication(responsePromise) {
                    let response = await responsePromise;
                    if (response.result === 'needsTan') {
                        if (selectedMode.isDecoupled) {
                            do {
                                alert('Bitte bestätigen Sie die Aktion auf Ihrem Gerät und klicken Sie dann auf OK.');
                                response = await post('checkDecoupledSubmission');
                            } while (response.result === 'ongoing');
                        } else {
                            const tan = prompt('Bitte die TAN eingeben. Bank sagt: ' + response.challenge);
                            response = await post('submitTan', {tan});
                        }
                    }
                    if (response.result !== 'success') {
                        throw new Error(`Unexpected result ${response.result}`);
                    }
                    return response;
                }

                // Now we have everything we need to log in.
                await handleStrongAuthentication(post('login'));

                // Now that we're logged in, we can grab the balances.
                const balances = await post('getBalances');
                document.getElementById('output').innerText = JSON.stringify(balances);

                // And let's log out.
                await post('logout');
            } catch (e) {
                console.log(e);
                alert(e);
            } finally {
                fieldset.disabled = false;
            }
        };
    </script>
</body>
</html>
