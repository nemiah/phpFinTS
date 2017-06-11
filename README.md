# PHP FinTS/HBCI library


A PHP library implementing the following methods of the FinTS/HBCI protocol:

 * Get balance
 * Get transactions

## Getting Started

Install via composer:

    composer require nemiah/php-fints


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
