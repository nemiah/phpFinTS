<?php

namespace Fhp;

use Fhp\Model\NoPsd2TanMode;
use Fhp\Model\TanMedium;
use Fhp\Model\TanMode;
use Fhp\Options\Credentials;
use Fhp\Options\FinTsOptions;
use Fhp\Options\SanitizingLogger;
use Fhp\Protocol\BPD;
use Fhp\Protocol\DialogInitialization;
use Fhp\Protocol\GetTanMedia;
use Fhp\Protocol\Message;
use Fhp\Protocol\MessageBuilder;
use Fhp\Protocol\ServerException;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Protocol\UPD;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\HIBPA\HIBPAv3;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\HKEND\HKENDv1;
use Fhp\Segment\HKIDN\HKIDNv2;
use Fhp\Segment\HKVVB\HKVVBv3;
use Fhp\Segment\TAN\HITAN;
use Fhp\Segment\TAN\HKTAN;
use Fhp\Segment\TAN\HKTANFactory;
use Fhp\Segment\TAN\HKTANv6;
use Fhp\Syntax\InvalidResponseException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * This is the main class of this library. Please see the Samples directory for how to use it.
 * This class is not thread-safe, do not call its funtions concurrently.
 */
class FinTs
{
    // Things we retrieved from the user / the calling application.
    /** @var FinTsOptions */
    private $options;
    /** @var Credentials|null */
    private $credentials;
    /** @var SanitizingLogger */
    private $logger;

    // The TAN mode and medium to be used for business transactions that require a TAN.
    /** @var TanMode|int|null */
    private $selectedTanMode;
    /** @var string|null This is a {@link TanMedium::getName()}, but we don't have the {@link TanMedium} instance. */
    private $selectedTanMedium;

    // State that persists across physical connections, dialogs and even PHP executions.
    /** @var BPD|null */
    private $bpd;
    /** @var int[]|null The IDs of the {@link TanMode}s from the BPD which the user is allowed to use. */
    private $allowedTanModes;
    /** @var UPD|null */
    private $upd;

    // State of the current connection/dialog with the bank.
    /** @var Connection|null */
    private $connection;
    /** @var string|null */
    private $kundensystemId;
    /** @var string|null */
    protected $dialogId;
    /** @var int */
    private $messageNumber = 1;

    /**
     * Use this factory to create new instances.
     * @param FinTsOptions $options Configuration options for the connection to the bank.
     * @param Credentials $credentials Authentication information for the user. Note: This library does not support
     *     anonymous connections, so the credentials are mandatory.
     * @param string|null $persistedInstance The return value of {@link persist()} of a previous FinTs instance,
     *     usually from an earlier PHP execution. Passing this in here saves 1-2 dialogs that are normally made with the
     *     bank to obtain the BPD and Kundensystem-ID.
     */
    public static function new(FinTsOptions $options, Credentials $credentials, ?string $persistedInstance = null): FinTs
    {
        $options->validate();
        $fints = new static($options, $credentials);

        if ($persistedInstance !== null) {
            $fints->loadPersistedInstance($persistedInstance);
        }
        return $fints;
    }

    /**
     * This function allows to fetch the BPD without knowing the user's credentials yet, by using an anonymous dialog.
     * Note: If this fails with an error saying that your bank does not support the anonymous dialog, you probably need
     * to use {@link NoPsd2TanMode} for regular login.
     * @param FinTsOptions $options Configuration options for the connection to the bank.
     * @param ?LoggerInterface $logger An optional logger to record messages exchanged with the bank.
     * @return BPD Bank parameters that tell the client software what features the bank supports.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws UnexpectedResponseException When the server does not send the BPD or close the dialog properly.
     * @throws ServerException When the server resopnds with an error.
     */
    public static function fetchBpd(FinTsOptions $options, ?LoggerInterface $logger = null): BPD
    {
        $options->validate();
        $fints = new static($options, null);
        if ($logger !== null) {
            $fints->setLogger($logger);
        }
        return $fints->getBpd();
    }

    /** Please use the factory above. */
    protected function __construct(FinTsOptions $options, ?Credentials $credentials)
    {
        $this->options = $options;
        $this->credentials = $credentials;
        $this->setLogger(new NullLogger());
    }

