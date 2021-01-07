<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Import;

use Amasty\MultiInventory\Controller\Adminhtml\AbstractImport;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassComplete
 */
class MassComplete extends AbstractImport
{
    /**
     * Execute action
     *
     * @return Redirect
     */
    public function execute()
    {
        parent::import();

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('amasty_multi_inventory/*/index');
    }
}
