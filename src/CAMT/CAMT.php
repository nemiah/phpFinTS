<?php

namespace Fhp\CAMT;

use Fhp\MT940\MT940;

/**
 * Parser for CAMT XML format (ISO 20022)
 * Supports camt.052 (Account Report) format versions
 */
class CAMT
{
    /**
     * Parse CAMT XML string(s) into an array structure compatible with MT940 parser output
     *
     * @param string[] $xmlStrings Array of XML strings (one or more CAMT documents)
     * @return array Associative array with dates as keys, containing statement and transaction data
     */
    public function parse(array $xmlStrings): array
    {
        $result = [];

        foreach ($xmlStrings as $xmlString) {
            if (empty($xmlString)) {
                continue;
            }

            // Try to load the XML
            $previousValue = libxml_use_internal_errors(true);
            $doc = simplexml_load_string($xmlString);
            libxml_use_internal_errors($previousValue);

            if ($doc === false) {
                continue; // Skip invalid XML
            }

            // Register namespace - support multiple camt versions
            $namespaces = $doc->getNamespaces(true);
            $ns = null;
            foreach ($namespaces as $prefix => $uri) {
                if (str_contains($uri, 'camt.052')) {
                    $ns = $uri;
                    break;
                }
            }

            if ($ns === null) {
                continue; // Not a camt.052 document
            }

            $doc->registerXPathNamespace('c', $ns);

            // Parse each report
            $reports = $doc->xpath('//c:Rpt');
            if ($reports === false) {
                continue;
            }

            foreach ($reports as $report) {
                $this->parseReport($report, $ns, $result);
            }
        }

        return $result;
    }

    /**
     * Parse a single report (Rpt) element
     *
     * @param string $ns Namespace URI
     * @param array &$result Result array to populate
     */
    private function parseReport(\SimpleXMLElement $report, string $ns, array &$result): void
    {
        $report->registerXPathNamespace('c', $ns);

        // Get account balances
        $balances = $this->parseBalances($report, $ns);

        // Parse entries (transactions)
        $entries = $report->xpath('.//c:Ntry');
        if ($entries === false) {
            return;
        }

        foreach ($entries as $entry) {
            $entry->registerXPathNamespace('c', $ns);
            $transaction = $this->parseEntry($entry, $ns);

            if ($transaction === null) {
                continue;
            }

            // Group transactions by booking date
            $dateKey = $transaction['booking_date'];
            if (!isset($result[$dateKey])) {
                $result[$dateKey] = [
                    'start_balance' => $balances,
                    'transactions' => [],
                ];
            }

            $result[$dateKey]['transactions'][] = $transaction;
        }

        // If we have balances but no transactions, still create an entry
        if (!empty($balances) && empty($entries)) {
            $dateKey = $balances['date'] ?? date('Y-m-d');
            if (!isset($result[$dateKey])) {
                $result[$dateKey] = [
                    'start_balance' => $balances,
                    'transactions' => [],
                ];
            }
        }

        // Set end balances
        foreach ($result as $dateKey => &$statement) {
            if (!isset($statement['end_balance']) && !empty($balances)) {
                $statement['end_balance'] = [
                    'amount' => $balances['amount'] ?? 0,
                    'credit_debit' => $balances['credit_debit'] ?? MT940::CD_CREDIT,
                    'date' => $dateKey,
                ];
            }
        }
    }

    /**
     * Parse balance information from report
     *
     * @return array Balance information
     */
    private function parseBalances(\SimpleXMLElement $report, string $ns): array
    {
        $report->registerXPathNamespace('c', $ns);

        // Try to find opening balance (OPBD) or closing balance (CLBD)
        $balances = $report->xpath('.//c:Bal');
        if ($balances === false || empty($balances)) {
            return [];
        }

        $result = [];
        foreach ($balances as $balance) {
            $balance->registerXPathNamespace('c', $ns);

            $type = (string) $balance->xpath('.//c:Tp/c:CdOrPrtry/c:Cd')[0] ?? '';
            $amount = (float) ($balance->xpath('.//c:Amt')[0] ?? 0);
            $currency = (string) ($balance->xpath('.//c:Amt/@Ccy')[0] ?? 'EUR');
            $creditDebit = (string) ($balance->xpath('.//c:CdtDbtInd')[0] ?? 'CRDT');
            $date = (string) ($balance->xpath('.//c:Dt/c:Dt')[0] ?? '');

            // Use opening balance if available
            if ($type === 'OPBD' || empty($result)) {
                $result = [
                    'amount' => $amount,
                    'currency' => $currency,
                    'credit_debit' => $creditDebit === 'DBIT' ? MT940::CD_DEBIT : MT940::CD_CREDIT,
                    'date' => $date,
                ];
            }
        }

        return $result;
    }

