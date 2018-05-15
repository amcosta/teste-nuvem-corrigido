<?php

namespace TiendaNube\Checkout\Service\Shipping;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Exception\GuzzleException;

class AddressServiceApi implements AddressServiceInterface
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
     * AddressServiceApi constructor.
     *
     * @param Client $client
     * @param LoggerInterface $logger
     */
    public function __construct(Client $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @param string $zip
     * @return array|null
     */
    public function getAddressByZip(string $zip): ?array
    {
        $this->logger->debug(sprintf(
            'Make a request to get the address to endpoint "%s"', $this->client->getConfig('base_uri')
        ));

        try {
            $response = $this->client->request('GET', 'address/' . $zip);
        } catch (GuzzleException $e) {
            $this->logger->error('An error occurred in the request to address endpoint, exception message: ' . $e->getMessage());
            return null;
        }

        $this->logger->debug(sprintf(
            'Response from address endpoint %s: %s', $response->getStatusCode(), $response->getReasonPhrase()
        ));

        if (200 === $response->getStatusCode()) {
            $address = $response->getBody()->getContents();
            return json_decode($address, true);
        }

        return null;
    }
}