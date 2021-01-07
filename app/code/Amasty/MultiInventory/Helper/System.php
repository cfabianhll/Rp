<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Helper;

use Amasty\MultiInventory\Block\Adminhtml\System\Config\Field\PriorityValues;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class System
 */
class System extends AbstractHelper
{
    use \Amasty\MultiInventory\Traits\Additional;

    const ORDER_SHIPMENT = 0;

    const ORDER_CREATION = 1;

    const ORDER_INVOICED = 2;

    const CONFIG_ENABLE_MULTI = 'amasty_multi_inventory/stock/enabled_multi';

    const CONFIG_FIELD_PRIORITY = PriorityValues::class;

    const CONFIG_DECREASE_STOCK = 'amasty_multi_inventory/stock/decrease_stock';

    /**
     * @return bool
     */
    public function isMultiEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_ENABLE_MULTI,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnableLog()
    {
        return $this->scopeConfig->isSetFlag(
            'amasty_multi_inventory/general/enable_log',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isAddressSuggestionEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'amasty_multi_inventory/general/google_address',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * If Warehouse can be taken only for store.
     * 1 warehouse 1 store.
     * If 'true' then current store view warehouse stock is used only.
     * All other warehouses and criteria is ignored.
     *
     * @return bool
     */
    public function isLockOnStore()
    {
        return $this->scopeConfig->isSetFlag(
            'amasty_multi_inventory/stock/lock_on_store',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getBackorders($store = null)
    {
        return (int) $this->scopeConfig->getValue(
            Configuration::XML_PATH_BACKORDERS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return mixed
     */
    public function getGoogleMapsKey()
    {
        return $this->scopeConfig->getValue(
            'amasty_multi_inventory/general/google_api',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getGoogleDistancematrix()
    {
        return $this->scopeConfig->getValue(
            'amasty_multi_inventory/general/google_distancematrix',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getDisplayInGrid()
    {
        return $this->scopeConfig->getValue(
            'amasty_multi_inventory/general/display_in_grid',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getLowStock()
    {
        return $this->scopeConfig->getValue(
            'amasty_multi_inventory/stock/low_stock',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getAvailableDecreese()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_DECREASE_STOCK,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getPhysicalDecreese()
    {
        return $this->scopeConfig->getValue(
            'amasty_multi_inventory/stock/decrease_physical',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getDefinationWarehouse()
    {
        return $this->scopeConfig->getValue(
            'amasty_multi_inventory/stock/defination_warehouse',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getReturnCreditmemo()
    {
        return $this->scopeConfig->getValue(
            'amasty_multi_inventory/stock/return_creditmemo',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get dispatches from config and sort pirorities
     *
     * @return array
     */
    public function getDispatchOrder()
    {
        if ($this->isLockOnStore()) {
            return ['store_view' => ['is_active' => 1, 'priority' => 1]];
        }
        $suffix = 'amasty_multi_inventory/stock/dispatch_order';
        $data = $this->scopeConfig->getValue(
            $suffix,
            ScopeInterface::SCOPE_STORE
        );

        foreach ($data as $key => &$element) {
            $element['priority'] = $this->scopeConfig->getValue(
                $suffix . "_" . $key . "_priority",
                ScopeInterface::SCOPE_STORE
            );
            $element['is_active'] = $this->scopeConfig->getValue(
                $suffix . "_" . $key . "_is_active",
                ScopeInterface::SCOPE_STORE
            );

            if (!$element['is_active']) {
                unset($data[$key]);
            }
        }
        uasort($data, ["self", "sortPriority"]);

        return $data;
    }

    /**
     * @return mixed
     */
    public function getSeparateOrders()
    {
        return $this->scopeConfig->getValue(
            'amasty_multi_inventory/stock/separate_orders',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Default magento Catalog setting
     *
     * @return int|bool
     */
    public function isShowOutOfStock()
    {
        return $this->scopeConfig->getValue(
            Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getBackordersUseDefault()
    {
        return $this->scopeConfig->getValue(
            'amasty_multi_inventory/stock/backorders_default'
        );
    }

    /**
     * Backorders algorithm
     *
     * @return int
     */
    public function getBackordersAction()
    {
        return $this->scopeConfig->getValue(
            'amasty_multi_inventory/stock/backorders_action'
        );
    }

    /**
     * @return bool
     */
    public function isUseGoogleForDistance()
    {
        return (bool) $this->scopeConfig->getValue(
            'amasty_multi_inventory/stock/use_google_to_locate'
        );
    }

    /**
     * @return string
     */
    public function getGeocodeUrl()
    {
        return $this->scopeConfig->getValue(
            'amasty_multi_inventory/general/google_geocode'
        );
    }
}
