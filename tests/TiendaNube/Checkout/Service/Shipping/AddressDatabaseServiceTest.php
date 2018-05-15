<?php

namespace TiendaNube\Checkout\Service\Shipping;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AddressDatabaseServiceTest extends TestCase
{
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

        $databaseResult = [
            'zipcode' => '40010000',
            'address' => json_encode($address)
        ];

        // mocking statement
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('rowCount')->willReturn(1);
        $stmt->method('fetch')->willReturn($databaseResult);

        // mocking pdo
        $pdo = $this->createMock(\PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        // mocking logger
        $logger = $this->createMock(LoggerInterface::class);

        // creating service
        $service = new AddressDatabaseService($pdo, $logger);

        // testing
        $result = $service->getAddressByZip('40010000');

        // asserts
        $this->assertNotNull($result);
        $this->assertEquals($address, $result);
    }

    public function testGetNonexistentAddressByZipcode()
    {
        // mocking statement
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('rowCount')->willReturn(0);

        // mocking pdo
        $pdo = $this->createMock(\PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        // mocking logger
        $logger = $this->createMock(LoggerInterface::class);

        // creating service
        $service = new AddressDatabaseService($pdo,$logger);

        // testing
        $result = $service->getAddressByZip('40010001');

        // asserts
        $this->assertNull($result);
    }

    public function testGetAddressByZipcodeWithPdoException()
    {
        // mocking pdo
        $pdo = $this->createMock(\PDO::class);
        $pdo->method('prepare')->willThrowException(new \PDOException('An error occurred'));

        // mocking logger
        $logger = $this->createMock(LoggerInterface::class);

        // creating service
        $service = new AddressDatabaseService($pdo,$logger);

        // testing
        $result = $service->getAddressByZip('40010000');

        // asserts
        $this->assertNull($result);
    }

    public function testGetAddressByZipcodeWithUncaughtException()
    {
        // expects
        $this->expectException(\Exception::class);

        // mocking pdo
        $pdo = $this->createMock(\PDO::class);
        $pdo->method('prepare')->willThrowException(new \Exception('An error occurred'));

        // mocking logger
        $logger = $this->createMock(LoggerInterface::class);

        // creating service
        $service = new AddressDatabaseService($pdo,$logger);

        // testing
        $service->getAddressByZip('40010000');
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

        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('execute')->willReturn(true);

        // mocking pdo
        $pdo = $this->createMock(\PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        // mocking logger
        $logger = $this->createMock(LoggerInterface::class);

        // creating service
        $service = new AddressDatabaseService($pdo, $logger);

        // testing
        $result = $service->saveAddress($address);

        // asserts
        $this->assertNull($result);
    }

    public function testSaveAddressThrownPdoException()
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

        // mocking pdo
        $pdo = $this->createMock(\PDO::class);
        $pdo->method('prepare')->willThrowException(new \PDOException('An error occurred'));

        // mocking logger
        $logger = $this->createMock(LoggerInterface::class);

        // creating service
        $service = new AddressDatabaseService($pdo,$logger);

        // testing
        $result = $service->saveAddress($address);

        // asserts
        $this->assertNull($result);
    }
}
