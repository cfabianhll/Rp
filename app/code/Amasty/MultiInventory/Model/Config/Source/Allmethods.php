<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\Registry;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Allmethods
 */
class Allmethods implements ArrayInterface
{
    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Config
     */
    protected $shippingConfig;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $shippingConfig
     * @param Registry $coreRegistry
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $shippingConfig,
        Registry $coreRegistry
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->shippingConfig = $shippingConfig;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Return array of carriers.
     * If $isActiveOnlyFlag is set to true, will return only active carriers
     *
     * @param bool $isActiveOnlyFlag
     * @return array
     */
    public function toOptionArray($isActiveOnlyFlag = false)
    {
        $methods = [['value' => '', 'label' => '']];
        $carriers = $this->shippingConfig->getAllCarriers();

        foreach ($carriers as $carrierCode => $carrierModel) {
            if (!$carrierModel->isActive() && (bool)$isActiveOnlyFlag === true) {
                continue;
            }
            if (!$carrierModel->getAllowedMethods()) {
                continue;
            }
            $carrierTitle = $this->scopeConfig->getValue(
                'carriers/' . $carrierCode . '/title',
                ScopeInterface::SCOPE_STORE
            );
            $methods[$carrierCode] = ['label' => $carrierTitle, 'value' => $carrierCode, 'rate' => ''];
        }
        if ($model = $this->coreRegistry->registry('amasty_multi_inventory_warehouse')) {
            if ($shippings = $model->getShippings()) {
                foreach ($shippings as $item) {
                    $methods[$item->getShippingMethod()]['rate'] = $item->getRate();
                }
            }
        }
        return $methods;
    }
}
