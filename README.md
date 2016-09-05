# FinTS HBCI PHP

[![Build Status](https://travis-ci.org/mschindler83/fints-hbci-php.svg?branch=master)](https://travis-ci.org/mschindler83/fints-hbci-php)
[![Latest Stable Version](https://poser.pugx.org/mschindler83/fints-hbci-php/v/stable)](https://packagist.org/packages/mschindler83/fints-hbci-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mschindler83/fints-hbci-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mschindler83/fints-hbci-php/?branch=master)
[![Monthly Downloads](https://poser.pugx.org/mschindler83/fints-hbci-php/d/monthly)](https://packagist.org/packages/mschindler83/fints-hbci-php)
[![License](https://poser.pugx.org/mschindler83/fints-hbci-php/license)](https://packagist.org/packages/mschindler83/fints-hbci-php)

A PHP library implementing the basics of the FinTS / HBCI protocol.
It can be used to fetch the balance of connected bank accounts and for fetching bank statements of accounts.

## Getting Started

Install via composer:

    composer require mschindler83/fints-hbci-php


## How to use it

You can have a look at the "Samples" folder in this repository.
Just fill in the required data beginning from line 13 to 17 and run the script.

You can find the server information of your bank here:
https://www.hbci-zka.de/institute/institut_auswahl.htm

## Contribute

### Bank compatibility

This library can only work stable with *YOUR* help!
As I'm very limited in testing different banks it would be good to get some feedback from you all.
Feel free to create PR's for the [COMPATIBILITY.md](COMPATIBILITY.md) file where you can update the list of working banks.

### Code Style

If you plan to contribute to this library, please ensure that you stick with the PSR coding rules as close as you can (At least PSR-0 to PSR-4).
You can find the PHP Standard Recommendations [here](http://www.php-fig.org/psr/)

### Have fun!
