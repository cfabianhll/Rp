<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Model\ResourceModel\Rma;

/**
 * @method \Mirasvit\Rma\Model\Rma getFirstItem()
 * @method \Mirasvit\Rma\Model\Rma getLastItem()
 * @method \Mirasvit\Rma\Model\ResourceModel\Rma\Collection|\Mirasvit\Rma\Model\Rma[] addFieldToFilter
 * @method \Mirasvit\Rma\Model\ResourceModel\Rma\Collection|\Mirasvit\Rma\Model\Rma[] setOrder
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'rma_id';
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    private $resource;
    /**
     * @var null
     */
    private $connection;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
     */
    private $fetchStrategy;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Framework\Data\Collection\EntityFactoryInterface
     */
    private $entityFactory;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->entityFactory = $entityFactory;
        $this->logger = $logger;
        $this->fetchStrategy = $fetchStrategy;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->connection = $connection;
        $this->resource = $resource;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\Rma', 'Mirasvit\Rma\Model\ResourceModel\Rma');
    }

    /**
     * @param bool|string $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        $arr = [];
        if ($emptyOption) {
            $arr[0] = ['value' => 0, 'label' => __('-- Please Select --')];
        }
        /** @var \Mirasvit\Rma\Model\Rma $item */
        foreach ($this as $item) {
            $arr[] = ['value' => $item->getId(), 'label' => $item->getName()];
        }

        return $arr;
    }

    /**
     * @param string|false $emptyOption
     *
     * @return array
     */
    public function getOptionArray($emptyOption = false)
    {
        $arr = [];
        if ($emptyOption) {
            $arr[0] = __('-- Please Select --');
        }
        /** @var \Mirasvit\Rma\Model\Rma $item */
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getName();
        }

        return $arr;
    }

    /**
     * @param \Magento\Store\Model\Store $storeId
     *
     * @return $this
     */
    public function addStoreIdFilter($storeId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('mst_rma_rma_store')}`
                AS `rma_store_table`
                WHERE main_table.rma_id = rma_store_table.rs_rma_id
                AND rma_store_table.rs_store_id in (?))", [0, $storeId]);

        return $this;
    }

    /**
     * @param string $exchangeOrderId
     *
     * @return $this
     */
    public function addExchangeOrderFilter($exchangeOrderId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('mst_rma_rma_order')}`
                AS `rma_order_table`
                WHERE main_table.rma_id = rma_order_table.re_rma_id
                AND rma_order_table.re_exchange_order_id in (?))", [-1, $exchangeOrderId]);

        return $this;
    }

    /**
     * @param string $replacementOrderId
     *
     * @return $this
     */
    public function addReplacementOrderFilter($replacementOrderId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('mst_rma_rma_replacement_order')}`
                AS `rma_replacement_order`
                WHERE main_table.rma_id = rma_replacement_order.rma_id
                AND rma_replacement_order.replacement_order_id in (?))", [-1, $replacementOrderId]);

        return $this;
    }

    /**
     * @param string $creditMemoId
     *
     * @return $this
     */
    public function addCreditMemoFilter($creditMemoId)
    {
        $this->getSelect()
            ->where("EXISTS (SELECT * FROM `{$this->getTable('mst_rma_rma_creditmemo')}`
                AS `rma_creditmemo_table`
                WHERE main_table.rma_id = rma_creditmemo_table.rc_rma_id
                AND rma_creditmemo_table.rc_credit_memo_id in (?))", [-1, $creditMemoId]);

        return $this;
    }

    /**
     * @param int $orderId
     *
     * @return $this
     */
    public function addOrderFilter($orderId)
    {
        $select = $this->getSelect();
        $select->joinInner(
            ['rma_item' => $this->getTable('mst_rma_item')],
            'main_table.rma_id = rma_item.rma_id',
            ''
        );
        $select->joinLeft(
            ['order_item' => $this->getTable('sales_order_item')],
            'order_item.item_id = rma_item.order_item_id',
            ''
        );
        $select->where('main_table.`order_id` = ? OR order_item.order_id = ?', $orderId);
        $select->group('main_table.rma_id');

        return $this;
    }

    /**
     *
     */
    protected function initFields()
    {
        /* @noinspection PhpUnusedLocalVariableInspection */
        $select = $this->getSelect();
        $select->joinLeft(
            ['status' => $this->getTable('mst_rma_status')],
            'main_table.status_id = status.status_id',
            ['status_name' => 'status.name']
        );
        $select->columns(['name' => new \Zend_Db_Expr("CONCAT(main_table.firstname, ' ', main_table.lastname)")]);
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->initFields();
    }

     /************************/
}