    /**
     * Parse a single entry (transaction) from CAMT XML
     *
     * @return array|null Transaction data or null if parsing fails
     */
    private function parseEntry(\SimpleXMLElement $entry, string $ns): ?array
    {
        $entry->registerXPathNamespace('c', $ns);

        // Get booking date
        $bookingDate = (string) ($entry->xpath('.//c:BookgDt/c:Dt')[0] ?? $entry->xpath('.//c:BookgDt/c:DtTm')[0] ?? '');
        if (empty($bookingDate)) {
            return null;
        }

        // Parse date - handle both date and datetime formats
        if (str_contains($bookingDate, 'T')) {
            $bookingDate = substr($bookingDate, 0, 10);
        }

        // Get value date
        $valutaDate = (string) ($entry->xpath('.//c:ValDt/c:Dt')[0] ?? $entry->xpath('.//c:ValDt/c:DtTm')[0] ?? $bookingDate);
        if (str_contains($valutaDate, 'T')) {
            $valutaDate = substr($valutaDate, 0, 10);
        }

        // Get amount
        $amount = (float) ($entry->xpath('.//c:Amt')[0] ?? 0);

        // Get credit/debit indicator
        $creditDebit = (string) ($entry->xpath('.//c:CdtDbtInd')[0] ?? 'CRDT');
        $creditDebit = $creditDebit === 'DBIT' ? MT940::CD_DEBIT : MT940::CD_CREDIT;

        // Check if it's a reversal/storno
        $reversalIndicator = (string) ($entry->xpath('.//c:RvslInd')[0] ?? 'false');
        $isStorno = strtolower($reversalIndicator) === 'true';

        // Get status - check if booked or pending
        $status = (string) ($entry->xpath('.//c:Sts')[0] ?? 'BOOK');
        $booked = strtoupper($status) === 'BOOK';

        // Parse transaction details
        $details = $this->parseEntryDetails($entry, $ns);

        return [
            'booking_date' => $bookingDate,
            'valuta_date' => $valutaDate,
            'amount' => $amount,
            'credit_debit' => $creditDebit,
            'is_storno' => $isStorno,
            'booked' => $booked,
            'description' => $details,
        ];
    }

    /**
     * Parse detailed transaction information
     *
     * @return array Transaction details
     */
    private function parseEntryDetails(\SimpleXMLElement $entry, string $ns): array
    {
        $entry->registerXPathNamespace('c', $ns);

        $details = [
            'booking_code' => '',
            'booking_text' => '',
            'description_1' => '',
            'description_2' => '',
            'description' => [],
            'bank_code' => '',
            'account_number' => '',
            'name' => '',
            'primanoten_nr' => '',
            'text_key_addition' => '',
        ];

        // Get transaction details from NtryDtls/TxDtls
        $txDetails = $entry->xpath('.//c:NtryDtls/c:TxDtls');
        if ($txDetails === false || empty($txDetails)) {
            return $details;
        }

        $txDetail = $txDetails[0];
        $txDetail->registerXPathNamespace('c', $ns);

        // Get booking code and text from BkTxCd
        $this->parseBookingCode($txDetail, $ns, $details);

        // Get remittance information (Verwendungszweck)
        $remittanceInfo = $txDetail->xpath('.//c:RmtInf');
        if ($remittanceInfo !== false && !empty($remittanceInfo)) {
            $remittanceInfo[0]->registerXPathNamespace('c', $ns);
            $this->parseRemittanceInfo($remittanceInfo[0], $ns, $details);
        }

        // Get counterparty information
        $relatedParties = $txDetail->xpath('.//c:RltdPties');
        if ($relatedParties !== false && !empty($relatedParties)) {
            $relatedParties[0]->registerXPathNamespace('c', $ns);
            $this->parseRelatedParties($relatedParties[0], $ns, $details);
        }

        // Get agent information (BIC codes)
        $relatedAgents = $txDetail->xpath('.//c:RltdAgts');
        if ($relatedAgents !== false && !empty($relatedAgents)) {
            $relatedAgents[0]->registerXPathNamespace('c', $ns);
            $this->parseRelatedAgents($relatedAgents[0], $ns, $details);
        }

        // Get references (EREF, MREF, CRED, etc.)
        $refs = $txDetail->xpath('.//c:Refs');
        if ($refs !== false && !empty($refs)) {
            $refs[0]->registerXPathNamespace('c', $ns);
            $this->parseReferences($refs[0], $ns, $details);
        }

        // Get mandate information and creditor ID
        $this->parseMandateInfo($txDetail, $ns, $details);

        return $details;
    }