    /**
     * Destructing the object only disconnects. Please use {@link close()} if you want to properly "log out", i.e. end
     * the FinTs dialog. On the other hand, do *not* close in case you have serialized the FinTs instance and intend
     * to resume it later due to a TAN request.
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Returns a serialized form of parts of this object. This is different from PHP's `\Serializable` in that it only
     * serializes parts and cannot simply be restored with `unserialize()` because the `FinTsOptions` and the
     * `Credentials` need to be passed to FinTs::new() in addition to the string returned here.
     *
     * Alternatively you can use {@link loadPersistedInstance) to separate constructing the instance and resuming it.
     *
     * NOTE: Unless you're persisting this object to complete a TAN request later on, you probably want to log the user
     * out first by calling {@link close()}.
     *
     * @param bool $minimal If true, the return value only contains only those values that are necessary to complete an
     *     outstanding TAN request, but not the relatively large BPD/UPD, which can always be retrieved again later with
     *     a few extra requests to the server.
     * @return string A serialized form of those parts of the FinTs instance that can reasonably be persisted (BPD, UPD,
     *     Kundensystem-ID, etc.). Note that this usually contains some user data (user's name, account names and
     *     sometimes a dialog ID that is equivalent to session cookie), so the returned string needs to be treated
     *     carefully (not written to log files, only to a database or other storage system that would normally be used
     *     for user data). The returned string never contains highly sensitive information (not the user's password or
     *     PIN), so it probably does not need to be encrypted.
     */
    public function persist(bool $minimal = false): string
    {
        // IMPORTANT: Be sure not to include highly sensitive user information here.
        return serialize([ // This should match loadPersistedInstanceVersion1().
            2, // Version of the serialized format.
            $minimal ? null : $this->bpd,
            $minimal ? null : $this->allowedTanModes,
            $minimal ? null : $this->upd,
            $this->selectedTanMode,
            $this->selectedTanMedium,
            $this->kundensystemId,
            $this->dialogId,
            $this->messageNumber,
        ]);
    }

    public function __serialize(): array
    {
        throw new \LogicException('FinTs cannot be serialize()-ed, you should call persist() instead.');
    }

    public function __unserialize(array $data): void
    {
        throw new \LogicException(
            'FinTs cannot be unserialize()-ed, you should pass $persistedInstance to FinTs::new() instead.');
    }

    /**
     * Use this to continue a previous FinTs Instance, for example after a TAN was needed and PHP execution was ended to
     * obtain it from the user.
     *
     * @param string $persistedInstance The return value of {@link persist()} of a previous FinTs instance, usually
     *     from an earlier PHP execution.
     *
     * @throws \InvalidArgumentException
     */
    public function loadPersistedInstance(string $persistedInstance)
    {
        $unserialized = unserialize($persistedInstance);
        if (!is_array($unserialized) || count($unserialized) === 0) {
            throw new \InvalidArgumentException("Invalid persistedInstance: '$persistedInstance'");
        }
        $version = $unserialized[0];
        $data = array_slice($unserialized, 1);
        if ($version === 2) {
            $this->loadPersistedInstanceVersion2($data);
        } else {
            throw new \InvalidArgumentException("Unknown persistedInstace version: '{$unserialized[0]}''");
        }
    }

    private function loadPersistedInstanceVersion2(array $data)
    {
        list( // This should match persist().
            $this->bpd,
            $this->allowedTanModes,
            $this->upd,
            $this->selectedTanMode,
            $this->selectedTanMedium,
            $this->kundensystemId,
            $this->dialogId,
            $this->messageNumber
        ) = $data;
    }

    /** @noinspection PhpUnused */
    public function getLogger(): SanitizingLogger
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger The logger to use going forward. Note that it will be wrapped in a
     *     {@link SanitizingLogger} to protect sensitive information like usernames and PINs.
     */
    public function setLogger(LoggerInterface $logger): void
    {
        if ($logger instanceof SanitizingLogger) {
            $this->logger = $logger;
        } else {
            $this->logger = new SanitizingLogger($logger, [$this->options, $this->credentials]);
        }
    }

    /**
     * @param int $connectTimeout The number of seconds to wait before aborting a connection attempt to the bank server.
     * @param int $responseTimeout The number of seconds to wait before aborting a request to the bank server.
     * @noinspection PhpUnused
     */
    public function setTimeouts(int $connectTimeout, int $responseTimeout)
    {
        $this->options->timeoutConnect = $connectTimeout;
        $this->options->timeoutResponse = $responseTimeout;
    }

    /**
     * Executes a strongly authenticated login action and returns it. With some banks, this requires a TAN.
     * @return DialogInitialization A {@link BaseAction} for the outcome of the login. You should check whether a TAN is
     *     needed using {@link BaseAction::needsTan()} and, if so, finish the login by passing the {@link BaseAction}
     *     returned here to {@link submitTan()} or {@link checkDecoupledSubmission()}.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws UnexpectedResponseException When the server responds with a valid but unexpected message.
     * @throws ServerException When the server responds with a (FinTS-encoded) error message, which includes most things
     *     that can go wrong with the action itself, like wrong credentials, invalid IBANs, locked accounts, etc.
     */
    public function login(): DialogInitialization
    {
        $this->requireTanMode();
        $this->ensureSynchronized();
        $this->messageNumber = 1;
        $login = new DialogInitialization($this->options, $this->requireCredentials(), $this->getSelectedTanMode(),
            $this->selectedTanMedium, $this->kundensystemId);
        $this->execute($login);
        return $login;
    }

