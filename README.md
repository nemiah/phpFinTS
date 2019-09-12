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

See the examples in the "Samples" folder.<br>
Fill out the required configuration and execute the file.

Server details can be obtained here after registration:
https://www.hbci-zka.de

## Special usage

My goal for this library was to be able to execute automatic direct debits with m(obile)TAN.
 
## Contribute

Contributions are welcome!

### Bank compatibility

Please update the COMPATIBILITY.md file and send a pull request if you successfully tested this library with your bank.
