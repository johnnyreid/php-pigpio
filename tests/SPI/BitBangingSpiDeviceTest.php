<?php
namespace Volantus\Pigpio\Tests\SPI;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\ExtensionRequest;
use Volantus\Pigpio\Protocol\Response;
use Volantus\Pigpio\SPI\BitBaningSpiDevice;
use Volantus\Pigpio\SPI\RegularSpiDevice;

/**
 * Class BitBangingSpiDeviceTest
 *
 * @package Volantus\Pigpio\Tests\SPI
 */
class BitBangingSpiDeviceTest extends TestCase
{
    /**
     * @var Client|MockObject
     */
    private $client;

    /**
     * @var RegularSpiDevice
     */
    private $device;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $this->device = new BitBaningSpiDevice($this->client, 32000, 6, 8, 21, 22, 32);
    }

    public function test_open_correctRequest()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new ExtensionRequest(Commands::BSPIO, 6, 0, 'LLLLL', [8, 21, 22, 32000, 32])))
            ->willReturn(new Response(0));

        $this->device->open();
        self::assertTrue($this->device->isOpen());
    }

    public function test_open_calledTwice_idempotent()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new ExtensionRequest(Commands::BSPIO, 6, 0, 'LLLLL', [8, 21, 22, 32000, 32])))
            ->willReturn(new Response(0));

        $this->device->open();
        $this->device->open();
        self::assertTrue($this->device->isOpen());
    }

    /**
     * @expectedException \Volantus\Pigpio\SPI\OpeningDeviceFailedException
     * @expectedExceptionMessage Opening device failed => bad GPIO pin given (PI_BAD_USER_GPIO)
     */
    public function test_open_badGpioPin()
    {
        $this->expectExceptionCode(BitBaningSpiDevice::PI_BAD_USER_GPIO);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(BitBaningSpiDevice::PI_BAD_USER_GPIO));

        $this->device->open();
    }

    /**
     * @expectedException \Volantus\Pigpio\SPI\OpeningDeviceFailedException
     * @expectedExceptionMessage Opening device failed => GPIO pin is already in use (PI_GPIO_IN_USE)
     */
    public function test_open_gpioAlreadyInUse()
    {
        $this->expectExceptionCode(BitBaningSpiDevice::PI_GPIO_IN_USE);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(BitBaningSpiDevice::PI_GPIO_IN_USE));

        $this->device->open();
    }

    /**
     * @expectedException \Volantus\Pigpio\SPI\OpeningDeviceFailedException
     * @expectedExceptionMessage Opening device failed => bad baud rate given (PI_BAD_SPI_BAUD)
     */
    public function test_open_badBaudRate()
    {
        $this->expectExceptionCode(BitBaningSpiDevice::PI_BAD_SPI_BAUD);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(BitBaningSpiDevice::PI_BAD_SPI_BAUD));

        $this->device->open();
    }

    /**
     * @expectedException \Volantus\Pigpio\SPI\OpeningDeviceFailedException
     * @expectedExceptionMessage Opening device failed => unknown error
     */
    public function test_open_unknownError()
    {
        $this->expectExceptionCode(-512);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(-512));

        $this->device->open();
    }
}