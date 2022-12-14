<?php

namespace MagePsycho\RegionCityPro\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MagePsycho\RegionCityPro\Helper\Data as RegionCityProHelper;
use Magento\Framework\Api\CustomAttributesDataInterface;
use \Magento\Framework\Api\AttributeValueFactory;
use MagePsycho\RegionCityPro\Api\Data\CityInterface;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CoreCopyFieldsetOrderAddressToCustomerAddress implements ObserverInterface
{
    /**
     * @var RegionCityProHelper
     */
    private $regionCityProHelper;

    /**
     * @var AttributeValueFactory
     */
    private $attributeValueFactory;

    public function __construct(
        RegionCityProHelper $regionCityProHelper,
        AttributeValueFactory $attributeValueFactory
    ) {
        $this->regionCityProHelper  = $regionCityProHelper;
        $this->attributeValueFactory = $attributeValueFactory;
    }

    /**
     * Convert order address's city_id to the customer address
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $source = $observer->getEvent()->getSource();
        $target = $observer->getEvent()->getTarget();

        $cityIdAttribute = $this->attributeValueFactory->create();
        $cityIdAttribute->setAttributeCode(CityInterface::ID)
            ->setValue($source->getCityId());

        $target->setData(CityInterface::ID, $source->getCityId());
        $target->setData(
            CustomAttributesDataInterface::CUSTOM_ATTRIBUTES,
            [
                CityInterface::ID => $cityIdAttribute
            ]
        );

        return $this;
    }
}
