<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Sorting\Method;

use Amasty\MultiInventory\Helper\System;
use Amasty\Sorting\Model\Source\Stock as StockSource;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Instock
 */
class Instock
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var System
     */
    private $system;

    /**
     * Instock constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     * @param System $system
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request,
        System $system
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->system = $system;
    }

    /**
     * @param \Amasty\Sorting\Model\ResourceModel\Method\Instock $object
     * @param \Closure $proceed
     * @param Collection $collection
     * @param string $direction
     *
     * @return \Amasty\Sorting\Model\ResourceModel\Method\Instock
     * @throws \Zend_Db_Select_Exception
     */
    public function aroundApply($object, $proceed, $collection, $direction = '')
    {
        if (!$this->isMethodActive($collection)) {
            return $object;
        }

        if (!$this->system->isMultiEnabled()) {
            return $proceed($collection, $direction);
        }

        if ($this->scopeConfig->getValue(
            'amsorting/general/out_of_stock_qty',
            ScopeInterface::SCOPE_STORE,
            null
        )
        ) {
            $stockColumn = $collection->getConnection()
                ->getIfNullSql('warehouse_index.qty', 'stock_status_index.qty');

            $collection->getSelect()->columns(
                ['index_qty' => $stockColumn]
            );
            $collection->getSelect()->order(
                /** IF(stock_status_index.qty > 0, 0, 1) */
                $collection->getConnection()->getCheckSql('index_qty > 0', '0', '1')
            );
        } else {
            $collection->getSelect()->order('is_salable ' . $collection::SORT_ORDER_DESC);
        }

        $orders = $collection->getSelect()->getPart(\Zend_Db_Select::ORDER);
        // move from the last to the the first position
        array_unshift($orders, array_pop($orders));
        $collection->getSelect()->setPart(\Zend_Db_Select::ORDER, $orders);

        return $object;
    }

    /**
     * Check if out ot stock products should be the last
     *
     * @param $collection
     * @return bool
     */
    private function isMethodActive($collection)
    {
        if ($collection->getFlag('amasty_stock_sorted')) {
            return false;
        }

        // is out of stock is not displayed, method don't need to be applied
        $isShowOutOfStock = $this->scopeConfig->getValue(
            Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            ScopeInterface::SCOPE_STORE
        );
        if (!$isShowOutOfStock) {
            return false;
        }

        $show = $this->scopeConfig->getValue(
            'amsorting/general/out_of_stock_last',
            ScopeInterface::SCOPE_STORE,
            null
        );

        if (!$show || ($show == StockSource::SHOW_LAST_FOR_CATALOG && $this->isSearchModule())) {
            return false;
        }

        return true;
    }

    /**
     * skip search results
     *
     * @return bool
     */
    private function isSearchModule()
    {
        return in_array(
            $this->request->getModuleName(),
            ['sqli_singlesearchresult', 'catalogsearch']
        );
    }
}
