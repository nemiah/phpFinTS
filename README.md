# PHP FinTS/HBCI library

[![Build Status](https://travis-ci.org/nemiah/phpFinTS.svg?branch=master)](https://travis-ci.org/nemiah/phpFinTS)

**Note: The current developer version contains changes to the parameters of FinTs::_construct!**

A PHP library implementing the following functions of the FinTS/HBCI protocol:

 * Get accounts
 * Get bank parameters
 * Get balance
 * Get transactions
 * Get standing orders
 * Delete standing order
 * Execute direct debit
 * Execute transfer

Forked from [mschindler83/fints-hbci-php](https://github.com/mschindler83/fints-hbci-php)

## Getting Started

Install via composer:

```
composer require nemiah/php-fints
```

## Usage

Before using this library, you have to register your software with [Die Deutsche Kreditwirtschaft](https://www.hbci-zka.de/register/hersteller.htm) in order to get your registration number.
See the examples in the "[Samples](/Samples)" folder. Fill out the required configuration and execute the file.

Server details can be obtained at [www.hbci-zka.de](https://www.hbci-zka.de) after registration.

## Special usage

My goal for this library was to be able to execute automatic direct debits with m(obile)TAN.
 
## Contribute

Contributions are welcome!

### Bank compatibility

Please update the COMPATIBILITY.md file and send a pull request if you successfully tested this library with your bank.
