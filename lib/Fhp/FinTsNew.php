<?php

namespace Fhp;

use Fhp\Model\TanMedium;
use Fhp\Model\TanMode;
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
use Fhp\Segment\TAN\HITANv6;
use Fhp\Segment\TAN\HKTANv6;
use Fhp\Segment\TAN\VerfahrensparameterZweiSchrittVerfahrenV6;
use Fhp\Syntax\InvalidResponseException;
use Fhp\Syntax\Serializer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * This is the main class of this library. Please see the Samples directory for how to use it.
 * This class is not thread-safe, do not call its funtions concurrently.
 */
class FinTsNew
{
    // Things we retrieved from the user / the calling application.
    /** @var FinTsOptions */
    private $options;
    /** @var Credentials */
    private $credentials;
    /** @var SanitizingLogger */
    private $logger;

    // The TAN mode and medium to be used for business transactions that require a TAN.
    /** @var VerfahrensparameterZweiSchrittVerfahrenV6|int|null Note that this is a sub-type of {@link TanMode} */
    private $selectedTanMode;
    /** @var string|null This is a {@link TanMedium#getName()}, but we don't have the {@link TanMedium} instance. */
    private $selectedTanMedium;

    // State that persists across physical connections, dialogs and even PHP sessions.
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
     * @param FinTsOptions $options Configuration options for the connection to the bank.
     * @param Credentials $credentials Authentication information for the user. Note: This library does not support
     *     anonymous connections, so the credentials are mandatory.
     * @param string|null $persistedInstance The return value of {@link #persist()} of a previous FinTs instance, usually from an earlier
     *     PHP session. Passing this in here saves 1-2 dialogs that are normally made with the bank to obtain the BPD
     *     and Kundensystem-ID.
     */
    public function __construct(FinTsOptions $options, Credentials $credentials, ?string $persistedInstance = null)
    {
        $this->logger = new NullLogger();
        $options->validate();
        $this->options = $options;
        $this->credentials = $credentials;

        if ($persistedInstance !== null) {
            $this->loadPersistedInstance($persistedInstance);
        }
    }

    /**
     * Destructing the object only disconnects. Please use {@link #close()} if you want to properly "log out", i.e. end
     * the FinTs dialog. On the other hand, do *not* close in case you have serialized the FinTsNew instance and intend
     * to resume it later due to a TAN request.
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Returns a serialized form of parts of this object. This is different from PHP's `\Serializable` in that it only
     * serializes parts and cannot simply be restored with `unserialize()` because the `FinTsOptions` and the
     * `Credentials` need to be passed to the constructor in addition to the string returned here.
     *
     * Alternatively you can use {@link #loadPersistedInstance) to separate constructing the instance and resuming it.
     *
     * NOTE: Unless you're persisting this object to complete a TAN request later on, you probably want to log the user
     * out first by calling {@link #close()}.
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

    /**
     * Use this to continue a previous FinTs Instance, for example after a TAN was needed and PHP execution was ended to
     * obtain it from the user.
     *
     * @param string $persistedInstance The return value of {@link #persist()} of a previous FinTs instance, usually from an earlier
     *     PHP session.
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

    /** @param array $data */
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

    /**
     * @return SanitizingLogger
     */
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
     * Executes a strongly authenticated login action and returns it. With some banks, this requires a TAN.
     * @return DialogInitialization A {@link BaseAction} for the outcome of the login. You should check this for errors
     *     using {@link BaseAction#isError()} or {@link BaseAction#maybeThrowError()}. You should also check whether a
     *     TAN is needed using {@link BaseAction#needsTan()} and, if so, finish the login by passing {@link BaseAction}
     *     returned here to {@link #submitTan()}.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws UnexpectedResponseException When the server responds with a valid but unexpected message.
     * @throws ServerException When the server responds with a (FinTS-encoded) error message. Note that some errors are
     *     passed to the $action instead.
     */
    public function login(): DialogInitialization
    {
        $this->requireTanMode();
        $this->ensureSynchronized();
        $this->messageNumber = 1;
        $login = new DialogInitialization($this->options, $this->credentials, $this->getSelectedTanMode(),
            $this->selectedTanMedium, $this->kundensystemId);
        $this->execute($login);
        return $login;
    }

    /**
     * Executes an action. Be sure to {@link #login()} first. See the `\Fhp\Action` package for actions that can be
     * executed with this function. Note that, after this function returns, the result of the action is stored inside
     * the action itself, so you need to check its {@link BaseAction#isSuccess()}, {@link BaseAction#needsTan()} and
     * other getters in order to obtain its status and result.
     * @param BaseAction $action The action to be executed. Its status will be updated when this function returns.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws UnexpectedResponseException When the server responds with a valid but unexpected message.
     * @throws ServerException When the server responds with a (FinTS-encoded) error message. Note that some errors are
     *     passed to the $action instead.
     */
    public function execute(BaseAction $action)
    {
        if ($this->dialogId === null && !($action instanceof DialogInitialization)) {
            throw new \RuntimeException('Need to login (DialogInitialization) before executing other actions');
        }

        // Let the BaseAction implementation build its request segments.
        try {
            $requestSegments = $action->createRequest($this->bpd, $this->upd);
            $requestSegments = is_array($requestSegments) ? $requestSegments : [$requestSegments];
        } catch (\Exception $e) {
            $action->processError($e, $this->bpd, $this->upd);
            return;
        }
        if (count($requestSegments) === 0) {
            return; // No request needed.
        }
        $this->checkPaginationToken($action, $requestSegments);

        // Construct the full request message.
        $message = MessageBuilder::create()->add($requestSegments); // This fills in the segment numbers.
        if ($this->bpd->tanRequiredForRequest($requestSegments)) {
            $message->add(HKTANv6::createProzessvariante2Step1($this->requireTanMode(), $this->selectedTanMedium));
        }
        $request = $this->buildMessage($message, $this->getSelectedTanMode());
        $action->setRequestSegmentNumbers(array_map(function ($segment) {
            /* @var BaseSegment $segment */
            return $segment->getSegmentNumber();
        }, $requestSegments));

        // Execute the request.
        $response = $this->sendRequestForAction($action, $request);
        if ($response === null) {
            return; // Error occurred and was written to $action.
        }

        // Detect if the bank wants a TAN.
        /** @var HITANv6 $hitan */
        $hitan = $response->findSegment(HITANv6::class);
        if ($hitan !== null && $hitan->auftragsreferenz !== HITANv6::DUMMY_REFERENCE) {
            if ($hitan->tanProzess !== 4) {
                throw new UnexpectedResponseException("Unsupported TAN request type $hitan->tanProzess");
            }
            if ($this->bpd === null || $this->kundensystemId === null) {
                throw new UnexpectedResponseException('Unexpected TAN request');
            }
            $action->setTanRequest($hitan);
            if ($action instanceof DialogInitialization) {
                $action->setDialogId($response->header->dialogId);
                $action->setMessageNumber($this->messageNumber);
            }
            return;
        }

        // If no TAN is needed, process the response normally, and maybe keep going for more pages.
        $this->processActionResponse($action, $response->filterByReferenceSegments($action->getRequestSegmentNumbers()));
        if ($action->hasMorePages()) {
            $this->execute($action);
        }
    }

    /**
     * @param BaseAction $action The action being executed.
     * @param Message $request The request that has been constructed for the action and should be sent.
     * @return Message|null The full response from the server, or null if an action-level error occurred and the
     *     response should not be processed further.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws ServerException When the server responds with a (FinTS-encoded) error message.
     */
    private function sendRequestForAction(BaseAction $action, Message $request): ?Message
    {
        try {
            $response = $this->sendMessage($request);
        } catch (ServerException $e) {
            $actionError = $e->extractErrorsForReference($action->getRequestSegmentNumbers());
            if ($actionError !== null) {
                $action->processError($actionError, $this->bpd, $this->upd);
                $e->extractError(Rueckmeldungscode::TEILWEISE_FEHLERHAFT); // Drop global error.
                if ($e->extractError(Rueckmeldungscode::ABGEBROCHEN) !== null) {
                    $this->forgetDialog();
                }
            }
            if (count($e->getErrors()) > 0) {
                throw $e;
            }
            return null;
        }
        $this->readBPD($response);
        return $response;
    }

    /**
     * For an action where {@link BaseAction#needsTan()} returns `true`, this function sends the given $tan to the
     * server in order to complete the action. This can be done asynchronously, i.e. not in the same PHP session as the
     * original {@link #execute()} call.
     * @param BaseAction $action The action to be completed.
     * @param string $tan The TAN entered by the user.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws UnexpectedResponseException When the server responds with a valid but unexpected message.
     * @throws ServerException When the server responds with a (FinTS-encoded) error message. Note that some errors are
     *     passed to the $action instead.
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
        $message = MessageBuilder::create()
            ->add(HKTANv6::createProzessvariante2Step2($tanMode, $tanRequest->getProcessId()));
        $request = $this->buildMessage($message, $tanMode, $tan);

        // Execute the request.
        $response = $this->sendRequestForAction($action, $request);
        if ($response === null) {
            return; // Error occurred and was written to $action.
        }

        // Ensure that the TAN was accepted.
        /** @var HITANv6 $hitan */
        $hitan = $response->findSegment(HITANv6::class);
        if ($hitan === null) {
            throw new UnexpectedResponseException('HITAN missing after submitting TAN');
        }
        if ($hitan->tanProzess !== 2 || $hitan->auftragsreferenz !== $tanRequest->getProcessId()) {
            throw new UnexpectedResponseException("Bank has not accepted TAN: $hitan");
        }
        $action->setTanRequest(null);

        // Process the response normally, and maybe keep going for more pages.
        $this->processActionResponse($action, $response->filterByReferenceSegments($action->getRequestSegmentNumbers()));
        if ($action->hasMorePages()) {
            $this->execute($action);
        }
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
     * @throws ServerException When the server resopnds with an error.
     */
    public function getTanModes(): array
    {
        $this->ensureTanModesAvailable();
        return array_combine($this->allowedTanModes, array_map(function ($tanModeId) {
            return $this->bpd->allTanModes[$tanModeId];
        }, $this->allowedTanModes));
    }

    /**
     * For TAN modes where {@link TanMode#needsTanMedium()} returns true, the user additionally needs to pick a TAN
     * medium. This function returns a list of possible TAN media. Note that, depending on the bank, this list may
     * contain all the user's TAN media, or just the ones that are compatible with the given $tanMode.
     * @param TanMode|int $tanMode Either a {@link TanMode} instance obtained from {@link #getTanModes()} or its ID.
     * @return TanMedium[] A list of possible TAN media.
     * @throws UnexpectedResponseException Among other situations, this is thrown if the bank does not support
     *     enumerating TAN media. In that case, hopefully {@link TanMode#needsTanMedium()} didn't return true.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
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
        } catch (UnexpectedResponseException | CurlException | ServerException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new UnexpectedResponseException('Failed to retrieve TAN media', 0, $e);
        } finally {
            $this->selectedTanMode = $oldTanMode;
            $this->selectedTanMedium = $oldTanMedium;
        }
    }

    /**
     * @param TanMode|int $tanMode Either a {@link TanMode} instance obtained from {@link #getTanModes()} or its ID.
     * @param TanMedium|string|null $tanMedium If the $tanMode has {@link TanMode#needsTanMedium()} set to true, this
     *     must be the value returned from {@link TanMedium#getName()} for one of the TAN media supported with that TAN
     *     mode. Use {@link #getTanMedia()} to obtain a list of possible TAN media options.
     */
    public function selectTanMode($tanMode, $tanMedium = null)
    {
        $this->selectedTanMode = $tanMode instanceof TanMode ? $tanMode->getId() : $tanMode;
        $this->selectedTanMedium = $tanMedium instanceof TanMedium ? $tanMedium->getName() : $tanMedium;
    }

    // ------------------------------------------------- IMPLEMENTATION ------------------------------------------------

    /**
     * Ensures that the latest BPD data is present by executing an anonymous dialog (including initialization and
     * termination of the dialog) if necessary. Executing this does not require (strong or any) authentication, and it
     * makes the {@link #$bpd} available.
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
            return;
        } // Nothing to do.
        if ($this->dialogId !== null) {
            throw new \RuntimeException('Cannot init another dialog.');
        }
        // We must always include HKTAN in order to signal that strong authentication (PSD2) is supported (section B.4.3.1).
        $initRequest = Message::createPlainMessage(MessageBuilder::create()
            ->add(HKIDNv2::createAnonymous($this->options->bankCode))
            ->add(HKVVBv3::create($this->options, null, null)) // Pretend we have no BPD/UPD.
            ->add(HKTANv6::createProzessvariante2Step1()));
        $initResponse = $this->sendMessage($initRequest);
        if (!$this->readBPD($initResponse)) {
            throw new UnexpectedResponseException('Did not receive BPD');
        }
        $this->dialogId = $initResponse->header->dialogId;
        $this->endDialog(true);
    }

    /**
     * Ensures that the {@link #$allowedTanModes} are available by executing a personalized, TAN-less dialog
     * initialization (and closing the dialog again), if necessary. Executing this only requires the {@link Credentials}
     * but no strong authentication.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws ServerException When the server resopnds with an error.
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
     * Ensures that we have a {@link #$kundensystemId} by executing a synchronization dialog (and closing it again) if
     * if necessary. Executing this does not require strong authentication.
     * @throws CurlException When the connection fails in a layer below the FinTS protocol.
     * @throws ServerException When the server resopnds with an error.
     */
    private function ensureSynchronized()
    {
        if ($this->kundensystemId === null) {
            $this->ensureBpdAvailable();
            $this->executeWeakDialogInitialization(null);
            if ($this->kundensystemId === null) {
                throw new UnexpectedResponseException('No Kundensystem-ID retrieved from sync.');
            }
            $this->endDialog();
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
        if (is_int($this->selectedTanMode)) {
            $this->ensureBpdAvailable();
            if (!array_key_exists($this->selectedTanMode, $this->bpd->allTanModes)) {
                throw new \InvalidArgumentException("Unknown TAN mode: $this->selectedTanMode");
            }
            $this->selectedTanMode = $this->bpd->allTanModes[$this->selectedTanMode];
            if (!($this->selectedTanMode instanceof VerfahrensparameterZweiSchrittVerfahrenV6)) {
                throw new UnsupportedException('Only supports VerfahrensparameterZweiSchrittVerfahrenV6');
            }
            if ($this->selectedTanMode->tanProzess !== VerfahrensparameterZweiSchrittVerfahrenV6::PROZESSVARIANTE_2) {
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
     * Like {@link #getSelectedTanMode()}, but throws an exception if none was selected.
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
     * Creates a new connection based on the {@link #$options}. This can be overridden for unit testing purposes.
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
     * Ensures that the action included its pagination token in the request, if it has one. This is to prevent infinite
     * loops of requests for the first page in case an action does not (properly) implement pagination.
     * @param BaseAction $action An action that is about to be executed.
     * @param BaseSegment[] $requestSegments The segments that the action built as its request.
     * @throws UnsupportedException If the action has a pagination token from a previous execution, but did not include
     *     it in the request, i.e. it does not appear to support pagination even though it should.
     */
    private function checkPaginationToken(BaseAction $action, array $requestSegments)
    {
        $token = $action->getPaginationToken();
        if ($token === null) {
            return;
        }
        $token = Serializer::serializeDataElement($token, 'string');
        foreach ($requestSegments as $segment) {
            if (strpos($segment->serialize(), $token) !== false) {
                return;
            }
        }
        throw new UnsupportedException(
            'The action has a pagination token but does not appear to support pagination: ' . get_class($action));
    }

    /**
     * Passes the response segments to the action for post-processing of the response. If this fails, passes the error
     * itself to the action too, so that this function itself never throws an exception.
     * @param BaseAction $action The action to which the response belongs.
     * @param Message $fakeResponseMessage A messsage that contains the response segments for this action.
     */
    private function processActionResponse(BaseAction $action, Message $fakeResponseMessage)
    {
        try {
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
        } catch (\Exception $e) {
            $action->processError($e, $this->bpd, $this->upd);
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
     * @throws UnexpectedResponseException When the server responds with a valid but unexpected message.
     * @throws ServerException When the server responds with a (FinTS-encoded) error message. Note that some errors are
     *     passed to the $action instead.
     */
    private function executeWeakDialogInitialization(?string $hktanRef)
    {
        if ($this->dialogId !== null) {
            throw new \RuntimeException('Cannot init another dialog.');
        }

        $this->messageNumber = 1;
        $dialogInitialization = new DialogInitialization($this->options, $this->credentials,
            $this->getSelectedTanMode(), $this->selectedTanMedium, $this->kundensystemId, $hktanRef);
        $this->execute($dialogInitialization);
        try {
            $dialogInitialization->ensureSuccess();
        } catch (\Exception $e) {
            throw new UnexpectedResponseException('Failed to initialize weakly authenticated dialog', 0, $e);
        }
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
     * @param TanMode|null $tanMode Optionally a TAN mode that will be used when sending this message.
     * @param string|null Optionally a TAN to sign this message with.
     * @return Message The built message.
     */
    private function buildMessage(MessageBuilder $message, ?TanMode $tanMode = null, ?string $tan = null): Message
    {
        return Message::createWrappedMessage(
            $message,
            $this->options,
            $this->kundensystemId === null ? '0' : $this->kundensystemId,
            $this->credentials,
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
            try {
                $response = Message::parse($rawResponse);
            } catch (\InvalidArgumentException $e) {
                throw new InvalidResponseException('Invalid response from server', 0, $e);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            if ($e instanceof CurlException) {
                $this->logger->debug(print_r($e->getCurlInfo(), true));
            }
            $this->disconnect();
            throw $e;
        }

        try {
            ServerException::detectAndThrowErrors($response, $request);
        } catch (ServerException $e) {
            $this->disconnect();
            throw $e;
        }
        return $response;
    }
}
