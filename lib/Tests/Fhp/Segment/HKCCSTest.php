<?php

namespace Tests\Fhp\Segment;

use Fhp\Segment\CCS\HKCCSv1;
use PHPUnit\Framework\TestCase;

class HKCCSTest extends TestCase
{
    // This lacks a bank code (BLZ) after the 280, so the Kik is incomplete.
    public const INVALID_HKCCS = "HKCCS:3:1+PRIVATE_______________:GENODEM1GLS:::280+urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.03+@1@0'";

    public function testValidateHBCI22Example1()
    {
        $parsed = HKCCSv1::parse(mb_convert_encoding(static::INVALID_HKCCS, 'ISO-8859-1', 'UTF-8'));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('kreditinstitutscode');
        $parsed->validate();
    }
}
