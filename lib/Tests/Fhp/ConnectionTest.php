<?php

namespace Tests\Fhp;

use Fhp\Adapter\AdapterInterface;
use Fhp\Adapter\Curl;
use Fhp\Connection;
use Fhp\Message\Message;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|AdapterInterface */
    protected $adapter;
    /** @var \PHPUnit_Framework_MockObject_MockObject|Message */
    protected $message;

    public function setUp()
    {
        $this->adapter = $this->getMockBuilder('\Fhp\Adapter\Curl')
            ->disableOriginalConstructor()
            ->setMethods(array('send'))
            ->getMock();

        $this->message = $this->getMockBuilder('\Fhp\Message\Message')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function test_can_set_and_get_adapter()
    {
        $conn = new Connection($this->adapter);
        $this->assertEquals($this->adapter, $conn->getAdapter());
    }

    public function test_send_calls_adapter_send()
    {
        $this->adapter->expects($this->once())
            ->method('send')
            ->with($this->message)
            ->will($this->returnValue('response text'));

        $conn = new Connection($this->adapter);
        $res = $conn->send($this->message);

        $this->assertInternalType('string', $res);
    }
}
