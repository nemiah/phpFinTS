<?php

namespace Fhp\Message;

use Fhp\DataTypes\Kik;
use Fhp\DataTypes\Ktv;
use Fhp\Segment\AbstractSegment;
use Fhp\Segment\HKSAL;
use Fhp\Segment\HNHBS;
use Fhp\Segment\HNSHA;
use Fhp\Segment\HNSHK;
use Fhp\Segment\HNVSD;
use Fhp\Segment\HNVSK;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function test_setter_and_getter()
    {
        $message = new Message('12345678', 'username', '1234', '987654');

        $message->setDialogId(333);
        $this->assertEquals(333, $message->getDialogId());

        $message->setMessageNumber(10);
        $this->assertEquals(10, $message->getMessageNumber());

        $segments = $message->getSegments();

        $this->assertInternalType('array', $segments);
        $this->assertCount(3, $segments);
    }

    public function test_basic_message_creation()
    {
        $message = new Message('12345678', 'username', '1234', '987654');
        $date = new \DateTime();
        $dateString = $date->format('Ymd');

        $this->assertRegExp(
            '/HNHBK:1:3\+000000000296\+300\+0\+0\'HNVSK:998:3\+PIN:1\+998\+1\+1::987654\+1:' . $dateString
            . ':(\d+)\+2:2:13:@8@00000000:5:1\+280:12345678:username:V:0:0\+0\'HNVSD:999:1\+@130@HNSHK:2:4\+PIN:1'
            . '\+999\+(\d+)\+1\+1\+1::987654\+1\+1:' . $dateString . ':(\d+)\+1:999:1\+6:10:16\+280:12345678:'
            . 'username:S:0:0\'HNSHA:3:2\+(\d+)\+\+1234\'\'HNHBS:4:1\+0\'/',
            (string) $message
        );
    }

    public function test_message_creation_with_options_and_segments()
    {
        $kik = new Kik('290', '123123');
        $ktv = new Ktv('123123123', 'sub', $kik);
        $hksal = new HKSAL(HKSAL::VERSION, 3, $ktv, true);
        $options = array(
            Message::OPT_PINTAN_MECH => array('998')
        );

        $message = new Message(
            '12345678',
            'username',
            '1234',
            '987654',
            0,
            0,
            array($hksal),
            $options
        );

        $date = new \DateTime();
        $dateString = $date->format('Ymd');

        $this->assertRegExp(
            '/HNHBK:1:3\+000000000333\+300\+0\+0\'HNVSK:998:3\+PIN:2\+998\+1\+1::987654\+1:' . $dateString
            . ':(\d+)\+2:2:13:@8@00000000:5:1\+280:12345678:username:V:0:0\+0\'HNVSD:999:1\+@167@HNSHK:2:4\+PIN:2\+'
            . '998\+(\d+)\+1\+1\+1::987654\+1\+1:' . $dateString . ':(\d+)\+1:999:1\+6:10:16\+280:12345678:username:'
            . 'S:0:0\'HKSAL:3:7\+123123123:sub:290:123123\+1\'HNSHA:4:2\+(\d+)\+\+1234\'\'HNHBS:5:1\+0\'/',
            (string) $message
        );
    }

    public function test_get_encrypted_segments()
    {
        $message = new Message('12345678', 'username', '1234', '987654');
        $segments = $message->getEncryptedSegments();

        $this->assertInternalType('array', $segments);

        foreach ($segments as $segment) {
            $this->assertInstanceOf('\Fhp\Segment\AbstractSegment', $segment);
        }
    }
}