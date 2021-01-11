<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\ResourceModel\Stocktaking;

use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * StocktakingItem resource model class
 */
class StocktakingItem extends AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ms_stocktaking_item', StocktakingItemInterface::ID);
    }
}