    /**
     * Executes an action. Be sure to {@link login()} first. See the `\Fhp\Action` package for actions that can be
     * executed with this function. Note that, after this function returns, the result of the action is stored inside
     * the action itself, so you need to check {@link BaseAction::needsTan()} to see if it needs a TAN before being
     * completed and use its getters in order to obtain the result. In case the action fails, the corresponding
     * exception will be thrown from this function.
     * @param BaseAction $action The action to be executed. Its {@link BaseAction::isDone()} status will be updated when
     *     this function returns successfully.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws UnexpectedResponseException When the server responds with a valid but unexpected message.
     * @throws ServerException When the server responds with a (FinTS-encoded) error message, which includes most things
     *     that can go wrong with the action itself, like wrong credentials, invalid IBANs, locked accounts, etc.
     */
    public function execute(BaseAction $action)
    {
        if ($this->dialogId === null && !($action instanceof DialogInitialization)) {
            throw new \RuntimeException('Need to login (DialogInitialization) before executing other actions');
        }

        $requestSegments = $action->getNextRequest($this->bpd, $this->upd);

        if (count($requestSegments) === 0) {
            return; // No request needed.
        }

        // Construct the full request message.
        $message = MessageBuilder::create()->add($requestSegments); // This fills in the segment numbers.
        if (!($this->getSelectedTanMode() instanceof NoPsd2TanMode)) {
            if (($needTanForSegment = $action->getNeedTanForSegment()) !== null) {
                $message->add(HKTANFactory::createProzessvariante2Step1(
                    $this->requireTanMode(), $this->selectedTanMedium, $needTanForSegment));
            }
        }
        $request = $this->buildMessage($message, $this->getSelectedTanMode());
        $action->setRequestSegmentNumbers(array_map(function ($segment) {
            /* @var BaseSegment $segment */
            return $segment->getSegmentNumber();
        }, $requestSegments));

        // Execute the request.
        $response = $this->sendMessage($request);
        $this->readBPD($response);

        // Detect if the bank wants a TAN.
        /** @var HITAN $hitan */
        $hitan = $response->findSegment(HITAN::class);
        if ($hitan !== null && $hitan->getAuftragsreferenz() !== HITAN::DUMMY_REFERENCE) {
            if ($hitan->tanProzess !== HKTAN::TAN_PROZESS_4) {
                throw new UnexpectedResponseException("Unsupported TAN request type $hitan->tanProzess");
            }
            if ($this->bpd === null || $this->kundensystemId === null) {
                throw new UnexpectedResponseException('Unexpected TAN request');
            }
            // NOTE: In case of a decoupled TAN mode, the response code 3955 must be present, but it seems useless to us.
            $action->setTanRequest($hitan);
            if ($action instanceof DialogInitialization) {
                $action->setDialogId($response->header->dialogId);
                $action->setMessageNumber($this->messageNumber);
            }
            return;
        }

        // If no TAN is needed, process the response normally, and maybe keep going for more pages.
        $this->processActionResponse($action, $response->filterByReferenceSegments($action->getRequestSegmentNumbers()));
        if ($action instanceof PaginateableAction && $action->hasMorePages()) {
            $this->execute($action);
        }
    }

    /**
     * For an action where {@link BaseAction::needsTan()} returns `true` and {@link TanMode::isDecoupled()} returns
     * `false`, this function sends the given $tan to the server in order to complete the action. This can be done
     * asynchronously, i.e. not in the same PHP process as the original {@link execute()} call.
     *
     * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2020-07-10_final_version.pdf
     * Section B.4.2.1.1
     *
     * @param BaseAction $action The action to be completed.
     * @param string $tan The TAN entered by the user.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws UnexpectedResponseException When the server responds with a valid but unexpected message.
     * @throws ServerException When the server responds with a (FinTS-encoded) error message, which includes most things
     *     that can go wrong with the action itself, like wrong credentials, invalid IBANs, locked accounts, etc.
     */
    public function submitTan(BaseAction $action, string $tan)
    {
        // Check the action's state.
        $tanRequest = $action->getTanRequest();
        if ($tanRequest === null) {
            throw new \InvalidArgumentException('This action does not need a TAN');
        }
        if ($action instanceof DialogInitialization) {
            if ($this->dialogId !== null) {
                throw new \RuntimeException('Cannot init another dialog.');
            }
            $this->dialogId = $action->getDialogId();
            $this->messageNumber = $action->getMessageNumber();
        }

        // Construct the request.
        $tanMode = $this->requireTanMode();
        if ($tanMode instanceof NoPsd2TanMode) {
            throw new \InvalidArgumentException('Cannot submit TAN when the bank does not support PSD2');
        }
        if ($tanMode->isDecoupled()) {
            throw new \InvalidArgumentException('Cannot submit TAN for a decoupled TAN mode');
        }
        $message = MessageBuilder::create()
            ->add(HKTANFactory::createProzessvariante2Step2($tanMode, $tanRequest->getProcessId()));
        $request = $this->buildMessage($message, $tanMode, $tan);

        // Execute the request.
        $response = $this->sendMessage($request);
        $this->readBPD($response);

        // Ensure that the TAN was accepted.
        /** @var HITAN $hitan */
        $hitan = $response->findSegment(HITAN::class);
        if ($hitan === null) {
            throw new UnexpectedResponseException('HITAN missing after submitting TAN');
        }
        if ($hitan->getTanProzess() !== HKTAN::TAN_PROZESS_2 // We only support the case "(B)" in the specification.
            || $hitan->getAuftragsreferenz() !== $tanRequest->getProcessId()) {
            throw new UnexpectedResponseException("Bank has not accepted TAN: $hitan");
        }
        $action->setTanRequest(null);

        // Process the response normally, and maybe keep going for more pages.
        $this->processActionResponse($action, $response->filterByReferenceSegments($action->getRequestSegmentNumbers()));
        if ($action instanceof PaginateableAction && $action->hasMorePages()) {
            $this->execute($action);
        }
    }

