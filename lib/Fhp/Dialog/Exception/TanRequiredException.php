<?php

namespace Fhp\Dialog\Exception;

use Fhp\Response\GetTANRequest;

class TanRequiredException extends \Exception {

    private $_getTanRequest;

    public function __construct(GetTANRequest $getTANRequest) {
        parent::__construct();

        $this->_getTanRequest = $getTANRequest;
    }

    public function getResponse() : GetTANRequest {
        return $this->_getTanRequest;
    }

    public function getTanToken() : string {
        return $this->getResponse()->getTanTokenValues()->toString();
    }
}