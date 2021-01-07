<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Ui\CatalogInventory\DataProvider\Product;

use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection;

/**
 * Class AddQuantityFieldToCollection
 */
class AddQuantityFieldToCollection
{
    /**
     * @var System
     */
    private $system;

    /**
     * @var WarehouseFactory
     */
    private $whFactory;

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * AddQuantityFieldToCollection constructor.
     *
     * @param System $system
     * @param WarehouseFactory $whFactory
     * @param ResourceConnection $connection
     */
    public function __construct(
        System $system,
        WarehouseFactory $whFactory,
        ResourceConnection $connection
    ) {
        $this->system = $system;
        $this->whFactory = $whFactory;
        $this->connection = $connection;
    }

    /**
     * @param \Magento\CatalogInventory\Ui\DataProvider\Product\AddQuantityFieldToCollection $object
     * @param \Closure $work
     * @param Collection $collection
     * @param $field
     * @param null $alias
     */
    public function aroundAddField(
        \Magento\CatalogInventory\Ui\DataProvider\Product\AddQuantityFieldToCollection $object,
        \Closure $work,
        Collection $collection,
        $field,
        $alias = null
    ) {
        if ($this->system->isMultiEnabled()) {
            $collection->joinField(
                'qty',
                $this->connection->getTableName('amasty_multiinventory_warehouse_item'),
                'qty',
                'product_id=entity_id',
                sprintf('{{table}}.warehouse_id=%s', $this->whFactory->create()->getDefaultId()),
                'left'
            );
        } else {
            $work($collection, $field, $alias);
        }
    }
}
