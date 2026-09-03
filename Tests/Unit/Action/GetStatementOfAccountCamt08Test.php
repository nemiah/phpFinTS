<?php

namespace Fhp\Tests\Unit\Action;

/**
 * Runs the same scenarios as {@link GetStatementOfAccountAutoTest}, but with a camt.052.001.08 document instead of a
 * camt.052.001.02 one (hand-written as well, see the note over there). That is what the bank from https://github.com/nemiah/phpFinTS/issues/553
 * does, and banks may answer with any of the CAMT versions they announced in HICAZS, so parsing must not depend on the
 * version. Note that .08 wraps the booking status in a <Sts><Cd> element, unlike .02, which spells it out directly.
 */
class GetStatementOfAccountCamt08Test extends GetStatementOfAccountAutoTest
{
    public const CAMT_VERSION = 'camt.052.001.08';

    public const STATUS_PENDING = '<Sts><Cd>PDNG</Cd></Sts>';

    public const CAMT_DOCUMENT = '<?xml version="1.0" encoding="ISO-8859-1" ?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:camt.052.001.08" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><BkToCstmrAcctRpt><Rpt><Id>1234567890-2020-02-05</Id><Acct><Id><IBAN>DExxABCDEFGH1234567890</IBAN></Id></Acct>'
        . '<Bal><Tp><CdOrPrtry><Cd>OPBD</Cd></CdOrPrtry></Tp><Amt Ccy="EUR">1234.56</Amt><CdtDbtInd>CRDT</CdtDbtInd><Dt><Dt>2020-02-05</Dt></Dt></Bal>'
        . '<Ntry><Amt Ccy="EUR">123.45</Amt><CdtDbtInd>CRDT</CdtDbtInd><Sts><Cd>BOOK</Cd></Sts><BookgDt><Dt>2020-02-05</Dt></BookgDt><ValDt><Dt>2020-02-05</Dt></ValDt>'
        . '<NtryDtls><TxDtls><RmtInf><Ustrd>GUTSCHRIFT TESTZAHLUNG</Ustrd></RmtInf><RltdPties><Dbtr><Pty><Nm>SENDER NAME</Nm></Pty></Dbtr></RltdPties></TxDtls></NtryDtls></Ntry>'
        . '<Ntry><Amt Ccy="EUR">42.00</Amt><CdtDbtInd>DBIT</CdtDbtInd><Sts><Cd>BOOK</Cd></Sts><BookgDt><Dt>2020-02-05</Dt></BookgDt><ValDt><Dt>2020-02-06</Dt></ValDt>'
        . '<NtryDtls><TxDtls><RmtInf><Ustrd>MIETE FEBRUAR</Ustrd></RmtInf><RltdPties><Cdtr><Pty><Nm>EMPFAENGER NAME</Nm></Pty></Cdtr></RltdPties></TxDtls></NtryDtls></Ntry>'
        . '<Ntry><Amt Ccy="EUR">9.99</Amt><CdtDbtInd>DBIT</CdtDbtInd><Sts><Cd>PDNG</Cd></Sts><BookgDt><Dt>2020-02-05</Dt></BookgDt><ValDt><Dt>2020-02-05</Dt></ValDt>'
        . '<NtryDtls><TxDtls><RmtInf><Ustrd>KARTENZAHLUNG VORGEMERKT</Ustrd></RmtInf><RltdPties><Cdtr><Pty><Nm>SUPERMARKT</Nm></Pty></Cdtr></RltdPties></TxDtls></NtryDtls></Ntry>'
        . '</Rpt></BkToCstmrAcctRpt></Document>';
}
