<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\DataProvider\Product;

use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;

/**
 * Class AddQuantityShipFieldToCollection
 */
class AddQuantityShipFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * @var WarehouseFactory
     */
    private $whFactory;

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * ProductDataProvider constructor.
     *
     * @param WarehouseFactory $whFactory
     * @param ResourceConnection $connection
     */
    public function __construct(
        WarehouseFactory $whFactory,
        ResourceConnection $connection
    ) {
        $this->whFactory = $whFactory;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        $collection->joinField(
            'ship_qty',
            $this->connection->getTableName('amasty_multiinventory_warehouse_item'),
            'ship_qty',
            'product_id=entity_id',
            sprintf('{{table}}.warehouse_id=%s', $this->whFactory->create()->getDefaultId()),
            'left'
        );
    }
}
