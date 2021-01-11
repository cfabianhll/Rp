<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magestore\Stocktaking\Api\Data\StocktakingArchiveInterface;

/**
 * Class StocktakingArchive
 *
 * Used for stocktaking archive resource model
 */
class StocktakingArchive extends AbstractDb
{
    /**
     * Main table primary key field name
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Resource Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ms_stocktaking_archive', StocktakingArchiveInterface::ID);
    }
}
