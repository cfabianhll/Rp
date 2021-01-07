<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Class Import
 */
abstract class Import extends Action
{
    const ADMIN_RESOURCE = 'Amasty_MultiInventory::import_stocks';
}
