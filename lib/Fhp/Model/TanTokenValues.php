<?php

namespace Fhp\Model;

class TanTokenValues{

    public $processId;
    public $systemId;
    public $dialogId;
    public $messageNumber;
    public $tanMechanism;
    public $tanMediaName;

    public function __construct(string $processId, string $systemId, string $dialogId, int $messageNumber,
                                string $tanMechanism, string $tanMediaName = null
    ) {
        $this->processId = $processId;
        $this->systemId = $systemId;
        $this->dialogId = $dialogId;
        $this->messageNumber = $messageNumber;
        $this->tanMechanism = $tanMechanism;
        $this->tanMediaName = $tanMediaName;
    }

    public function toString() : string {

        // The string must be usable in a url parameter
        $result = base64_encode(serialize($this));

        // base64_encode converts each not allowed char except "=", "+" and "/"
        return str_replace(['=', '+', '/'], ['-', '_', '.'], $result);
    }

    public static function fromString(string $base64String) : TanTokenValues {

        $base64String = str_replace(['-', '_', '.'], ['=', '+', '/'], $base64String);
        $string = base64_decode($base64String, true);
        if($string === FALSE) {
            throw new \RuntimeException('Unexpected chars in base64 string');
        }
        return unserialize($string);
    }
}