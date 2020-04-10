<?php

namespace Fhp\Protocol;

use Fhp\BaseAction;
use Fhp\Model\NoPsd2TanMode;
use Fhp\Model\TanMode;
use Fhp\Options\Credentials;
use Fhp\Options\FinTsOptions;
use Fhp\Segment\HISYN\HISYNv4;
use Fhp\Segment\HKIDN\HKIDNv2;
use Fhp\Segment\HKSYN\HKSYNv3;
use Fhp\Segment\HKVVB\HKVVBv3;
use Fhp\Segment\TAN\HKTANv6;

/**
 * Initializes a FinTs dialog. The dialog initialization message is usually the first message that should be sent over
 * the wire. The most basic form consists of HIKDN for authentication, and HKVVB to declare the current BPD/UPD versions
 * present at the client. The server responds with updated BPD/UPD data.
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: C.3
 *
 * An extended form of dialog initialization (called "synchronization") additionally requests a new Kundensystem-ID by
 * sending a HKSYN segment. The Kundensystem-ID identifies the application using the phpFinTS library plus the device on
 * which it runs.
 * This action automatically executes synchroniziation if `$kundensystemId=null` was passed to the constructor. In this
 * case, the opened dialog must not be used for any other (financial/business) actions, so the caller must call
 * {@link FinTs#endDialog()} immediately after executing a {@link DialogInitialization} without pre-existing
 * Kundensystem-ID.
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: C.8
 *
 * By default (`$hktanRef = 'HKIDN'`), the dialog is opened with strong authentication (PSD2), which requires that the
 * $tanMode has already been selected.
 * For special PIN/TAN management use cases (e.g. enumerating the available TAN media) that need to be executable
 * without strong authentication, or for synchronization (see above), the `$hktanRef` can be set to the segment
 * identifier of the PIN/TAN management transaction or null, respectively, to indicate weak authentication.
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: B.4.3.1 and B.4.3.3
 *
 * Rough overview of the initialization procedure with no prior information on the client side:
 * 1. Open connection.
 * 2. Initialize and close anonymously to retrieve BPD (HITANS, HIPINS, ...). This is implemented in
 *    {@link FinTs#ensureBpdAvailable()}.
 * 3. Initialize a dialog with $kundensystemId=null ("synchronization") and $hktanRef=null, and close it again. At this
 *    point, the allowed TAN modes are available, so the user can select a $tanMode.
 * 4. Optional: If the user needs to select a TAN medium, initialize another dialog with $hktanRef=HKTAB to execute
 *    {@link GetTanMedia}, and close it again. At this point, both $tanMode and $tanMedium are available.
 * 4. Initialize a strongly authenticated dialog (which possibly requires a TAN) to retrieve UPD.
 * 5. Now we're ready to execute business transactions.
 * Note that steps (2.) and (3.) can be skipped if the BPD/Kundensystem-ID are already present on the client side.
 */
class DialogInitialization extends BaseAction
{
    // These come from FinTs and are needed as inputs for the dialog initialization. They are NOT available after
    // serialization, i.e. not in processResponse().
    /** @var FinTsOptions */
    private $options;
    /** @var Credentials */
    private $credentials;
    /** @var TanMode|null */
    private $tanMode;
    /** @var string|null */
    private $tanMedium;

    /**
     * The segment that HKTAN points to. This implicitly defines what kind of dialog is initialized: null means weak
     * authentication, 'HKIDN' means strong authentication and other values initialize a special PIN/TAN dialog.
     * @var string
     */
    private $hktanRef;

    // This is the persistent state of the dialog initialization (can be both input and output).
    /** @var string|null */
    private $kundensystemId; // May be present initially. If not, will send HKSYN to obtain it.
    /** @var int|null */
    private $messageNumber; // Stored temporarily, to continue properly after TAN input.
    /** @var string|null */
    private $dialogId; // This is the main result.

    // Side results.
    /** @var UPD|null */
    private $upd;

