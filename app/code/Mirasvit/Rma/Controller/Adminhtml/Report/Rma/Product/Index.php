<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Controller\Adminhtml\Report\Rma\Product;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Mirasvit\Rma\Controller\Adminhtml\Report\Rma\Product
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage);
        $resultPage->getConfig()->getTitle()->prepend(__('RMA by Product'));
        $gridBlock = $resultPage->getLayout()->getBlock('adminhtml_report_rma_product.grid');
        $filterFormBlock = $resultPage->getLayout()->getBlock('grid.filter.form');
        $this->_initReportAction([
            $gridBlock,
            $filterFormBlock,
        ]);

        return $resultPage;
    }
}
