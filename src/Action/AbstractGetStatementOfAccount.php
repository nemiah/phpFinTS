<?php

namespace Fhp\Action;

use Fhp\Model\StatementOfAccount\StatementOfAccount;
use Fhp\PaginateableAction;

/**
 * Base class for the actions that retrieve statements for one specific account or for all accounts that the user has
 * access to. A statement is a series of financial transactions that pertain to the account, grouped by day.
 *
 * The FinTS specification defines two wire formats for statements, and banks do not necessarily support both:
 * MT 940 (requested with HKKAZ, implemented in {@link GetStatementOfAccountMT940}) and CAMT XML (requested with HKCAZ,
 * implemented in {@link GetStatementOfAccountXML}). Either of them can be used directly if your application needs a
 * particular format, or you can let {@link GetStatementOfAccount} pick whichever one the bank supports.
 */
abstract class AbstractGetStatementOfAccount extends PaginateableAction
{
    /**
     * @return StatementOfAccount The statement, independent of the wire format it was retrieved in.
     */
    abstract public function getStatement(): StatementOfAccount;

    /**
     * @return string[] The statement data in the format of the action that produced it, exactly as the bank sent it,
     *     for applications that want to parse it themselves. There is one entry per document: MT 940 data arrives as a
     *     single blob (so there is at most one entry), whereas each page of a CAMT XML response is a document of its
     *     own. The array is empty if the bank reported that no statement is available.
     */
    abstract public function getRawResponse(): array;
}
