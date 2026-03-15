<?php

namespace Fhp\Model;

use Fhp\Syntax\Bin;

/** Application code should not interact directly with this type, see {@link VopConfirmationRequest instead}. */
class VopConfirmationRequestImpl implements VopConfirmationRequest
{
    private Bin $vopId;
    private ?\DateTime $expiration;
    private ?string $informationForUser;
    private ?string $verificationResult;
    private ?string $verificationNotApplicableReason;

    public function __construct(
        Bin $vopId,
        ?\DateTime $expiration,
        ?string $informationForUser,
        ?string $verificationResult,
        ?string $verificationNotApplicableReason,
    ) {
        $this->vopId = $vopId;
        $this->expiration = $expiration;
        $this->informationForUser = $informationForUser;
        $this->verificationResult = $verificationResult;
        $this->verificationNotApplicableReason = $verificationNotApplicableReason;
    }

    public function getVopId(): Bin
    {
        return $this->vopId;
    }

    public function getExpiration(): ?\DateTime
    {
        return $this->expiration;
    }

    public function getInformationForUser(): ?string
    {
        return $this->informationForUser;
    }

    public function getVerificationResult(): ?string
    {
        return $this->verificationResult;
    }

    public function getVerificationNotApplicableReason(): ?string
    {
        return $this->verificationNotApplicableReason;
    }
}
