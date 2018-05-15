<?php

namespace TiendaNube\Checkout\Service\Shipping;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

class AddressServiceApiTest extends TestCase
{
    public function testVerifyInterface()
    {
        $client = $this->createMock(Client::class);
        $logger = $this->createMock(LoggerInterface::class);

        $service = new AddressServiceApi($client, $logger);

        $this->assertInstanceOf(AddressServiceInterface::class, $service);
    }

    public function testSuccessResponseFromApi()
    {
        $content = [
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

        $client = $this->mockApiResponse(json_encode($content), 200, 'OK');
        $logger = $this->createMock(LoggerInterface::class);

        $service = new AddressServiceApi($client, $logger);
        $response = $service->getAddressByZip('40010000');

        $this->assertEquals($content, $response);
    }

    private function mockApiResponse($content, $statusCode, $reasonPhrase)
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn($content);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getReasonPhrase')->willReturn($reasonPhrase);
        $response->method('getBody')->willReturn($stream);

        $client = $this->createMock(Client::class);
        $client->method('request')->willReturn($response);

        return $client;
    }

    public function testInternalErrorResponseFromApi()
    {
        $client = $this->mockApiResponse(null, 500, 'Internal Server Error');
        $logger = $this->createMock(LoggerInterface::class);

        $service = new AddressServiceApi($client, $logger);
        $response = $service->getAddressByZip('400100001');

        $this->assertNull($response);
    }

    public function testNotFoundResponseFromApi()
    {
        $client = $this->mockApiResponse(null, 404, 'Not Found');
        $logger = $this->createMock(LoggerInterface::class);

        $service = new AddressServiceApi($client, $logger);
        $response = $service->getAddressByZip('400100001');

        $this->assertNull($response);
    }
}