    /**
     * For an action where {@link BaseAction::needsTan()} returns `true` and {@link TanMode::isDecoupled()} returns
     * `true`, this function checks with the server whether the second factor authentication has been completed yet on
     * the secondary device of the user. If so, this completes the given action and returns `true`, otherwise it
     * returns `false` and the action remains in its previous, uncompleted state.
     * This function can be called asynchronously, i.e. not in the same PHP process as the original {@link execute()}
     * call, and also repeatedly subject to the delays specified in the {@link TanMode}.
     *
     * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2020-07-10_final_version.pdf
     * Section B.4.2.2
     *
     * @param BaseAction $action The action to be completed.
     * @return bool True if the decoupled authentication is done and the $action was completed. If false, the
     *     {@link TanRequest} inside the action has been updated, which *may* provide new/more instructions to the user,
     *     though probably it rarely does in practice.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws UnexpectedResponseException When the server responds with a valid but unexpected message.
     * @throws ServerException When the server responds with a (FinTS-encoded) error message, which includes most things
     *     that can go wrong with the action itself, like wrong credentials, invalid IBANs, locked accounts, etc.
     */
    public function checkDecoupledSubmission(BaseAction $action): bool
    {
        // Check the action's state.
        $tanRequest = $action->getTanRequest();
        if ($tanRequest === null) {
            throw new \InvalidArgumentException('This action is not awaiting decoupled confirmation');
        }
        if ($action instanceof DialogInitialization) {
            if ($this->dialogId !== null) {
                throw new \RuntimeException('Cannot init another dialog.');
            }
            $this->dialogId = $action->getDialogId();
            $this->messageNumber = $action->getMessageNumber();
        }

        // (2a) Construct the request.
        $tanMode = $this->requireTanMode();
        if ($tanMode instanceof NoPsd2TanMode) {
            throw new \InvalidArgumentException('Cannot check decoupled status when the bank does not support PSD2');
        }
        if (!$tanMode->isDecoupled()) {
            throw new \InvalidArgumentException('Cannot check decoupled status for a non-decoupled TAN mode');
        }
        $message = MessageBuilder::create()
            ->add(HKTANFactory::createProzessvariante2StepS($tanMode, $tanRequest->getProcessId()));
        $request = $this->buildMessage($message, $tanMode);

        // Execute the request.
        $response = $this->sendMessage($request);
        $this->readBPD($response);

        // Determine if the decoupled authentication has completed. See section B.4.2.2.1.
        // There is always at least one HITAN segment with TAN-Prozess=S and the reference ID.
        // (2b) The response code 3956 indicates that the authentication is still outstanding. There could also be more
        //      information for the user in the HITAN challenge field.
        // (2c) Note that we only support the (B) variant here. There is additionally supposed to be a HITAN segment
        //      with TAN-Prozess=2 and the reference ID to indicate that the authentication has completed, though not
        //      all banks actually send this, as they seem to consider the absence of 3956 as sufficient for signaling
        //      success. In this case, the response also contains the response segments for the executed action, if any.
        $hitanProcessS = null;
        /** @var HITAN $hitan */
        foreach ($response->findSegments(HITAN::class) as $hitan) {
            if ($hitan->getAuftragsreferenz() !== $tanRequest->getProcessId()) {
                throw new UnexpectedResponseException('Unexpected Auftragsreferenz: ' . $hitan->getAuftragsreferenz());
            }
            if ($hitan->getTanProzess() === HKTAN::TAN_PROZESS_S) {
                $hitanProcessS = $hitan;
            }
        }
        if ($hitanProcessS === null) {
            throw new UnexpectedResponseException('Missing HITAN with tanProzess=S in the response');
        }
        if ($response->findRueckmeldungen(Rueckmeldungscode::STARKE_KUNDENAUTHENTIFIZIERUNG_NOCH_AUSSTEHEND)) {
            // The decoupled submission isn't complete yet. Update the TAN request, as the bank may have sent additional
            // instructions.
            $action->setTanRequest($hitanProcessS);
            if ($action instanceof DialogInitialization) {
                $this->dialogId = null;
                $action->setMessageNumber($this->messageNumber);
            }
            return false;
        }

        // The decoupled submission is complete and the action's result is included in the response.
        $action->setTanRequest(null);
        // Process the response normally, and maybe keep going for more pages.
        $this->processActionResponse($action, $response->filterByReferenceSegments($action->getRequestSegmentNumbers()));
        if ($action instanceof PaginateableAction && $action->hasMorePages()) {
            $this->execute($action);
        }
        return true;
    }

