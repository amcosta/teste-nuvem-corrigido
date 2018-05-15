<?php

namespace TiendaNube\Checkout\Service\Shipping;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use TiendaNube\Checkout\Service\Store\StoreServiceInterface;
use Predis\Client as RedisClient;

/**
 * Class AddressServiceFactory
 * @package TiendaNube\Checkout\Service\Shipping
 */
class AddressServiceFactory
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreServiceInterface
     */
    private $storeService;

    /**
     * @var RedisClient
     */
    private $redisClient;

    /**
     * AddressServiceFactory constructor.
     * @param RedisClient $redisClient
     * @param \PDO $pdo
     * @param LoggerInterface $logger
     * @param StoreServiceInterface $storeService
     */
    public function __construct(RedisClient $redisClient, \PDO $pdo, LoggerInterface $logger, StoreServiceInterface $storeService)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
        $this->storeService = $storeService;
        $this->redisClient = $redisClient;
    }

    /**
     * Return a AddressDatabaseService if the store is a beta tester otherwise return a AddressService
     *
     * @return AddressServiceInterface
     */
    public function create()
    {
        $store = $this->storeService->getCurrentStore();
        if (!$store->isBetaTester()) {
            return new AddressService($this->pdo, $this->logger);
        }

        $client = new Client([
            'base_uri' => 'https://shipping.tiendanube.com/v1/',
            'headers' => [
                'Authentication bearer' => 'YouShallNotPass',
                'Content-type' => 'application/json'
            ]
        ]);

        $addressService = new AddressRedisService($this->redisClient, $this->logger);

        return new AddressApiService($client, $this->logger, $addressService);
    }
}