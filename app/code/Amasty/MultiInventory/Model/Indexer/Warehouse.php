<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Indexer;

use Amasty\MultiInventory\Model\Indexer\Warehouse\Action\Full;
use Amasty\MultiInventory\Model\Indexer\Warehouse\Action\Row;
use Amasty\MultiInventory\Model\Indexer\Warehouse\Action\Rows;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mview\ActionInterface;

/**
 * Class Warehouse
 */
class Warehouse implements \Magento\Framework\Indexer\ActionInterface, ActionInterface
{
    /**
     * @var Warehouse\Action\Row
     */
    private $productStockIndexerRow;

    /**
     * @var Warehouse\Action\Rows
     */
    private $productStockIndexerRows;

    /**
     * @var Warehouse\Action\Full
     */
    private $productStockIndexerFull;

    /**
     * Warehouse constructor.
     *
     * @param Warehouse\Action\Row $productStockIndexerRow
     * @param Warehouse\Action\Rows $productStockIndexerRows
     * @param Warehouse\Action\Full $productStockIndexerFull
     */
    public function __construct(
        Row $productStockIndexerRow,
        Rows $productStockIndexerRows,
        Full $productStockIndexerFull
    ) {
        $this->productStockIndexerRow = $productStockIndexerRow;
        $this->productStockIndexerRows = $productStockIndexerRows;
        $this->productStockIndexerFull = $productStockIndexerFull;
    }

    /**
     * @param int[] $ids
     *
     * @throws LocalizedException
     */
    public function execute($ids)
    {
        $this->productStockIndexerRows->execute($ids);
    }

    /**
     *
     * @return void
     * @throws LocalizedException
     */
    public function executeFull()
    {
        $this->productStockIndexerFull->execute();
    }

    /**
     * @param array $ids
     *
     * @throws LocalizedException
     */
    public function executeList(array $ids)
    {
        $this->productStockIndexerRows->execute($ids);
    }

    /**
     * @param int $id
     *
     * @throws LocalizedException
     */
    public function executeRow($id)
    {
        $this->productStockIndexerRow->execute($id);
    }
}