    /**
     * Closes open dialog/connection if any. This instance remains usable.
     * @throws ServerException When closing the dialog fails.
     */
    public function close()
    {
        if ($this->dialogId !== null) {
            $this->endDialog();
        }
        $this->disconnect();
    }

    /**
     * Assumes that the dialog (if any is open) is gone. This can be called by the application using this library when
     * it just restored this FinTs instance from the persisted format after a long time, so that the dialog/session has
     * most likely been closed at the server side already.
     */
    public function forgetDialog()
    {
        $this->dialogId = null;
    }

    /**
     * Before executing any actions that might require two-step authentication (like fetching a statement or initiating
     * a wire transfer), the user needs to pick a {@link TanMode}. Note that this does not always imply that the user
     * actually needs to enter a TAN every time, but they need to have picked the mode so that the system knows how to
     * deliver a TAN, if necesssary.
     * @return TanMode[] The TAN modes that are available to the user, indexed by their IDs.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws UnexpectedResponseException When the server does not send the BPD, the Kundensystem-ID or the TAN modes
     *     like it should according to the protocol, or when the dialog is not closed properly.
     * @throws ServerException When the server responds with an error.
     */
    public function getTanModes(): array
    {
        $this->ensureTanModesAvailable();
        $result = array();
        foreach ($this->allowedTanModes as $tanModeId) {
            if (!array_key_exists($tanModeId, $this->bpd->allTanModes)) continue;
            $result[$tanModeId] = $this->bpd->allTanModes[$tanModeId];
        }
        return $result;
    }

    /**
     * For TAN modes where {@link TanMode::needsTanMedium()} returns true, the user additionally needs to pick a TAN
     * medium. This function returns a list of possible TAN media. Note that, depending on the bank, this list may
     * contain all the user's TAN media, or just the ones that are compatible with the given $tanMode.
     * @param TanMode|int $tanMode Either a {@link TanMode} instance obtained from {@link getTanModes()} or its ID.
     * @return TanMedium[] A list of possible TAN media.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws UnexpectedResponseException When the server does not send the BPD, the Kundensystem-ID or the TAN media
     *     (which includes the case where the server does not support enumerating TAN media, which is indicated by
     *     {@link TanMode::needsTanMedium()} returning false), or when the dialog is not closed properly.
     * @throws ServerException When the server responds with an error.
     */
    public function getTanMedia($tanMode): array
    {
        if ($this->dialogId !== null) {
            $this->endDialog();
        }
        $this->ensureBpdAvailable();
        $this->ensureSynchronized();
        $getTanMedia = new GetTanMedia();

        // Execute the GetTanMedia request with the $tanMode swapped in temporarily.
        $oldTanMode = $this->selectedTanMode;
        $oldTanMedium = $this->selectedTanMedium;
        $this->selectedTanMode = $tanMode instanceof TanMode ? $tanMode->getId() : $tanMode;
        $this->selectedTanMedium = '';
        try {
            $this->executeWeakDialogInitialization('HKTAB');
            $this->execute($getTanMedia);
            $this->endDialog();
            return $getTanMedia->getTanMedia();
        } catch (UnexpectedResponseException|CurlException|ServerException $e) {
            throw $e;
        } finally {
            $this->selectedTanMode = $oldTanMode;
            $this->selectedTanMedium = $oldTanMedium;
        }
    }

    /**
     * @param TanMode|int $tanMode Either a {@link TanMode} instance obtained from {@link getTanModes()} or its ID.
     * @param TanMedium|string|null $tanMedium If the $tanMode has {@link TanMode::needsTanMedium()} set to true, this
     *     must be the value returned from {@link TanMedium::getName()} for one of the TAN media supported with that TAN
     *     mode. Use {@link getTanMedia()} to obtain a list of possible TAN media options.
     */
    public function selectTanMode($tanMode, $tanMedium = null)
    {
        if (!is_int($tanMode) && !($tanMode instanceof TanMode)) {
            throw new \InvalidArgumentException('tanMode must be an int or a TanMode');
        }
        if ($tanMedium !== null && !is_string($tanMedium) && !($tanMedium instanceof TanMedium)) {
            throw new \InvalidArgumentException('tanMedium must be a string or a TanMedium');
        }
        $this->selectedTanMode = $tanMode instanceof TanMode ? $tanMode->getId() : $tanMode;
        $this->selectedTanMedium = $tanMedium instanceof TanMedium ? $tanMedium->getName() : $tanMedium;
    }

    /**
     * Fetches the BPD from the server, if they are not already present at the client, and then returns them. Note that
     * this does not require user login.
     * @return BPD The BPD from the bank.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws UnexpectedResponseException When the server does not send the BPD or close the dialog properly.
     * @throws ServerException When the server resopnds with an error.
     */
    public function getBpd(): BPD
    {
        $this->ensureBpdAvailable();
        return $this->bpd;
    }