    /**
     * @param TanMode|null $tanMode The TAN mode selected by the user.
     * @param string|null $tanMedium Possibly a TAN medium selected by the user.
     * @param string|null $kundensystemId The current Kundensystem-ID, if the client already has one.
     * @param string|null $hktanRef The segment to declare inside HKTAN.
     *     If this is null, a weakly authenticated dialog (with the TAN mode 999) will be initialized, which can only be
     *     used for synchronization and/or to retrieve BPD.
     *     If this is 'HKIDN', a regular, strongly authenticated dialog will be initialized, which may require a TAN.
     *     If it is one of the special PIN/TAN management segments (e.g. HKTAB), then the dialog does not have strong
     *     authentication (no TAN required) and can only be used for that one particular transaction.
     */
    public function __construct(FinTsOptions $options, Credentials $credentials, ?TanMode $tanMode, ?string $tanMedium, ?string $kundensystemId, ?string $hktanRef = 'HKIDN')
    {
        if ($hktanRef !== null && $tanMode === null) {
            throw new \InvalidArgumentException('hktanRef is ignored unless a tanMode is given');
        }
        $this->options = $options;
        $this->credentials = $credentials;
        $this->tanMode = $tanMode instanceof NoPsd2TanMode ? null : $tanMode;
        $this->tanMedium = $tanMedium;
        $this->kundensystemId = $kundensystemId;
        $this->hktanRef = $hktanRef;
    }

    public function serialize(): string
    {
        return serialize([
            parent::serialize(),
            $this->hktanRef,
            $this->kundensystemId,
            $this->messageNumber,
            $this->dialogId,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $parentSerialized,
            $this->hktanRef,
            $this->kundensystemId,
            $this->messageNumber,
            $this->dialogId
            ) = unserialize($serialized);
        parent::unserialize($parentSerialized);
    }

    /**
     * @param BPD|null $bpd The BPD. Note that we support null here because a dialog initialization is how the BPD can
     *     be obtained in the first place.
     * @param UPD|null $upd The UPD.
     * @return array|\Fhp\Segment\BaseSegment|\Fhp\Segment\BaseSegment[]
     */
    public function createRequest(?BPD $bpd, ?UPD $upd)
    {
        $request = [
            HKIDNv2::create($this->options->bankCode, $this->credentials, $this->kundensystemId ?? '0'),
            HKVVBv3::create($this->options, $bpd, $upd),
        ];
        if ($this->tanMode !== null) {
            $request[] = HKTANv6::createProzessvariante2Step1($this->tanMode, $this->tanMedium, $this->hktanRef ?? 'HKIDN');
        }

        if ($this->kundensystemId === null) {
            // NOTE: HKSYN must be *after* HKTAN.
            $request[] = HKSYNv3::createEmpty(); // See section C.8.1.1
        }
        return $request;
    }

    public function processResponse(Message $response)
    {
        parent::processResponse($response);
        $this->dialogId = $response->header->dialogId;

        if ($this->kundensystemId === null) {
            /** @var HISYNv4 $hisyn */
            $hisyn = $response->requireSegment(HISYNv4::class);
            if ($hisyn->kundensystemId === null) {
                throw new UnexpectedResponseException('No Kundensystem-ID received');
            }
            $this->kundensystemId = $hisyn->kundensystemId;
        }

        if (UPD::containedInResponse($response)) {
            $this->upd = UPD::extractFromResponse($response);
        }
    }

    public function isStronglyAuthenticated(): bool
    {
        return $this->hktanRef === 'HKIDN';
    }

    public function getKundensystemId(): ?string
    {
        return $this->kundensystemId;
    }

    public function getMessageNumber(): ?int
    {
        return $this->messageNumber;
    }

    public function setMessageNumber(?int $messageNumber): void
    {
        $this->messageNumber = $messageNumber;
    }

    public function getDialogId(): ?string
    {
        return $this->dialogId;
    }

    /**
     * To be called when a TAN is required for login, but we need to intermittently store the Dialog-ID.
     */
    public function setDialogId(?string $dialogId): void
    {
        $this->dialogId = $dialogId;
    }

    public function getUpd(): ?UPD
    {
        return $this->upd;
    }
}
