<?php

namespace TiendaNube\Checkout\Service\Shipping;

use Psr\Log\LoggerInterface;

class AddressDatabaseService implements AddressPersistenceInterface
{
    /**
     * The database connection link
     *
     * @var \PDO
     */
    protected $connection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AddressService constructor.
     *
     * @param \PDO $pdo
     * @param LoggerInterface $logger
     */
    public function __construct(\PDO $pdo, LoggerInterface $logger)
    {
        $this->connection = $pdo;
        $this->logger = $logger;
    }

    public function getAddressByZip(string $zip): ?array
    {
        $this->logger->debug('Getting address for the zipcode [' . $zip . '] from database');

        try {
            // getting the address from database
            $stmt = $this->connection->prepare('SELECT * FROM `addresses_api` WHERE `zipcode` = ?');
            $stmt->execute([$zip]);

            // checking if the address exists
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                return json_decode($result['address'], true);
            }

            return null;
        } catch (\PDOException $ex) {
            $this->logger->error(
                'An error occurred at try to fetch the address from the database, exception with message was caught: ' .
                $ex->getMessage()
            );

            return null;
        }
    }

    public function saveAddress(array $address)
    {
        $this->logger->debug('Save the api response address');

        try {
            // save the address in database
            $stmt = $this->connection->prepare('INSERT INTO addresses_api (zipcode, address) VALUES (?, ?)');
            $stmt->execute([$address['cep'], json_encode($address)]);
        } catch (\PDOException $ex) {
            $this->logger->error(
                'An error occurred at try to save the address in database, exception with message was caught: ' .
                $ex->getMessage()
            );
        }
    }
}