    // ------------------------------------------------- IMPLEMENTATION ------------------------------------------------

    /**
     * Ensures that the latest BPD data is present by executing an anonymous dialog (including initialization and
     * termination of the dialog) if necessary. Executing this does not require (strong or any) authentication, and it
     * makes the {@link $bpd} available.
     *
     * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
     * Section: C.5.1 (and also C.3.1.1)
     *
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws UnexpectedResponseException When the server does not send the BPD or close the dialog properly.
     * @throws ServerException When the server resopnds with an error.
     */
    private function ensureBpdAvailable()
    {
        if ($this->bpd !== null) {
            return; // Nothing to do.
        }
        if ($this->dialogId !== null) {
            throw new \RuntimeException('Cannot init another dialog.');
        }
        if ($this->selectedTanMode === NoPsd2TanMode::ID || $this->selectedTanMode instanceof NoPsd2TanMode) {
            // For banks that don't support PSD2, we also don't use an anonymous dialog to obtain the BPD. The more
            // common procedure before PSD2 was to just get the BPD upon first login. Thus execute(DialogInitialization)
            // tolerates not having a BPD yet.
            return;
        }

        // We must always include HKTAN in order to signal that strong authentication (PSD2) is supported (section
        // B.4.3.1). As this is the first contact with the server, we don't know which HKTAN versions it supports, so we
        // just sent HKTANv6 as it's currently most supported by banks.
        $initRequest = Message::createPlainMessage(MessageBuilder::create()
            ->add(HKIDNv2::createAnonymous($this->options->bankCode))
            ->add(HKVVBv3::create($this->options, null, null)) // Pretend we have no BPD/UPD.
            ->add(HKTANv6::createDummy()));
        $initResponse = $this->sendMessage($initRequest);
        if (!$this->readBPD($initResponse)) {
            throw new UnexpectedResponseException('Did not receive BPD');
        }
        $this->dialogId = $initResponse->header->dialogId;
        $this->endDialog(true);
    }

    private function requireCredentials(): Credentials
    {
        if ($this->credentials === null) {
            throw new \LogicException('This action is not allowed on a FinTs instance without Credentials');
        }
        return $this->credentials;
    }

    /**
     * Ensures that the {@link $allowedTanModes} are available by executing a personalized, TAN-less dialog
     * initialization (and closing the dialog again), if necessary. Executing this only requires the {@link Credentials}
     * but no strong authentication.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws UnexpectedResponseException When the server does not send the BPD, the Kundensystem-ID or the TAN modes
     *     like it should according to the protocol, or when the dialog is not closed properly.
     * @throws ServerException When the server responds with an error.
     */
    private function ensureTanModesAvailable()
    {
        if ($this->allowedTanModes === null) {
            $this->ensureBpdAvailable();
            $this->ensureSynchronized(); // The response here will contain 3920, which is written to $allowedTanModes.
            if ($this->allowedTanModes === null) {
                throw new UnexpectedResponseException('No TAN modes received');
            }
        }
    }

    /**
     * Ensures that we have a {@link $kundensystemId} by executing a synchronization dialog (and closing it again) if
     * if necessary. Executing this does not require strong authentication.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws UnexpectedResponseException When the server does not send the BPD or the Kundensystem-ID, or when the
     *     dialog is not closed properly.
     * @throws ServerException When the server responds with an error.
     */
    private function ensureSynchronized()
    {
        if ($this->kundensystemId === null) {
            $this->ensureBpdAvailable();

            // Execute dialog initialization without a TAN mode/medium, so using the fake mode 999. While most banks
            // accept the real TAN mode for synchronization (as defined in the specification), some get confused by the
            // presence of anything other than 999 into thinking that strong authentication is required. And for those
            // banks that don't support PSD2, we just keep the dummy TAN mode, as they wouldn't even understand 999.
            $oldTanMode = $this->selectedTanMode;
            $oldTanMedium = $this->selectedTanMedium;
            if (!($this->selectedTanMode instanceof NoPsd2TanMode)) {
                $this->selectedTanMode = null;
            }
            $this->selectedTanMedium = null;
            try {
                $this->executeWeakDialogInitialization(null);
                if ($this->kundensystemId === null) {
                    throw new UnexpectedResponseException('No Kundensystem-ID retrieved from sync.');
                }
                $this->endDialog();
            } finally {
                $this->selectedTanMode = $oldTanMode;
                $this->selectedTanMedium = $oldTanMedium;
            }
        }
    }

