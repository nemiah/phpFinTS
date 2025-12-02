<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

/**
 * SAMPLE - Execute a SEPA transfer.
 *
 * Note: The phpFinTs library only implements the FinTS protocol. For SEPA transfers, you need a separate library to
 * produce the SEPA XML data, which is then wrapped into FinTS requests. This example uses the phpSepaXml library
 * (see https://github.com/nemiah/phpSepaXml), which you need to install separately.
 */

use nemiah\phpSepaXml\SEPACreditor;
use nemiah\phpSepaXml\SEPADebitor;
use nemiah\phpSepaXml\SEPATransfer;

// See login.php, it returns a FinTs instance that is already logged in.
/** @var \Fhp\FinTs $fints */
$fints = require_once 'login.php';

// Just pick the first account, for demonstration purposes. You could also have the user choose, or have SEPAAccount
// hard-coded and not call getSEPAAccounts() at all.
$getSepaAccounts = \Fhp\Action\GetSEPAAccounts::create();
$fints->execute($getSepaAccounts);
if ($getSepaAccounts->needsTan()) {
    handleStrongAuthentication($getSepaAccounts); // See login.php for the implementation.
}
$oneAccount = $getSepaAccounts->getAccounts()[0];

$dt = new \DateTime();
$dt->add(new \DateInterval('P1D'));

$sepaDD = new SEPATransfer([
    'messageID' => time(),
    'paymentID' => time(),
]);

$sepaDD->setDebitor(new SEPADebitor([ //this is you
    'name' => 'My Company',
    'iban' => 'DE68210501700012345678',
    'bic' => 'DEUTDEDB400', //,
    //'identifier' => 'DE98ZZZ09999999999'
]));

$sepaDD->addCreditor(new SEPACreditor([ //this is who you want to send money to
    //'paymentID' => '20170403652',
    'info' => '20170403652',
    'name' => 'Max Mustermann',
    'iban' => 'CH9300762011623852957',
    'bic' => 'GENODEF1P15',
    'amount' => 48.78,
    'currency' => 'EUR',
    'reqestedExecutionDate' => $dt,
]));

$sendSEPATransfer = \Fhp\Action\SendSEPATransfer::create($oneAccount, $sepaDD->toXML());
$fints->execute($sendSEPATransfer);

require_once 'vop.php';
handleVopAndAuthentication($sendSEPATransfer);

// SEPA transfers don't produce any result we could receive through a getter, but we still need to make sure it's done.
$sendSEPATransfer->ensureDone();
