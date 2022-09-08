<?php

namespace MagePsycho\RegionCityPro\Plugin\Block\Checkout;

use Magento\Checkout\Block\Checkout\DirectoryDataProcessor;
use MagePsycho\RegionCityPro\Helper\Data as RegionCityProHelper;
use MagePsycho\RegionCityPro\Model\ResourceModel\City\Collection as CityCollection;
use MagePsycho\RegionCityPro\Model\ResourceModel\City\CollectionFactory as CityCollectionFactory;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DirectoryDataProcessorPlugin
{
    /**
     * @var CityCollection
     */
    private $cityCollection;

    /**
     * @var RegionCityProHelper
     */
    private $regionCityProHelper;

    public function __construct(
        RegionCityProHelper $regionCityProHelper,
        CityCollectionFactory $cityCollection
    ) {
        $this->cityCollection = $cityCollection;
        $this->regionCityProHelper = $regionCityProHelper;
    }

    public function afterProcess(
        DirectoryDataProcessor $subject,
        array $jsLayout
    ) {
        if ($this->regionCityProHelper->isFxnSkipped()) {
            return $jsLayout;
        }

        if (isset($jsLayout['components']['checkoutProvider']['dictionaries'])) {
            $jsLayout['components']['checkoutProvider']['dictionaries']['city_id'] = $this->getCityOptions();
        }
        return $jsLayout;
    }

    private function getCityOptions()
    {
        $options = $this->cityCollection->create()->toOptionArray();
        $this->sortByKey($options, 'label');
        return $options;
    }

    private function sortByKey(&$data, $key)
    {
        usort($data, function ($a, $b) use ($key) {
            return strcmp($a[$key], $b[$key]);
        });
    }
}