    /**
     * If the selected TAN mode was provided as an int, resolves it to a full {@link TanMode} instance, which may
     * involve a request to the server to retrieve the BPD. Then returns it.
     * @return TanMode|null The current TAN mode, null if none was selected, never an int.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws ServerException When the server resopnds with an error during the BPD fetch.
     */
    public function getSelectedTanMode(): ?TanMode
    {
        if ($this->selectedTanMode === NoPsd2TanMode::ID) {
            $this->selectedTanMode = new NoPsd2TanMode();
        } elseif (is_int($this->selectedTanMode)) {
            $this->ensureBpdAvailable();
            if (!array_key_exists($this->selectedTanMode, $this->bpd->allTanModes)) {
                throw new \InvalidArgumentException("Unknown TAN mode: $this->selectedTanMode");
            }
            $this->selectedTanMode = $this->bpd->allTanModes[$this->selectedTanMode];
            if (!$this->selectedTanMode->isProzessvariante2()) {
                throw new UnsupportedException('Only supports Prozessvariante 2');
            }

            if ($this->selectedTanMode->needsTanMedium()) {
                if ($this->selectedTanMedium === null) {
                    throw new \InvalidArgumentException('tanMedium is mandatory for this tanMode');
                }
            } else {
                if ($this->selectedTanMedium !== null) {
                    throw new \InvalidArgumentException('tanMedium not allowed for this tanMode');
                }
            }
        }
        return $this->selectedTanMode;
    }

    /**
     * Like {@link getSelectedTanMode()}, but throws an exception if none was selected.
     * @return TanMode The current TAN mode.
     * @throws \RuntimeException If no TAN mode has been selected.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws ServerException When the server resopnds with an error during the BPD fetch.
     */
    private function requireTanMode(): TanMode
    {
        $tanMode = $this->getSelectedTanMode();
        if ($tanMode === null) {
            throw new \RuntimeException('selectTanMode() must be called before login() or execute()');
        }
        return $tanMode;
    }

    /**
     * Creates a new connection based on the {@link $options}. This can be overridden for unit testing purposes.
     * @return Connection A newly instantiated connection.
     */
    protected function newConnection(): Connection
    {
        return new Connection($this->options->url, $this->options->timeoutConnect, $this->options->timeoutResponse);
    }

    /**
     * Closes the physical connection, if necessary.
     */
    private function disconnect()
    {
        if ($this->connection !== null) {
            $this->connection->disconnect();
            $this->connection = null;
        }
    }

    /**
     * Passes the response segments to the action for post-processing of the response.
     * @param BaseAction $action The action to which the response belongs.
     * @param Message $fakeResponseMessage A messsage that contains the response segments for this action.
     * @throws UnexpectedResponseException When the server responded with a valid but unexpected message.
     */
    private function processActionResponse(BaseAction $action, Message $fakeResponseMessage)
    {
        $action->processResponse($fakeResponseMessage);
        if ($action instanceof DialogInitialization) {
            $this->dialogId = $action->getDialogId();
            if ($this->kundensystemId === null && $action->getKundensystemId()) {
                $this->kundensystemId = $action->getKundensystemId();
            }
            if ($action->getUpd() !== null) {
                $this->upd = $action->getUpd();
            } elseif ($this->upd === null && $action->isStronglyAuthenticated()) {
                throw new UnexpectedResponseException('No UPD received');
            }
        }
    }

    /**
     * Initialize a personalized dialog with weak authentication (no two-step authentication, no TAN, using the fake
     * mode with ID 999 instead), which can be used for certain less sensitive business transactions, including HKTAB to
     * retrieve the TAN media list. This is for Authentifizierungsklasse 1 and 4 (conditionally).
     * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
     * Section: B.3
     * @param string|null $hktanRef The identifier of the main PIN/TAN management segment to be executed in this dialog,
     *     or null for a general weakly authenticated dialog. See {@link DialogInitialization} for documentation.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws UnexpectedResponseException When the server does not send the BPD or the Kundensystem-ID as it should
     *     according to the protocol, when it asks for a TAN even though it shouldn't, or when the dialog is not closed
     *     properly.
     * @throws ServerException When the server responds with an error.
     */
    private function executeWeakDialogInitialization(?string $hktanRef)
    {
        if ($this->dialogId !== null) {
            throw new \RuntimeException('Cannot init another dialog.');
        }

        $this->messageNumber = 1;
        $dialogInitialization = new DialogInitialization($this->options, $this->requireCredentials(),
            $this->getSelectedTanMode(), $this->selectedTanMedium, $this->kundensystemId, $hktanRef);
        $this->execute($dialogInitialization);
        if ($dialogInitialization->needsTan()) {
            throw new UnexpectedResponseException('Server asked for TAN on a dialog meant for weak authentication');
        }
    }

    /**
     * @param Message $response A response retrieved from the server that may or may not contain the BPD.
     * @return bool Whether the BPD was found in the response.
     */
    private function readBPD(Message $response): bool
    {
        if ($allowed = $response->findRueckmeldung(Rueckmeldungscode::ZUGELASSENE_VERFAHREN)) {
            $this->allowedTanModes = array_map('intval', $allowed->rueckmeldungsparameter);
        }
        if (!$response->hasSegment(HIBPAv3::class)) {
            return false;
        }
        $this->bpd = BPD::extractFromResponse($response);
        if (!$this->bpd->supportsPsd2() && !($this->selectedTanMode instanceof NoPsd2TanMode)) {
            throw new UnsupportedException('The bank does not support PSD2.');
        }
        return true;
    }