    /**
     * Parse booking code and text
     */
    private function parseBookingCode(\SimpleXMLElement $txDetail, string $ns, array &$details): void
    {
        $txDetail->registerXPathNamespace('c', $ns);

        // Get domain code
        $domainCode = (string) ($txDetail->xpath('.//c:BkTxCd/c:Domn/c:Cd')[0] ?? '');
        $details['booking_code'] = $domainCode;

        // Get family and subfamily codes
        $family = (string) ($txDetail->xpath('.//c:BkTxCd/c:Domn/c:Fmly/c:Cd')[0] ?? '');
        $subfamily = (string) ($txDetail->xpath('.//c:BkTxCd/c:Domn/c:Fmly/c:SubFmlyCd')[0] ?? '');

        // Get proprietary code (often more descriptive)
        $proprietary = (string) ($txDetail->xpath('.//c:BkTxCd/c:Prtry/c:Cd')[0] ?? '');
        $proprietaryIssuer = (string) ($txDetail->xpath('.//c:BkTxCd/c:Prtry/c:Issr')[0] ?? '');

        // Build booking text - prefer proprietary code as it's more descriptive
        if (!empty($proprietary)) {
            $details['booking_text'] = $proprietary;

            // Extract booking code and text key addition from proprietary code
            // Format is often like: NTRF+118+05801 or NDDT+105+00931
            if (preg_match('/([A-Z]{4})\+(\d{3})\+(\d{5})/', $proprietary, $matches)) {
                $details['booking_code'] = $matches[2];
                $details['text_key_addition'] = $matches[3];
            } elseif (preg_match('/(\d{3})/', $proprietary, $matches)) {
                // Fallback: just extract the 3-digit code
                if (empty($details['booking_code'])) {
                    $details['booking_code'] = $matches[1];
                }
            }
        } elseif (!empty($family) || !empty($subfamily)) {
            $details['booking_text'] = trim($family . ' ' . $subfamily);
        }
    }

    /**
     * Parse remittance information
     */
    private function parseRemittanceInfo(\SimpleXMLElement $remittanceInfo, string $ns, array &$details): void
    {
        $remittanceInfo->registerXPathNamespace('c', $ns);

        // Unstructured remittance info - this is the main "Verwendungszweck"
        $unstructured = $remittanceInfo->xpath('.//c:Ustrd');
        if ($unstructured !== false && !empty($unstructured)) {
            $ustrd = (string) $unstructured[0];

            // Parse structured SEPA fields from unstructured text
            $structuredFields = $this->extractStructuredFieldsFromText($ustrd);

            // Extract BIC and IBAN if present in text and not yet set
            if (empty($details['bank_code']) && preg_match('/BIC:\s*([A-Z0-9]{8,11})/', $ustrd, $matches)) {
                $details['bank_code'] = $matches[1];
            }
            if (empty($details['account_number']) && preg_match('/IBAN:\s*([A-Z]{2}[0-9]{2}[A-Z0-9]+)/', $ustrd, $matches)) {
                $details['account_number'] = $matches[1];
            }

            // Set SVWZ (main description) - extract clean text without structured fields
            $cleanText = $this->removeStructuredFieldsFromText($ustrd);
            $details['description']['SVWZ'] = trim($cleanText);
            $details['description_1'] = trim($cleanText);

            // Merge extracted structured fields
            $details['description'] = array_merge($details['description'], $structuredFields);
        }

        // Structured remittance info
        $structured = $remittanceInfo->xpath('.//c:Strd/c:CdtrRefInf/c:Ref');
        if ($structured !== false && !empty($structured)) {
            $details['description_2'] = (string) $structured[0];
        }
    }

