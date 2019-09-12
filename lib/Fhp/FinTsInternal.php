<?php

namespace Fhp;

use Fhp\DataTypes\Kik;
use Fhp\DataTypes\Kti;
use Fhp\Dialog\Dialog;
use Fhp\Message\AbstractMessage;
use Fhp\Message\Message;
use Fhp\Model\SEPAAccount;
use Fhp\Model\SEPAStandingOrder;
use Fhp\Response\GetTANRequest;
use Fhp\Segment\HKCDL;
use Fhp\Segment\HKCCS;
use Fhp\Segment\HKTAN;
use Fhp\DataTypes\Ktv;
use Fhp\Segment\HKKAZ;
use Fhp\Segment\HKCAZ;

/**
 * Class FinTsInternal.
 *
 * @package Fhp
 */
class FinTsInternal {
    protected $url;
    /** @var int */
    protected $port;
    /** @var  Connection */
    protected $connection;
	/** @var int */
	protected $timeoutConnect = 15;
	/** @var int */
	protected $timeoutResponse = 30;
	
	protected function startDeleteSEPAStandingOrder(SEPAAccount $account, SEPAStandingOrder $order){
       $dialog = $this->getDialog();
        #$dialog->syncDialog();
        #$dialog->initDialog();

		$hkcdbAccount = new Kti(
			$account->getIban(),
			$account->getBic(),
			$account->getAccountNumber(),
			$account->getSubAccount(),
			new Kik(280, $account->getBlz())
		);

        $message = new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $dialog->getSystemId(),
            $dialog->getDialogId(),
            $dialog->getMessageNumber(),
            array(
                new HKCDL(HKCDL::VERSION, 3, $hkcdbAccount, "urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.03", $order),
				new HKTAN(HKTAN::VERSION, 4)
            ),
            array(
                AbstractMessage::OPT_PINTAN_MECH => $this->getUsedPinTanMechanism($dialog)
            )
        );

