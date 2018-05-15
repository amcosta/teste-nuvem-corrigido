<?php

namespace TiendaNube\Checkout\Service\Shipping;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Psr\Log\LoggerInterface;
use TiendaNube\Checkout\Model\Store;
use TiendaNube\Checkout\Service\Store\StoreServiceInterface;

class AddressServiceFactoryTest extends TestCase
{
    public function testCreateNotBetaTesterAddressService()
    {
        $store = new Store();
        $store->disableBetaTesting();

        $pdo = $this->mockPdo();
        $logger = $this->mockLogger();
        $storeService = $this->mockStoreService($store);
        $redisClient = $this->mockRedisClient();

        $factory = new AddressServiceFactory($redisClient, $pdo, $logger, $storeService);
        $service = $factory->create();

        $this->assertInstanceOf(AddressServiceInterface::class, $service);
        $this->assertInstanceOf(AddressService::class, $service);
    }

    public function testCreateABetaTesterAddressService()
    {
        $store = new Store();
        $store->enableBetaTesting();

        $pdo = $this->mockPdo();
        $logger = $this->mockLogger();
        $storeService = $this->mockStoreService($store);
        $redisClient = $this->mockRedisClient();

        $factory = new AddressServiceFactory($redisClient, $pdo, $logger, $storeService);
        $service = $factory->create();

        $this->assertInstanceOf(AddressServiceInterface::class, $service);
        $this->assertInstanceOf(AddressApiService::class, $service);
    }

    private function mockPdo()
    {
        return $this->createMock(\PDO::class);
    }

    private function mockLogger()
    {
        return $this->createMock(LoggerInterface::class);
    }

    private function mockStoreService(Store $store)
    {
        $storeService = $this->createMock(StoreServiceInterface::class);
        $storeService->method('getCurrentStore')->willReturn($store);

        return $storeService;
    }

    private function mockRedisClient()
    {
        return $this->createMock(Client::class);
    }
}