    /**
     * Parse related parties (Debtor/Creditor)
     */
    private function parseRelatedParties(\SimpleXMLElement $relatedParties, string $ns, array &$details): void
    {
        // Use children() to access elements with namespace
        $children = $relatedParties->children($ns);

        // Try without namespace as fallback
        $childrenNoNs = $relatedParties->children();

        // Debtor information
        $debtorName = '';
        $debtorAccount = '';
        $debtorOtherAccount = '';

        // Try with namespace first, then without
        if (isset($children->Dbtr)) {
            $dbtr = $children->Dbtr->children($ns);
            if (isset($dbtr->Pty)) {
                $pty = $dbtr->Pty->children($ns);
                $debtorName = (string) ($pty->Nm ?? '');
            } else {
                $debtorName = (string) ($dbtr->Nm ?? '');
            }
        } elseif (isset($childrenNoNs->Dbtr)) {
            $dbtr = $childrenNoNs->Dbtr->children();
            if (isset($dbtr->Pty)) {
                $pty = $dbtr->Pty->children();
                $debtorName = (string) ($pty->Nm ?? '');
            } else {
                $debtorName = (string) ($dbtr->Nm ?? '');
            }
        }
        if (isset($children->DbtrAcct)) {
            $dbtrAcct = $children->DbtrAcct->children($ns);
            if (isset($dbtrAcct->Id)) {
                $id = $dbtrAcct->Id->children($ns);
                $debtorAccount = (string) ($id->IBAN ?? '');
                if (empty($debtorAccount) && isset($id->Othr)) {
                    $othr = $id->Othr->children($ns);
                    $debtorOtherAccount = (string) ($othr->Id ?? '');
                }
            }
        } elseif (isset($childrenNoNs->DbtrAcct)) {
            $dbtrAcct = $childrenNoNs->DbtrAcct->children();
            if (isset($dbtrAcct->Id)) {
                $id = $dbtrAcct->Id->children();
                $debtorAccount = (string) ($id->IBAN ?? '');
                if (empty($debtorAccount) && isset($id->Othr)) {
                    $othr = $id->Othr->children();
                    $debtorOtherAccount = (string) ($othr->Id ?? '');
                }
            }
        }

        // Creditor information
        $creditorName = '';
        $creditorAccount = '';
        $creditorOtherAccount = '';

        if (isset($children->Cdtr)) {
            $cdtr = $children->Cdtr->children($ns);
            if (isset($cdtr->Pty)) {
                $pty = $cdtr->Pty->children($ns);
                $creditorName = (string) ($pty->Nm ?? '');
            } else {
                $creditorName = (string) ($cdtr->Nm ?? '');
            }
        } elseif (isset($childrenNoNs->Cdtr)) {
            $cdtr = $childrenNoNs->Cdtr->children();
            if (isset($cdtr->Pty)) {
                $pty = $cdtr->Pty->children();
                $creditorName = (string) ($pty->Nm ?? '');
            } else {
                $creditorName = (string) ($cdtr->Nm ?? '');
            }
        }
        if (isset($children->CdtrAcct)) {
            $cdtrAcct = $children->CdtrAcct->children($ns);
            if (isset($cdtrAcct->Id)) {
                $id = $cdtrAcct->Id->children($ns);
                $creditorAccount = (string) ($id->IBAN ?? '');
                if (empty($creditorAccount) && isset($id->Othr)) {
                    $othr = $id->Othr->children($ns);
                    $creditorOtherAccount = (string) ($othr->Id ?? '');
                }
            }
        } elseif (isset($childrenNoNs->CdtrAcct)) {
            $cdtrAcct = $childrenNoNs->CdtrAcct->children();
            if (isset($cdtrAcct->Id)) {
                $id = $cdtrAcct->Id->children();
                $creditorAccount = (string) ($id->IBAN ?? '');
                if (empty($creditorAccount) && isset($id->Othr)) {
                    $othr = $id->Othr->children();
                    $creditorOtherAccount = (string) ($othr->Id ?? '');
                }
            }
        }

        // Ultimate parties
        $ultimateDebtorName = '';
        $ultimateCreditorName = '';

        if (isset($children->UltmtDbtr)) {
            $ultDbtr = $children->UltmtDbtr->children($ns);
            if (isset($ultDbtr->Pty)) {
                $pty = $ultDbtr->Pty->children($ns);
                $ultimateDebtorName = (string) ($pty->Nm ?? '');
            } else {
                $ultimateDebtorName = (string) ($ultDbtr->Nm ?? '');
            }
        } elseif (isset($childrenNoNs->UltmtDbtr)) {
            $ultDbtr = $childrenNoNs->UltmtDbtr->children();
            if (isset($ultDbtr->Pty)) {
                $pty = $ultDbtr->Pty->children();
                $ultimateDebtorName = (string) ($pty->Nm ?? '');
            } else {
                $ultimateDebtorName = (string) ($ultDbtr->Nm ?? '');
            }
        }
        if (isset($children->UltmtCdtr)) {
            $ultCdtr = $children->UltmtCdtr->children($ns);
            if (isset($ultCdtr->Pty)) {
                $pty = $ultCdtr->Pty->children($ns);
                $ultimateCreditorName = (string) ($pty->Nm ?? '');
            } else {
                $ultimateCreditorName = (string) ($ultCdtr->Nm ?? '');
            }
        } elseif (isset($childrenNoNs->UltmtCdtr)) {
            $ultCdtr = $childrenNoNs->UltmtCdtr->children();
            if (isset($ultCdtr->Pty)) {
                $pty = $ultCdtr->Pty->children();
                $ultimateCreditorName = (string) ($pty->Nm ?? '');
            } else {
                $ultimateCreditorName = (string) ($ultCdtr->Nm ?? '');
            }
        }

        // Determine name priority
        $details['name'] = !empty($ultimateCreditorName) ? $ultimateCreditorName :
                          (!empty($ultimateDebtorName) ? $ultimateDebtorName :
                          (!empty($creditorName) ? $creditorName :
                          (!empty($debtorName) ? $debtorName : '')));

        // Set account
        $iban = !empty($creditorAccount) ? $creditorAccount :
                (!empty($debtorAccount) ? $debtorAccount :
                (!empty($creditorOtherAccount) ? $creditorOtherAccount : $debtorOtherAccount));
        $details['account_number'] = $iban;
    }

