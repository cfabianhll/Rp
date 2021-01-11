<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Model\ResourceModel\PurchaseOrder\Invoice\Grid;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magestore\PurchaseOrderSuccess\Model\ResourceModel\PurchaseOrder\Invoice;
use Psr\Log\LoggerInterface as Logger;
use Magestore\PurchaseOrderSuccess\Api\Data\InvoiceInterface;
use Zend_Db_Expr;

/**
 * Class Collection
 *
 * Use for invoice collection
 */
class Collection extends SearchResult
{
    /**
     * @var array $mappingFilterField
     */
    protected $mappingFilterField = [
        'grand_total_incl_tax' => 'main_table.grand_total_incl_tax',
        'total_qty_billed' => 'main_table.total_qty_billed'
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
        $mainTable = 'os_purchase_order_invoice',
        $resourceModel = Invoice::class
    ) {
        $this->request = $request;
        $this->mappingFilterField['total_paid']
            = new Zend_Db_Expr('main_table.grand_total_incl_tax - main_table.total_due');
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Init select
     *
     * @return $this|Collection|void
     */
    protected function _initSelect()
    {
        $id = $this->request->getParam('purchase_id', null);
        $this->getSelect()->from(['main_table' => $this->getMainTable()])
            ->joinLeft(
                ['purchase_order' => $this->getTable('os_purchase_order')],
                'main_table.purchase_order_id = purchase_order.purchase_order_id',
                ['currency_code']
            )
            ->columns([
                'total_paid' => new Zend_Db_Expr('main_table.grand_total_incl_tax - main_table.total_due')
            ]);
        if ($id) {
            $this->getSelect()->where(
                'main_table.' . InvoiceInterface::PURCHASE_ORDER_ID . ' = ?',
                $id
            );
        }
        return $this;
    }

    /**
     * Add field to filter
     *
     * @param mixed $field
     * @param array|null $condition
     * @return $this|AbstractDb
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
