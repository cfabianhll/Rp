<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Indexer\Warehouse\Action;

use Amasty\MultiInventory\Model\Indexer\Warehouse\AbstractAction;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Row
 */
class Row extends AbstractAction
{
    /**
     * @param null $id
     * @throws LocalizedException
     */
    public function execute($id = null)
    {
        if (!isset($id) || empty($id)) {
            throw new LocalizedException(
                __('We can\'t rebuild the index for an undefined product.')
            );
        }

        try {
            $this->reindexRows([$id]);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