    /**
     * Parse related agents (BIC information)
     */
    private function parseRelatedAgents(\SimpleXMLElement $relatedAgents, string $ns, array &$details): void
    {
        // Use children() to access elements with namespace
        $children = $relatedAgents->children($ns);

        $debtorBIC = '';
        $creditorBIC = '';

        // Debtor Agent (for outgoing transactions)
        if (isset($children->DbtrAgt)) {
            $dbtrAgt = $children->DbtrAgt->children($ns);
            if (isset($dbtrAgt->FinInstnId)) {
                $finInstn = $dbtrAgt->FinInstnId->children($ns);
                $debtorBIC = (string) ($finInstn->BICFI ?? $finInstn->BIC ?? '');

                // Try ClrSysMmbId if BIC not found
                if (empty($debtorBIC) && isset($finInstn->ClrSysMmbId)) {
                    $clrSys = $finInstn->ClrSysMmbId->children($ns);
                    $debtorBIC = (string) ($clrSys->MmbId ?? '');
                }
            }
        }

        // Creditor Agent (for incoming transactions)
        if (isset($children->CdtrAgt)) {
            $cdtrAgt = $children->CdtrAgt->children($ns);
            if (isset($cdtrAgt->FinInstnId)) {
                $finInstn = $cdtrAgt->FinInstnId->children($ns);
                $creditorBIC = (string) ($finInstn->BICFI ?? $finInstn->BIC ?? '');

                // Try ClrSysMmbId if BIC not found
                if (empty($creditorBIC) && isset($finInstn->ClrSysMmbId)) {
                    $clrSys = $finInstn->ClrSysMmbId->children($ns);
                    $creditorBIC = (string) ($clrSys->MmbId ?? '');
                }
            }
        }

        // Set BIC - only if not already set
        if (empty($details['bank_code'])) {
            $details['bank_code'] = !empty($creditorBIC) ? $creditorBIC : $debtorBIC;
        }
    }

    /**
     * Parse references (EREF, MREF, CRED, etc.)
     */
    private function parseReferences(\SimpleXMLElement $refs, string $ns, array &$details): void
    {
        $refs->registerXPathNamespace('c', $ns);

        // End to end reference (EREF)
        $endToEndId = (string) ($refs->xpath('.//c:EndToEndId')[0] ?? '');
        if (!empty($endToEndId) && $endToEndId !== 'NOTPROVIDED' && $endToEndId !== 'NONE') {
            $details['description']['EREF'] = $endToEndId;
        }

        // Mandate reference (MREF)
        $mandateId = (string) ($refs->xpath('.//c:MndtId')[0] ?? '');
        if (!empty($mandateId)) {
            $details['description']['MREF'] = $mandateId;
        }

        // Creditor reference (KREF)
        $creditorRef = (string) ($refs->xpath('.//c:CdtrRef')[0] ?? '');
        if (!empty($creditorRef)) {
            $details['description']['KREF'] = $creditorRef;
        }

        // Account servicer reference
        $acctSvcrRef = (string) ($refs->xpath('.//c:AcctSvcrRef')[0] ?? '');
        if (!empty($acctSvcrRef)) {
            $details['primanoten_nr'] = $acctSvcrRef;
        }
    }

