<?php

namespace Tests\Fhp\Syntax;

use Fhp\Syntax\Bin;

class BinTest extends \PHPUnit\Framework\TestCase
{
    public function testToString()
    {
        $string = md5(uniqid());
        $string2 = md5(uniqid());

        $d = new Bin($string);
        $this->assertEquals('@32@' . $string, (string) $d);
        $this->assertEquals('@32@' . $string, $d->toString());
        $this->assertEquals($string, $d->getData());

        $d->setData($string2);
        $this->assertEquals('@32@' . $string2, (string) $d);
        $this->assertEquals('@32@' . $string2, $d->toString());
        $this->assertEquals($string2, $d->getData());
    }
}
