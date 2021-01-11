<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\ResourceModel\StocktakingArchive;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magestore\Stocktaking\Model\StocktakingArchive as StocktakingArchiveModel;
use Magestore\Stocktaking\Model\ResourceModel\StocktakingArchive as StocktakingArchiveResourceModel;

/**
 * Class Collection
 *
 * Used for Stocktaking collection
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            StocktakingArchiveModel::class,
            StocktakingArchiveResourceModel::class
        );
    }
}