    /**
     * Parse mandate information and creditor scheme ID
     */
    private function parseMandateInfo(\SimpleXMLElement $txDetail, string $ns, array &$details): void
    {
        $txDetail->registerXPathNamespace('c', $ns);

        // Get creditor scheme identification from related parties
        $creditorSchemeId = (string) ($txDetail->xpath('.//c:RltdPties/c:Cdtr/c:Id/c:OrgId/c:Othr/c:Id')[0] ?? '');
        if (empty($creditorSchemeId)) {
            $creditorSchemeId = (string) ($txDetail->xpath('.//c:RltdPties/c:Cdtr/c:Id/c:PrvtId/c:Othr/c:Id')[0] ?? '');
        }
        if (empty($creditorSchemeId)) {
            // Try ultimate creditor
            $creditorSchemeId = (string) ($txDetail->xpath('.//c:RltdPties/c:UltmtCdtr/c:Id/c:OrgId/c:Othr/c:Id')[0] ?? '');
        }
        if (empty($creditorSchemeId)) {
            $creditorSchemeId = (string) ($txDetail->xpath('.//c:RltdPties/c:UltmtCdtr/c:Id/c:PrvtId/c:Othr/c:Id')[0] ?? '');
        }

        if (!empty($creditorSchemeId)) {
            $details['description']['CRED'] = $creditorSchemeId;
        }
    }

    /**
     * Extract structured SEPA fields from unstructured text
     *
     * @return array Structured fields found in text
     */
    private function extractStructuredFieldsFromText(string $text): array
    {
        $fields = [];

        // Common SEPA field patterns
        $patterns = [
            'EREF' => '/EREF\+([^\s]+)/',
            'MREF' => '/MREF\+([^\s]+)/',
            'CRED' => '/CRED\+([^\s]+)/',
            'DEBT' => '/DEBT\+([^\s]+)/',
            'SVWZ' => '/SVWZ\+(.+?)(?=(?:EREF|MREF|CRED|DEBT|\s*$))/',
            'KREF' => '/KREF\+([^\s]+)/',
            'IBAN' => '/IBAN\+([A-Z]{2}[0-9]{2}[A-Z0-9]+)/',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $fields[$key] = trim($matches[1]);
            }
        }

        return $fields;
    }

    /**
     * Remove structured SEPA fields from text to get clean description
     *
     * @return string Clean text without structured fields
     */
    private function removeStructuredFieldsFromText(string $text): string
    {
        // Remove common SEPA structured fields - allow space after colon
        $text = preg_replace('/\s*EREF:\s*[^\s]+/', '', $text);
        $text = preg_replace('/\s*MREF:\s*[^\s]+/', '', $text);
        $text = preg_replace('/\s*CRED:\s*[^\s]+/', '', $text);
        $text = preg_replace('/\s*DEBT:\s*[^\s]+/', '', $text);
        $text = preg_replace('/\s*KREF:\s*[^\s]+/', '', $text);
        $text = preg_replace('/\s*SVWZ:\s*/', '', $text);
        $text = preg_replace('/\s*IBAN:\s*[A-Z]{2}[0-9]{2}[A-Z0-9]+/', '', $text);
        $text = preg_replace('/\s*BIC:\s*[A-Z0-9]{8,11}/', '', $text);

        // Also handle + separator variants
        $text = preg_replace('/\s*EREF\+[^\s]+/', '', $text);
        $text = preg_replace('/\s*MREF\+[^\s]+/', '', $text);
        $text = preg_replace('/\s*CRED\+[^\s]+/', '', $text);
        $text = preg_replace('/\s*DEBT\+[^\s]+/', '', $text);
        $text = preg_replace('/\s*KREF\+[^\s]+/', '', $text);
        $text = preg_replace('/\s*SVWZ\+/', '', $text);

        // Clean up multiple spaces and trim
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}
