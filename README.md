# PHP FinTS/HBCI library

A PHP library implementing the following functions of the FinTS/HBCI protocol:

 * Get accounts
 * Get bank parameters
 * Get balance
 * Get transactions
 * Get standing orders
 * Delete standing order
 * Execute direct debit
 * Execute transfer

Forked from https://github.com/mschindler83/fints-hbci-php

## Getting Started

Install via composer:

    composer require nemiah/php-fints


## Usage

See the examples in the "Samples" folder.
Fill out the required configuration and execute the file.

Server details can be found here:
https://www.hbci-zka.de/institute/institut_auswahl.htm

## Special usage

My goal for this library was to be able to execute automatic direct debits with mTAN.
You can do so too by using the [sms2url](https://play.google.com/store/apps/details?id=it.furtmeier.sms2url)
 app I created for this purpose.
 
## Contribute

Contributions are welcome!

### Bank compatibility

Please alter the COMPATIBILITY.md file and send a pull request if you successfully tested this library with your bank.
