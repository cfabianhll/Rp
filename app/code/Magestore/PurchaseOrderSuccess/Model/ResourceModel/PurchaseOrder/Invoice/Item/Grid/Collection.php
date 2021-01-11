<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Model\ResourceModel\PurchaseOrder\Invoice\Item\Grid;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magestore\PurchaseOrderSuccess\Model\ResourceModel\PurchaseOrder\Invoice\Item;
use Psr\Log\LoggerInterface as Logger;
use Magestore\PurchaseOrderSuccess\Api\Data\InvoiceItemInterface;

/**
 * Class Collection
 *
 * Item grid collection
 */
class Collection extends SearchResult
{
    /**
     * @var array $mappingFilterField
     */
    protected $mappingFilterField = [
        'qty_billed' => 'main_table.qty_billed',
        'tax' => 'main_table.tax',
        'discount' => 'main_table.discount'
    ];

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Collection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param RequestInterface $request
     * @param string $mainTable
     * @param string $resourceModel
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        RequestInterface $request,
        $mainTable = 'os_purchase_order_invoice_item',
        $resourceModel = Item::class
    ) {
        $this->request = $request;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Init select
     *
     * @return $this|void
     */
    protected function _initSelect()
    {
        $id = $this->request->getParam('invoice_id', null);
        $this->getSelect()->from(['main_table' => $this->getMainTable()])
            ->joinLeft(
                ['purchase_order_item' => $this->getTable('os_purchase_order_item')],
                'main_table.purchase_order_item_id = purchase_order_item.purchase_order_item_id',
                ['product_id', 'product_sku', 'product_name', 'product_supplier_sku']
            )
            ->joinLeft(
                ['purchase_order' => $this->getTable('os_purchase_order')],
                'purchase_order_item.purchase_order_id = purchase_order.purchase_order_id',
                ['currency_code']
            );
        if ($id) {
            $this->getSelect()->where(
                'main_table.' . InvoiceItemInterface::PURCHASE_ORDER_INVOICE_ID . ' = ?',
                $id
            );
        }
        return $this;
    }

    /**
     * Add field to filter
     *
     * @param array|string $field
     * @param array|null $condition
     * @return Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        foreach ($this->mappingFilterField as $key => $value) {
            if ($field == $key) {
                $field = $value;
            }
        }
        return parent::addFieldToFilter($field, $condition);
    }
}
