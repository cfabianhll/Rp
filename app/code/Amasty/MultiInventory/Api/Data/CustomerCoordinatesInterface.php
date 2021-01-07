<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface CustomerCoordinatesInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';

    const ADDRESS_ID = 'address_id';

    const LAT = 'lat';

    const LNG = 'lng';

    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getAddressId();

    /**
     * @param int $addressId
     *
     * @return CustomerCoordinatesInterface
     */
    public function setAddressId($addressId);

    /**
     * @return string
     */
    public function getLat();

    /**
     * @param int $lat
     *
     * @return CustomerCoordinatesInterface
     */
    public function setLat($lat);

    /**
     * @return string
     */
    public function getLng();

    /**
     * @param int $lat
     *
     * @return CustomerCoordinatesInterface
     */
    public function setLng($lat);
}
