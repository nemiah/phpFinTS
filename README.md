# PHP FinTS/HBCI library

[![Build Status](https://travis-ci.org/nemiah/phpFinTS.svg?branch=master)](https://travis-ci.org/nemiah/phpFinTS)

A PHP library implementing the following functions of the FinTS/HBCI protocol:

 * Get accounts
 * Get balance
 * Get transactions
 * Execute direct debit
 * Execute transfer
 * Note that any other functions mentioned in
   [section C of the specification](https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf)
   should be relatively straightfoward to implement.

Forked from [mschindler83/fints-hbci-php](https://github.com/mschindler83/fints-hbci-php), but then mostly reimplemented.

## Getting Started

Before using this library (or any other FinTS library), you have to register your application with
[Die Deutsche Kreditwirtschaft](https://www.hbci-zka.de/register/hersteller.htm) in order to get your registration
number.
Note that this process can take several weeks.
First you receive your registration number **after a couple days, but then you have to wait anywhere between 0 and 8+ weeks**
for the registration to reach your bank's server. If you have multiple banks, it probably reaches them at different times.

Then install the library via composer:

```
composer require nemiah/php-fints
```

See the examples in the "[Samples](/Samples)" folder to get started on your code.
Fill out the required configuration in `init.php` (server details can be obtained at
[www.hbci-zka.de](https://www.hbci-zka.de) after registration).
Then execute `tanModesAndMedia.php` and later `login.php`.
Once you are able to login without any issues, you can move on to the other examples.

## Banks with special needs

If you are developing an online banking application with this library, please be aware of the following exceptions:

### Hypovereinsbank

The BLZ 71120078 will throw an "Unbekanntes Kreditinstitut" exception when used with the URL https://hbci-01.hypovereinsbank.de/bank/hbci. 
You have to use BLZ 70020270 instead.
```
if (trim($url) == 'https://hbci-01.hypovereinsbank.de/bank/hbci')
	$blz = '70020270';
```

### ING Diba

This bank does not support PSD2:
```
if(trim($blz) == "50010517")
	$fints->selectTanMode(new Fhp\Model\NoPsd2TanMode());
```

## Contribute

Contributions are welcome! See the [developer guide](DEVELOPER-GUIDE.md) for some background information.

We use a slightly modified version of the [Symfony Coding-Style](https://symfony.com/doc/current/contributing/code/standards.html).
Please run 
```
composer update
```
and
```
composer cs-fix
```

before sending a PR.

### Bank compatibility

Different banks implement different versions of the HBCI and FinTS specifications, and they also interpret the
specification differently sometimes. In addition, banks behave differently (within the boundaries of the specification)
when it comes to validation (some may tolerate slightly wrong requests), TANs (some ask for TANs more often than others)
and allowed parameters (not all banks support all parameter combinations).

This library aims to be compatible with all banks that support [FinTS V3.0](https://www.hbci-zka.de/spec/3_0.htm) and
PIN/TAN-based authentication according to PSD2 regulations, which includes most relevant German banks. Currently, it
works with the most popular banks at least, and probably with most others too. Some corner cases (e.g. Mehrfach-TANs or
SMS-Abbuchungskonto for mTAN fees) are not and probably will not be supported.
Those banks with a dedicated [integration test](/lib/Tests/Fhp/Integration) have been tested most extensively.

If you encounter any problems with your particular bank, please check for open GitHub issues or open a new one.
