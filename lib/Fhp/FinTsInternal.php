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

/**
 * Class FinTsInternal.
 *
 * @package Fhp
 */
class FinTsInternal {
    protected $server;
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
     * @return Dialog
     */
    protected function getDialog($sync = true) {
		if($this->dialog)
			return $this->dialog;
			
		if(!$this->connection)
			$this->connection = new Connection($this->server, $this->port, $this->timeoutConnect, $this->timeoutResponse);
		
        $D = new Dialog(
            $this->connection,
            $this->bankCode,
            $this->username,
            $this->pin,
            $this->systemId,
            $this->logger
        );
		
		if($sync)
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
    protected function escapeString($string) {
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
}
