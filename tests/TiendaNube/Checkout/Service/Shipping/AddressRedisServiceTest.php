<?php

namespace TiendaNube\Checkout\Service\Shipping;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Psr\Log\LoggerInterface;

class AddressRedisServiceTest extends TestCase
{
    public function testVerifyInterface()
    {
        $client = $this->createMock(Client::class);
        $logger = $this->createMock(LoggerInterface::class);

        $service = new AddressRedisService($client, $logger);

        $this->assertInstanceOf(AddressPersistenceInterface::class, $service);
    }

    public function testSaveAddress()
    {
        $address = [
            'altitude' => '7.0',
            'cep' => '40010000',
            'latitude' => '-12.967192',
            'longitude' => '-38.5101976',
            'address' => 'Avenida da França',
            'neighborhood' => 'Comércio',
            'city' => [
                'ddd' => 71,
                'ibge' => '2927408',
                'name' => 'Salvador'
            ],
            'state' => [
                'acronym' => 'BA'
            ]
        ];

        $client = $this->mockRedisClient();
        $client->method('set')->willReturn(null);

        $logger = $this->createMock(LoggerInterface::class);

        $service = new AddressRedisService($client, $logger);

        $result = $service->saveAddress($address);

        $this->assertNull($result);
    }

    public function testGetExistentAddressByZipcode()
    {
        $address = [
            'altitude' => '7.0',
            'cep' => '40010000',
            'latitude' => '-12.967192',
            'longitude' => '-38.5101976',
            'address' => 'Avenida da França',
            'neighborhood' => 'Comércio',
            'city' => [
                'ddd' => 71,
                'ibge' => '2927408',
                'name' => 'Salvador'
            ],
            'state' => [
                'acronym' => 'BA'
            ]
        ];

        $client = $this->mockRedisClient();
        $client->method('exists')->willReturn(true);
        $client->method('get')->willReturn(json_encode($address));

        $logger = $this->createMock(LoggerInterface::class);

        $service = new AddressRedisService($client, $logger);

        $result = $service->getAddressByZip('40010000');

        $this->assertEquals($address, $result);
    }

    public function testNotFoundAddressByZipcode()
    {
        $address = [
            'altitude' => '7.0',
            'cep' => '40010000',
            'latitude' => '-12.967192',
            'longitude' => '-38.5101976',
            'address' => 'Avenida da França',
            'neighborhood' => 'Comércio',
            'city' => [
                'ddd' => 71,
                'ibge' => '2927408',
                'name' => 'Salvador'
            ],
            'state' => [
                'acronym' => 'BA'
            ]
        ];

        $client = $this->mockRedisClient();
        $client->method('exists')->willReturn(false);

        $logger = $this->createMock(LoggerInterface::class);

        $service = new AddressRedisService($client, $logger);

        $result = $service->getAddressByZip('40010000');

        $this->assertNull($result);
    }

    private function mockRedisClient($address = null)
    {
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'set', 'exists'])
            ->getMock();

        return $client;
    }
}