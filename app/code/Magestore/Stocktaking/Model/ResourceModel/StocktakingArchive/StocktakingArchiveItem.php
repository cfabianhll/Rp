<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\ResourceModel\StocktakingArchive;

use Magestore\Stocktaking\Api\Data\StocktakingArchiveItemInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * StocktakingArchiveItem resource model class
 */
class StocktakingArchiveItem extends AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ms_stocktaking_archive_item', StocktakingArchiveItemInterface::ID);
    }
}
