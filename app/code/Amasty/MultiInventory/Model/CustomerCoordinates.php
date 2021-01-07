<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model;

use Amasty\MultiInventory\Api\Data\CustomerCoordinatesInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class CustomerCoordinates
 */
class CustomerCoordinates extends AbstractExtensibleModel implements CustomerCoordinatesInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\CustomerCoordinates::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->_getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressId()
    {
        return $this->_getData(self::ADDRESS_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddressId($addressId)
    {
        $this->setData(self::ADDRESS_ID, $addressId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLng()
    {
        return $this->_getData(self::LNG);
    }

    /**
     * {@inheritdoc}
     */
    public function setLng($lng)
    {
        $this->setData(self::LNG, $lng);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLat()
    {
        return $this->_getData(self::LAT);
    }

    /**
     * {@inheritdoc}
     */
    public function setLat($lat)
    {
        $this->setData(self::LAT, $lat);

        return $this;
    }
}
