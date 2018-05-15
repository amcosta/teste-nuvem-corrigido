<?php

namespace TiendaNube\Checkout\Service\Shipping;

interface AddressPersistenceInterface extends AddressServiceInterface
{
    public function saveAddress(array $address);
}