<?php

namespace Fhp\Tests\Unit\Action;

use Fhp\Action\GetSEPAAccounts;
use Fhp\Model\SEPAAccount;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UPD;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\SPA\HKSPAv2;

/**
 * GetSEPAAccounts annotates the accounts from HISPA with the descriptive fields of the matching HIUPD segment
 * (see https://github.com/nemiah/phpFinTS/pull/594).
 */
class GetSEPAAccountsTest extends \PHPUnit\Framework\TestCase
{
    public function testAnnotatesAccountsFromTheUpd()
    {
        $action = GetSEPAAccounts::create();
        $action->getNextRequest(self::createBpd(), self::createUpd());
        $action->processResponse(self::accountsResponse());

        $accounts = $action->getAccounts();
        $this->assertCount(2, $accounts);

        $giro = self::accountByIban($accounts, 'DE44500105175407324931');
        $this->assertSame('Mustermann Max', $giro->getName());
        $this->assertSame('Girokonto Komfort', $giro->getProductName());
        $this->assertSame('EUR', $giro->getCurrency());
        $this->assertSame(1, $giro->getAccountType());

        // The second HIUPD has no IBAN, so it is matched via the Kontonummer (Sparkasse style).
        $savings = self::accountByIban($accounts, 'DE21500105170123456789');
        $this->assertSame('Mustermann', $savings->getName());
        $this->assertSame('Sparkonto', $savings->getProductName());
        $this->assertSame('EUR', $savings->getCurrency());
        $this->assertSame(10, $savings->getAccountType());
    }

    public function testLeavesAccountsWithoutHiupdUnannotated()
    {
        $upd = new UPD();
        $upd->hiupd = [];

        $action = GetSEPAAccounts::create();
        $action->getNextRequest(self::createBpd(), $upd);
        $action->processResponse(self::accountsResponse());

        foreach ($action->getAccounts() as $account) {
            $this->assertSame('50010517', $account->getBlz());
            $this->assertNull($account->getName());
            $this->assertNull($account->getProductName());
            $this->assertNull($account->getCurrency());
            $this->assertNull($account->getAccountType());
        }
    }

    public function testWorksWithoutUpd()
    {
        $action = GetSEPAAccounts::create();
        $action->getNextRequest(self::createBpd(), null);
        $action->processResponse(self::accountsResponse());

        $this->assertCount(2, $action->getAccounts());
        $this->assertNull($action->getAccounts()[0]->getName());
    }

    /**
     * Some banks require a TAN for HKSPA. Applications then serialize the action while the user approves the TAN, so
     * the UPD captured in createRequest() has to survive that round trip for the accounts to be annotated afterwards.
     */
    public function testUpdSurvivesSerialization()
    {
        $action = GetSEPAAccounts::create();
        $request = $action->getNextRequest(self::createBpd(), self::createUpd());
        $this->assertInstanceOf(HKSPAv2::class, $request[0]);
        $request[0]->setSegmentNumber(3); // Normally done by FinTs::execute() before the segment is sent.

        /** @var GetSEPAAccounts $restored */
        $restored = unserialize(serialize($action));
        $restored->processResponse(self::accountsResponse());

        $giro = self::accountByIban($restored->getAccounts(), 'DE44500105175407324931');
        $this->assertSame('Mustermann Max', $giro->getName());
        $this->assertSame('Girokonto Komfort', $giro->getProductName());
    }

    /**
     * Actions serialized by a library version that did not carry the UPD along consist of the PaginateableAction
     * state only ([BaseAction state, pagination token, request segments]) and must still be readable.
     */
    public function testUnserializesActionsSerializedWithoutUpd()
    {
        $legacy = [
            [null, null, null, null, null], // BaseAction: request segment numbers, TAN request, TAN segment, polling info, VoP request
            null, // pagination token
            null, // request segments
        ];
        $blob = 'O:' . strlen(GetSEPAAccounts::class) . ':"' . GetSEPAAccounts::class . '":' . count($legacy) . ':{'
            . 'i:0;a:5:{i:0;N;i:1;N;i:2;N;i:3;N;i:4;N;}i:1;N;i:2;N;}';

        /** @var GetSEPAAccounts $restored */
        $restored = unserialize($blob);
        $this->assertInstanceOf(GetSEPAAccounts::class, $restored);
        $this->assertFalse($restored->isDone());

        $restored->processResponse(self::accountsResponse());
        $this->assertCount(2, $restored->getAccounts());
        $this->assertNull($restored->getAccounts()[0]->getName());
    }

    /**
     * @param SEPAAccount[] $accounts
     */
    private static function accountByIban(array $accounts, string $iban): SEPAAccount
    {
        foreach ($accounts as $account) {
            if ($account->getIban() === $iban) {
                return $account;
            }
        }
        throw new \AssertionError("No account with IBAN $iban");
    }

    private static function createBpd(): BPD
    {
        $bpd = new BPD();
        $hispas = BaseSegment::parse(
            "HISPAS:6:2:4+1+1+1+J:N:N:N:urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.03'"
        );
        $bpd->parameters['HISPAS'][$hispas->getVersion()] = $hispas;
        return $bpd;
    }

    private static function createUpd(): UPD
    {
        $upd = new UPD();
        $upd->hiupd = [
            BaseSegment::parse(
                "HIUPD:7:6:4+5407324931::280:50010517+DE44500105175407324931+9999999999+1+EUR+Mustermann+Max+Girokonto Komfort++HKSAK:1+HKKAZ:1+HKSPA:1'"
            ),
            BaseSegment::parse(
                "HIUPD:8:6:4+0123456789::280:50010517++9999999999+10+EUR+Mustermann++Sparkonto++HKSAK:1+HKKAZ:1+HKSPA:1'"
            ),
        ];
        return $upd;
    }

    private static function accountsResponse(): Message
    {
        return Message::createPlainMessage([
            BaseSegment::parse(
                'HISPA:5:2:3+J:DE44500105175407324931:INGDDEFFXXX:5407324931::280:50010517'
                . "+J:DE21500105170123456789:INGDDEFFXXX:0123456789::280:50010517'"
            ),
        ]);
    }
}
