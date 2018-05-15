<?php

namespace TiendaNube\Checkout\Service\Shipping;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

class AddressApiServiceTest extends TestCase
{
    public function testVerifyInterface()
    {
        $client = $this->createMock(Client::class);
        $logger = $this->createMock(LoggerInterface::class);
        $addressDatabaseService = $this->createMock(AddressDatabaseService::class);

        $service = new AddressApiService($client, $logger, $addressDatabaseService);

        $this->assertInstanceOf(AddressServiceInterface::class, $service);
    }

    public function testSuccessResponseFromApi()
    {
        $apiContent = [
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

        $client = $this->mockApiResponse(json_encode($apiContent), 200, 'OK');
        $addressDatabase = $this->mockAddressDatabaseService();
        $logger = $this->createMock(LoggerInterface::class);

        $service = new AddressApiService($client, $logger, $addressDatabase);
        $response = $service->getAddressByZip('40010000');

        $this->assertEquals($apiContent, $response);
    }

    private function mockAddressDatabaseService($content = null)
    {
        $service = $this->createMock(AddressDatabaseService::class);
        $service->method('getAddressByZip')->willReturn($content);

        return $service;
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
        $addressDatabase = $this->mockAddressDatabaseService();
        $logger = $this->createMock(LoggerInterface::class);

        $service = new AddressApiService($client, $logger, $addressDatabase);
        $response = $service->getAddressByZip('400100001');

        $this->assertNull($response);
    }

    public function testNotFoundResponseFromApi()
    {
        $client = $this->mockApiResponse(null, 404, 'Not Found');
        $addressDatabase = $this->mockAddressDatabaseService();
        $logger = $this->createMock(LoggerInterface::class);

        $service = new AddressApiService($client, $logger, $addressDatabase);
        $response = $service->getAddressByZip('400100001');

        $this->assertNull($response);
    }

    public function testHandleExceptionInRequest()
    {
        $exception = $this->createMock(ClientException::class);

        $client = $this->createMock(Client::class);
        $client->method('request')->willThrowException($exception);

        $addressDatabase = $this->mockAddressDatabaseService();
        $logger = $this->createMock(LoggerInterface::class);

        $service = new AddressApiService($client, $logger, $addressDatabase);
        $response = $service->getAddressByZip('400100001');

        $this->assertNull($response);
    }

    public function testUnhandleExceptionInRequest()
    {
        $client = $this->createMock(Client::class);
        $client->method('request')->willThrowException(new \Exception('An error occurred'));

        $addressDatabase = $this->mockAddressDatabaseService();
        $logger = $this->createMock(LoggerInterface::class);

        $this->expectException(\Exception::class);

        $service = new AddressApiService($client, $logger, $addressDatabase);
        $response = $service->getAddressByZip('400100001');
    }

    public function testGetLimitExceededResponseFromApi()
    {
        $client = $this->mockApiResponse('{"error":"Request limit exceeded, please try again in 3599 seconds"}',
            429,
            'Too Many Requests'
        );
        $addressDatabase = $this->mockAddressDatabaseService();
        $logger = $this->createMock(LoggerInterface::class);

        $service = new AddressApiService($client, $logger, $addressDatabase);
        $response = $service->getAddressByZip('400100000');

        $this->assertNull($response);
    }

    public function testGetAExistingAddressFromDatabase()
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

        $client = $this->mockApiResponse(null, null, null);
        $addressDatabase = $this->mockAddressDatabaseService($content);
        $logger = $this->createMock(LoggerInterface::class);

        $service = new AddressApiService($client, $logger, $addressDatabase);
        $response = $service->getAddressByZip('40010000');

        $this->assertEquals($content, $response);
    }
}