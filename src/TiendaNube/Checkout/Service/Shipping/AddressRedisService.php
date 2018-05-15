<?php

namespace TiendaNube\Checkout\Service\Shipping;

use Predis\Client;
use Psr\Log\LoggerInterface;

class AddressRedisService implements AddressPersistenceInterface
{
    /**
     * @var Client
     */
    private $redis;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Client $redis, LoggerInterface $logger)
    {
        $this->redis = $redis;
        $this->logger = $logger;
    }

    public function saveAddress(array $address)
    {
        $this->logger->debug('Save the api response address in redis');

        $this->redis->set($address['cep'], json_encode($address));
    }

    public function getAddressByZip(string $zip): ?array
    {
        $this->logger->debug(sprintf('Getting address for the zipcode [%s] from redis', $zip));

        if (!$this->redis->exists($zip)) {
            return null;
        }

        $address = $this->redis->get($zip);

        return json_decode($address, true);
    }
}