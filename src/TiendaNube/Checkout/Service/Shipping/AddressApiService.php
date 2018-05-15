<?php

namespace TiendaNube\Checkout\Service\Shipping;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Exception\GuzzleException;

class AddressApiService implements AddressServiceInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AddressPersistenceInterface
     */
    private $addressPersistenceService;

    /**
     * AddressApiService constructor.
     * @param Client $client
     * @param LoggerInterface $logger
     * @param AddressPersistenceInterface $addressPersistenceService
     */
    public function __construct(Client $client, LoggerInterface $logger, AddressPersistenceInterface $addressPersistenceService)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->addressPersistenceService = $addressPersistenceService;
    }

    /**
     * @param string $zip
     * @return array|null
     */
    public function getAddressByZip(string $zip): ?array
    {
        $result = $this->addressPersistenceService->getAddressByZip($zip);
        if (is_array($result)) {
            return $result;
        }

        $this->logger->debug(sprintf(
            'Make a request to get the address at the endpoint "%s"', $this->client->getConfig('base_uri')
        ));

        try {
            $response = $this->client->request('GET', 'address/' . $zip);
        } catch (GuzzleException $e) {
            $this->logger->error(
                'An error occurred in the request to get address at the endpoint, exception message: ' . $e->getMessage()
            );
            return null;
        }

        $this->logger->debug(sprintf(
            'Response from address endpoint %s: %s', $response->getStatusCode(), $response->getReasonPhrase()
        ));

        if (200 === $response->getStatusCode()) {
            $json = $response->getBody()->getContents();
            $address = json_decode($json, true);

            $this->addressPersistenceService->saveAddress($address);

            return $address;
        }

        return null;
    }
}