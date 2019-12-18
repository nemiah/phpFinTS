# PHP FinTS/HBCI library

[![Build Status](https://travis-ci.org/nemiah/phpFinTS.svg?branch=master)](https://travis-ci.org/nemiah/phpFinTS)

:exclamation:**Note: The current developer version contains a new API in the `FinTsNew` class.
If you are just starting to use this library, consider using that already and ignore the `FinTs` class.**
If you want to (continue) using the old class, note that its constructor has changed, whereas the
[Release 1.6](https://github.com/nemiah/phpFinTS/tree/1.6) still has the old constructor. :exclamation:

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
See the examples in the "[Samples](/Samples)" folder (or "[SamplesNew](/SamplesNew)" for the new API). Fill out the required configuration and execute the file.

Server details can be obtained at [www.hbci-zka.de](https://www.hbci-zka.de) after registration.

## Special usage

My goal for this library was to be able to execute automatic direct debits with m(obile)TAN.
 
## Contribute

Contributions are welcome!

We are using a slightly modified version of the [Symfony Coding-Style](https://symfony.com/doc/current/contributing/code/standards.html). Please run 
```
composer cs-fix
```

before sending a PR.

### Bank compatibility

Please update the COMPATIBILITY.md file and send a pull request if you successfully tested this library with your bank.