        $response = $dialog->sendMessage($message);
        return new GetTANRequest($response->rawResponse, $dialog);
	}

	/**
	 * @param SEPAAccount $account
	 * @param string $painMessage
	 * @return GetTANRequest
	 */
	protected function startSEPATransfer(SEPAAccount $account, $painMessage) {
		$painMessage = $this->clearXML($painMessage);
		
        $dialog = $this->getDialog();
        #$dialog->syncDialog();
        #$dialog->initDialog();

		$hkcdbAccount = new Kti(
			$account->getIban(),
			$account->getBic(),
			$account->getAccountNumber(),
			$account->getSubAccount(),
			new Kik(280, $account->getBlz())
		);

        $message = new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $dialog->getSystemId(),
            $dialog->getDialogId(),
            $dialog->getMessageNumber(),
            array(
                new HKCCS(HKCCS::VERSION, 3, $hkcdbAccount, "urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.003.03", $painMessage),
				new HKTAN(HKTAN::VERSION, 4)
            ),
            array(
                AbstractMessage::OPT_PINTAN_MECH => $this->getUsedPinTanMechanism($dialog)
            )
        );
		
		$this->logger->info('');
		$this->logger->info('HKCCS (SEPA Einzelüberweisung) initialize');
        $response = $dialog->sendMessage($message);
		$this->logger->info('HKCCS end');
		
        return new GetTANRequest($response->rawResponse, $dialog);
	}
	
    /**
     * Helper method to retrieve a pre configured message object.
     * Factory for poor people :)
     *
     * @param Dialog $dialog
     * @param array $segments
     * @param array $options
     * @return Message
     */
    protected function getNewMessage(Dialog $dialog, array $segments, array $options) {
        return new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $dialog->getSystemId(),
            $dialog->getDialogId(),
            $dialog->getMessageNumber(),
            $segments,
            $options
        );
    }

    /**
     * Helper method to retrieve a pre configured dialog object.
     * Factory for poor people :)
     *
     * @param boolean
     * @return Dialog
     * @throws \Exception
     */
    protected function getDialog($sync = true) {
		if ($this->dialog)
			return $this->dialog;
			
		if (!$this->connection)
			$this->connection = new Connection($this->url, $this->port, $this->timeoutConnect, $this->timeoutResponse);
		
        $D = new Dialog(
            $this->connection,
            $this->bankCode,
            $this->username,
            $this->pin,
            $this->systemId,
            $this->logger,
            $this->productName,
            $this->productVersion
        );

		if ($sync)
	        $D->syncDialog(false);
		
		$this->dialog = $D;
		
		return $this->dialog;
    }

    /**
     * Needed for escaping userdata.
     * HBCI escape char is "?"
     *
     * @param string $string
     * @return string
     */
    public static function escapeString($string) {
        return str_replace(
            array('?', '@', ':', '+', '\''),
            array('??', '?@', '?:', '?+', '?\''),
            $string
        );
    }
	
	protected function clearXML($xml) {
		$dom = new \DOMDocument;
		$dom->preserveWhiteSpace = FALSE;
		$dom->loadXML($xml);
		$dom->formatOutput = false;
		return $dom->saveXml();
	}
	
	protected function getUsedPinTanMechanism($dialog) {
		if($this->tanMechanism !== null AND in_array($this->tanMechanism, $dialog->getSupportedPinTanMechanisms()))
			return array($this->tanMechanism);
		
		return $dialog->getSupportedPinTanMechanisms();
	}
	

    /**
     * Helper method to create a "Statement of Account Message".
     *
     * @param Dialog $dialog
     * @param SEPAAccount $account
     * @param \DateTime $from
     * @param \DateTime $to
     * @param string|null $touchdown
     * @return Message
     * @throws \Exception
     */
    protected function createHKCAZMessage(Dialog $dialog, SEPAAccount $account, \DateTime $from, \DateTime $to, $touchdown = null)
    {
        $kti = new Kti(
            $account->getIban(),
            $account->getBic(),
            $account->getAccountNumber(),
            $account->getSubAccount(),
            new Kik(280, $account->getBlz())
        );

        $message = $this->getNewMessage(
            $dialog,
            array(
                new HKCAZ(
                    1,
                    3,
                    $kti,
                    self::escapeString(HKCAZ::CAMT_FORMAT_FQ),
                    HKCAZ::ALL_ACCOUNTS_N,
                    $from,
                    $to,
                    $touchdown
                )
            ),
            array(AbstractMessage::OPT_PINTAN_MECH => $this->getUsedPinTanMechanism($dialog))
        );

        return $message;
    }
	
    /**
     * Helper method to create a "Statement of Account Message".
     *
     * @param Dialog $dialog
     * @param SEPAAccount $account
     * @param \DateTime $from
     * @param \DateTime $to
     * @param string|null $touchdown
     * @return Message
     * @throws \Exception
     */
    protected function createStateOfAccountMessage(
        Dialog $dialog,
        SepaAccount $account,
        \DateTime $from,
        \DateTime $to,
        $touchdown = null
    ) {
        // version 4, 5, 6, 7

        // version 5
        /*
            1 Segmentkopf                   DEG         M 1
            2 Kontoverbindung Auftraggeber  DEG ktv #   M 1
            3 Alle Konten                   DE  jn  #   M 1
            4 Von Datum                     DE dat  #   K 1
            5 Bis Datum                     DE dat  #   K 1
            6 Maximale Anzahl Einträge      DE num ..4  K 1 >0
            7 Aufsetzpunkt                  DE an ..35  K 1
         */

        // version 6
        /*
            1 Segmentkopf                   1 DEG           M 1
            2 Kontoverbindung Auftraggeber  2 DEG ktv #     M 1
            3 Alle Konten                   1 DE jn #       M 1
            4 Von Datum                     1 DE dat #      O 1
            5 Bis Datum                     1 DE dat #      O 1
            6 Maximale Anzahl Einträge      1 DE num ..4    C 1 >0
            7 Aufsetzpunkt                  1 DE an ..35    C 1
         */

        // version 7
        /*
            1 Segmentkopf                   1 DEG       M 1
            2 Kontoverbindung international 1 DEG kti # M 1
            3 Alle Konten                   1 DE jn #   M 1
            4 Von Datum                     1 DE dat #  O 1
            5 Bis Datum                     1 DE dat #  O 1
            6 Maximale Anzahl Einträge      1 DE num ..4 C 1 >0
            7 Aufsetzpunkt                  1 DE an ..35 C 1
         */

        switch ($dialog->getHkkazMaxVersion()) {
            case 4:
            case 5:
                $konto = new Deg();
                $konto->addDataElement($account->getAccountNumber());
                $konto->addDataElement($account->getSubAccount());
                $konto->addDataElement(static::DEFAULT_COUNTRY_CODE);
                $konto->addDataElement($account->getBlz());
                break;
            case 6:
                $konto = new Ktv(
                    $account->getAccountNumber(),
                    $account->getSubAccount(),
                    new Kik(280, $account->getBlz())
                );
                break;
            case 7:
                $konto = new Kti(
                    $account->getIban(),
                    $account->getBic(),
                    $account->getAccountNumber(),
                    $account->getSubAccount(),
                    new Kik(280, $account->getBlz())
                );
                break;
            default:
                throw new \Exception('Unsupported HKKAZ version: ' . $dialog->getHkkazMaxVersion());
        }

        $message = $this->getNewMessage(
            $dialog,
            array(
                new HKKAZ(
                    $dialog->getHkkazMaxVersion(),
                    3,
                    $konto,
                    HKKAZ::ALL_ACCOUNTS_N,
                    $from,
                    $to,
                    $touchdown
                ),
				new HKTAN(6, 4)
            ),
            array(AbstractMessage::OPT_PINTAN_MECH => $this->getUsedPinTanMechanism($dialog))
        );

        return $message;
    }
}
