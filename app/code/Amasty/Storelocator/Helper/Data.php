<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Helper;

use Amasty\Storelocator\Model\ResourceModel\LocationProductIndex;

/**
 * Class Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var LocationProductIndex
     */
    private $locationProduct;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        LocationProductIndex $locationProduct,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->locationProduct = $locationProduct;
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function getImageUrl($name)
    {
        $path = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $path . 'amasty/amlocator/'. $name;
    }

    public function validateLocation($location, $product)
    {
        if ($valid = $this->locationProduct->validateLocation(
            $location->getId(),
            $product->getId(),
            $this->storeManager->getStore()->getId()
        )) {
            return true;
        }

        return false;
    }

    public function getDaysNames()
    {
        return [
            'monday' => __('Monday'),
            'tuesday' => __('Tuesday'),
            'wednesday' => __('Wednesday'),
            'thursday' => __('Thursday'),
            'friday' => __('Friday'),
            'saturday' => __('Saturday'),
            'sunday' => __('Sunday'),
        ];
    }
}
