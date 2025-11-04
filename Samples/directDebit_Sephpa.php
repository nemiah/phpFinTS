<?php

/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUnhandledExceptionInspection */

/**
 * SAMPLE - Send a direct debit request
 *
 * Note: The phpFinTs library only implements the FinTS protocol. For SEPA transfers, you need a separate library to
 * produce the SEPA XML data, which is then wrapped into FinTS requests. This example uses the Sephpa library
 * (see https://github.com/AbcAeffchen/Sephpa), which you need to install separately.
 */

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

// generate a SepaDirectDebit object (pain.008.003.02).
$directDebitFile = new \AbcAeffchenSephpa\SephpaDirectDebit(
    'Name of Application',
    'Message Identifier',
    \AbcAeffchenSephpa\SephpaDirectDebit::SEPA_PAIN_008_003_02
);
/*
 *
 * Configure the Direct Debit File
 * $directDebitCollection = $directDebitFile->addCollection([...]);
 * $directDebitCollection->addPayment([...]);
 *
 * See documentation:
 * https://github.com/AbcAeffchen/Sephpa
 *
*/
$xml = $directDebitFile->generateOutput(['zipToOneFile' => false])[0]['data'];

$sendSEPADirectDebit = \Fhp\Action\SendSEPADirectDebit::create($oneAccount, $xml);
$fints->execute($sendSEPADirectDebit);
if ($sendSEPADirectDebit->needsTan()) {
    handleStrongAuthentication($sendSEPADirectDebit); // See login.php for the implementation.
}

// Debit requests don't produce any result we could receive through a getter, but we still need to make sure it's done.
$sendSEPADirectDebit->ensureDone();