    /**
     * Closes the currently active dialog, if any. Note that this does *not* close the connection, it is possible to
     * open a new dialog on the same connection.
     * @param bool $isAnonymous If set to true, the HKEND message will not be wrapped into an encryption envelope.
     * @throws ServerException When the server responds with an error instead of closing the dialog. This means that
     *     the connection is tainted and can probably not be used for another dialog.
     */
    protected function endDialog(bool $isAnonymous = false)
    {
        if ($this->connection === null) {
            $this->dialogId = null;
            return;
        }
        try {
            if ($this->dialogId !== null) {
                $message = MessageBuilder::create()->add(HKENDv1::create($this->dialogId));
                $request = $isAnonymous
                    ? Message::createPlainMessage($message)
                    : $this->buildMessage($message, $this->getSelectedTanMode());
                $response = $this->sendMessage($request);
                if ($response->findRueckmeldung(Rueckmeldungscode::BEENDET) === null) {
                    throw new UnexpectedResponseException(
                        'Server did not confirm dialog end, but did not send error either');
                }
            }
        } catch (CurlException $e) {
            // Ignore, we want to disconnect anyway.
        } catch (ServerException $e) {
            if ($e->hasError(Rueckmeldungscode::ABGEBROCHEN)) {
                // We wanted to end the dialog, but the server already canceled it before.
                $this->logger->warning("Dialog already ended: $e");
            } else {
                // Something else went wrong.
                throw $e;
            }
        } finally {
            $this->dialogId = null;
        }
    }

    /**
     * Injects FinTsOptions/BPD/UPD/Credentials information into the message.
     * @param MessageBuilder $message The message to be built.
     * @param TanMode|null $tanMode Optionally a TAN mode that will be used when sending this message, defaults to 999
     *     (single step).
     * @param string|null Optionally a TAN to sign this message with.
     * @return Message The built message.
     */
    private function buildMessage(MessageBuilder $message, ?TanMode $tanMode = null, ?string $tan = null): Message
    {
        return Message::createWrappedMessage(
            $message,
            $this->options,
            $this->kundensystemId === null ? '0' : $this->kundensystemId,
            $this->requireCredentials(),
            $tanMode,
            $tan
        );
    }

    /**
     * Finalizes a message (conversion to wire format, filling in message number and size), sends it to the bank and
     * parses the response, plus logging.
     * @param MessageBuilder|Message $request The message to be sent.
     * @return Message The response from the server.
     * @throws CurlException When the request failed on the physical or TCP/HTTPS protocol level.
     * @throws ServerException When the response contains an error.
     */
    private function sendMessage($request): Message
    {
        if ($request instanceof MessageBuilder) {
            $request = $this->buildMessage($request, $this->getSelectedTanMode());
        }

        $request->header->dialogId = $this->dialogId === null ? '0' : $this->dialogId;
        $request->header->nachrichtennummer = $this->messageNumber;
        $request->footer->nachrichtennummer = $this->messageNumber;
        ++$this->messageNumber;
        $request->header->setNachrichtengroesse(strlen($request->serialize()));

        $request->validate();

        if ($this->connection === null) {
            $this->connection = $this->newConnection();
        }

        $rawRequest = $request->serialize();
        $this->logger->debug('> ' . $rawRequest);
        try {
            $rawResponse = $this->connection->send($rawRequest);
            $this->logger->debug('< ' . $rawResponse);
        } catch (CurlException $e) {
            $this->logger->critical($e->getMessage());
            $this->logger->debug(print_r($e->getCurlInfo(), true));
            $this->disconnect();
            throw $e;
        }

        try {
            $response = Message::parse($rawResponse);
        } catch (\InvalidArgumentException $e) {
            $this->disconnect();
            throw new InvalidResponseException('Invalid response from server', 0, $e);
        }

        try {
            ServerException::detectAndThrowErrors($response, $request);
        } catch (ServerException $e) {
            $this->disconnect();
            if ($e->hasError(Rueckmeldungscode::ABGEBROCHEN)) {
                $this->forgetDialog();
            }
            throw $e;
        }
        return $response;
    }

    /**
     * @return ?string The FinTS-Kundensystem-ID as provided by the bank (or set manually).
     * This ID should be persisted if provided and reused for further communication.
     * @see https://github.com/nemiah/phpFinTS/issues/453
     */
    public function getKundensystemId(): ?string {
        return $this->kundensystemId;
    }

    /**
     * Sets the FinTS-Kundensystem-ID to be used when communicating with the bank.
     * It is initially provided by the bank and should be persisted.
     * The kundensystemId should be set right after calling `selectTanMode()`.
     * Note that alternatively to all this, a persisted FinTs instance can be restored in the constructor.
     * @see https://github.com/nemiah/phpFinTS/issues/453
     *
     * @param mixed $kundensystemId
     */
    public function setKundensystemId(?string $kundensystemId): static {
        $this->kundensystemId = $kundensystemId;
        return $this;
    }
}
