<?php

/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUnhandledExceptionInspection */

/**
 * SAMPLE - Send a direct debit request
 *
 * Note: The phpFinTs library only implements the FinTS protocol. For SEPA transfers, you need a separate library to
 * produce the SEPA XML data, which is then wrapped into FinTS requests. This example uses the phpSepaXml library
 * (see https://github.com/nemiah/phpSepaXml), which you need to install separately.
 */

use nemiah\phpSepaXml\SEPACreditor;
use nemiah\phpSepaXml\SEPADebitor;
use nemiah\phpSepaXml\SEPADirectDebitBasic;

// See login.php, it returns a FinTs instance that is already logged in.
/** @var \Fhp\FinTs $fints */
$fints = require_once 'login.php';

$dt = new \DateTime();
$dt->add(new \DateInterval('P1D'));

$sepaDD = new SEPADirectDebitBasic([
    'messageID' => time(),
    'paymentID' => time()
]);

$sepaDD->setCreditor(new SEPACreditor([ //this is you
    'name' => 'My Company',
    'iban' => 'DE68210501700012345678',
    'bic' => 'DEUTDEDB400',
    'identifier' => 'DE98ZZZ09999999999',
]));

$sepaDD->addDebitor(new SEPADebitor([ //this is who you want to get money from
    'transferID' => 'R20170100',
    'mandateID' => 'aeicznaeibcnt',
    'mandateDateOfSignature' => '2017-05-05',
    'name' => 'Max Mustermann',
    'iban' => 'CH9300762011623852957',
    'bic' => 'GENODEF1P15',
    'amount' => 48.78,
    'currency' => 'EUR',
    'info' => 'R20170100 vom 09.05.2017',
    'requestedCollectionDate' => $dt,
    'sequenceType' => 'OOFF',
    'type' => 'CORE',
]));

// Just pick the first account, for demonstration purposes. You could also have the user choose, or have SEPAAccount
// hard-coded and not call getSEPAAccounts() at all.
$getSepaAccounts = \Fhp\Action\GetSEPAAccounts::create();
$fints->execute($getSepaAccounts);
if ($getSepaAccounts->needsTan()) {
    handleStrongAuthentication($getSepaAccounts); // See login.php for the implementation.
}
$oneAccount = $getSepaAccounts->getAccounts()[0];

$sendSEPADirectDebit = \Fhp\Action\SendSEPADirectDebit::create($oneAccount, $sepaDD->toXML('pain.008.001.02'));
$fints->execute($sendSEPADirectDebit);
if ($sendSEPADirectDebit->needsTan()) {
    handleStrongAuthentication($sendSEPADirectDebit); // See login.php for the implementation.
}
