<?php

/**
 * SAMPLE - Displays the current bank settings
 */

require '../vendor/autoload.php';

use Fhp\FinTs;
use Fhp\Model\StatementOfAccount\Statement;
use Fhp\Model\StatementOfAccount\Transaction;

define('FHP_BANK_URL', '');                 # HBCI / FinTS Url can be found here: https://www.hbci-zka.de/institute/institut_auswahl.htm (use the PIN/TAN URL)
define('FHP_BANK_PORT', 443);               # HBCI / FinTS Port can be found here: https://www.hbci-zka.de/institute/institut_auswahl.htm
define('FHP_BANK_CODE', '');                # Your bank code / Bankleitzahl
define('FHP_ONLINE_BANKING_USERNAME', '');  # Your online banking username / alias
define('FHP_ONLINE_BANKING_PIN', '');       # Your online banking PIN (NOT! the pin of your bank card!)

$fints = new FinTs(
    FHP_BANK_URL,
    FHP_BANK_PORT,
    FHP_BANK_CODE,
    FHP_ONLINE_BANKING_USERNAME,
    FHP_ONLINE_BANKING_PIN
);



