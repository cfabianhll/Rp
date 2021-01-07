<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer;

use Amasty\MultiInventory\Helper\Distance as Helper;
use Amasty\MultiInventory\Model\CustomerCoordinates;
use Amasty\MultiInventory\Model\CustomerCoordinatesFactory;
use Magento\Customer\Model\ResourceModel\Address as Repository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SaveCustomerCoordinates
 */
class SaveCustomerCoordinates implements ObserverInterface
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
     * @var CustomerCoordinates
     */
    private $customerCoordinates;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SaveCustomerCoordinates constructor.
     *
     * @param Repository $repository
     * @param Helper $helper
     * @param CustomerCoordinatesFactory $customerCoordinates
     * @param LoggerInterface $logger
     */
    public function __construct(
        Repository $repository,
        Helper $helper,
        CustomerCoordinatesFactory $customerCoordinates,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->helper = $helper;
        $this->customerCoordinates = $customerCoordinates->create();
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Zend_Json_Exception
     */
    public function execute(Observer $observer)
    {
        $latlng = null;

        /** @var Repository $customerAddress */
        $customerAddress = $observer->getCustomerAddress();
        $originAddress = $customerAddress->getStoredData();

        if ($this->helper->prepareAddressForGoogle($originAddress)
            != $this->helper->prepareAddressForGoogle($customerAddress->getData())
        ) {
            $addressId = $customerAddress->getId();
            $this->customerCoordinates->load($addressId, CustomerCoordinates::ADDRESS_ID);
            $this->customerCoordinates->setAddressId($addressId);
            $address = $this->helper->prepareAddressForGoogle($customerAddress->getData());
            $latlng = $this->helper->getCoordinatesByAddress($address);

            if ($latlng) {
                $this->customerCoordinates->setLat($latlng['lat']);
                $this->customerCoordinates->setLng($latlng['lng']);

                try {
                    $this->customerCoordinates->save();
                } catch (\Exception $e) {
                    $this->logger->critical($e->getMessage());
                }
            }
        }
    }
}
