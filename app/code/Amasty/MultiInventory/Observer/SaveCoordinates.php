<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer;

use Amasty\MultiInventory\Api\WarehouseRepositoryInterface as Repository;
use Amasty\MultiInventory\Helper\Distance as Helper;
use Amasty\MultiInventory\Model\Warehouse;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SaveCoordinates
 */
class SaveCoordinates implements ObserverInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * SaveCoordinates constructor.
     *
     * @param Repository $repository
     * @param Helper $helper
     */
    public function __construct(Repository $repository, Helper $helper)
    {
        $this->repository = $repository;
        $this->helper = $helper;
    }

    /**
     * Add coordinates to warehouse address
     *
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Json_Exception
     */
    public function execute(Observer $observer)
    {
        $latlng = null;
        /** @var Warehouse $warehouse */
        $warehouse = $observer->getObject();
        if (!$warehouse->getLat() || !$warehouse->getLng()) {
            $address = $this->helper->prepareAddressForGoogle($warehouse->getData());
            $latlng = $this->helper->getCoordinatesByAddress($address);
        } elseif (!$warehouse->isObjectNew()) {
            $model = $this->repository->getById($warehouse->getId());
            if ($this->helper->prepareAddressForGoogle($model->getData())
                != $this->helper->prepareAddressForGoogle($warehouse->getData())
            ) {
                $address = $this->helper->prepareAddressForGoogle($warehouse->getData());
                $latlng = $this->helper->getCoordinatesByAddress($address);
            }
        }
        if ($latlng) {
            $warehouse->setLng($latlng['lng']);
            $warehouse->setLat($latlng['lat']);
        }
    }
}
