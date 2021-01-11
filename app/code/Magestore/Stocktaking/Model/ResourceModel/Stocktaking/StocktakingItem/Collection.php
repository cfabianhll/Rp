<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magestore\Stocktaking\Model\Stocktaking\StocktakingItem as StocktakingItemModel;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem as StocktakingItemResourceModel;

/**
 * Class Collection
 *
 * Used for StocktakingItem collection
 */
class Collection extends AbstractCollection
{
    /**
     * Define collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            StocktakingItemModel::class,
            StocktakingItemResourceModel::class
        );
    }

    /**
     * Get different counted collection
     *
     * @return $this
     */
    public function getDifferentCountedCollection()
    {
        $this->getSelect()->where(
            'main_table.qty_in_source != main_table.counted_qty'
        );
        return $this;
    }
}
