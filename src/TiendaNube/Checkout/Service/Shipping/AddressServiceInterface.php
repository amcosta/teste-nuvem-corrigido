<?php

namespace TiendaNube\Checkout\Service\Shipping;

interface AddressServiceInterface
{
    /**
     * @param string $zip
     * @return array|null
     */
    public function getAddressByZip(string $zip): ?array;
}