<?php

namespace Fhp\Tests\Unit\Action;

use Fhp\Action\GetSEPAAccounts;
use Fhp\Model\SEPAAccount;
use Fhp\Protocol\Message;
use Fhp\Protocol\UPD;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\SPA\HKSPAv2;

/**
 * GetSEPAAccounts annotates the accounts from HISPA with the descriptive fields of the matching HIUPD segment
 * (see https://github.com/nemiah/phpFinTS/pull/594).
 */
class GetSEPAAccountsTest extends ActionTestCase
{
    /**
     * `serialize()` of a GetSEPAAccounts whose HKSPA request has been built, as written by dd2a7f2, the last version
     * before the UPD was serialized with the action. It consists of the PaginateableAction state only. Applications
     * keep such actions in their caches while waiting for a TAN, so they are still around when this change is deployed.
     */
    private const SERIALIZED_BY_DD2A7F2 = 'O:26:"Fhp\\Action\\GetSEPAAccounts":3:{'
        . 'i:0;a:5:{i:0;a:1:{i:0;i:3;}i:1;N;i:2;N;i:3;N;i:4;N;}i:1;N;'
        . 'i:2;a:1:{i:0;O:23:"Fhp\\Segment\\SPA\\HKSPAv2":1:{i:0;s:10:"HKSPA:3:2\'";}}}';

    public function testAnnotatesAccountsFromTheUpd()
    {
        $action = GetSEPAAccounts::create();
        self::nextRequest($action, self::createBpd(self::hispas()), self::createUpd());
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
        self::nextRequest($action, self::createBpd(self::hispas()), $upd);
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
        self::nextRequest($action, self::createBpd(self::hispas()));
        $action->processResponse(self::accountsResponse());

        $this->assertCount(2, $action->getAccounts());
        $this->assertNull($action->getAccounts()[0]->getName());
    }

    /**
     * Some banks require a TAN for HKSPA. Applications then serialize the action while the user approves the TAN, and
     * the FinTs instance that completes it may come from persist(true), which contains no UPD. So the UPD captured in
     * createRequest() has to survive that round trip for the accounts to be annotated afterwards.
     */
    public function testUpdSurvivesSerialization()
    {
        $action = GetSEPAAccounts::create();
        $requestSegments = self::nextRequest($action, self::createBpd(self::hispas()), self::createUpd());
        $this->assertInstanceOf(HKSPAv2::class, $requestSegments[0]);

        /** @var GetSEPAAccounts $restored */
        $restored = unserialize(serialize($action));
        $restored->processResponse(self::accountsResponse());

        $giro = self::accountByIban($restored->getAccounts(), 'DE44500105175407324931');
        $this->assertSame('Mustermann Max', $giro->getName());
        $this->assertSame('Girokonto Komfort', $giro->getProductName());
    }

    public function testUnserializesActionsSerializedWithoutUpd()
    {
        /** @var GetSEPAAccounts $restored */
        $restored = unserialize(self::SERIALIZED_BY_DD2A7F2);
        $this->assertInstanceOf(GetSEPAAccounts::class, $restored);
        $this->assertSame([3], $restored->getRequestSegmentNumbers());
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

    private static function hispas(): BaseSegment
    {
        return BaseSegment::parse("HISPAS:6:2:4+1+1+1+J:N:N:N:urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.03'");
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
