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



namespace Mirasvit\Rma\Controller\Adminhtml\Reason;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Mirasvit\Rma\Controller\Adminhtml\Reason
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $reason = $this->_initReason();

        if ($reason->getId()) {
            $this->initPage($resultPage);
            $resultPage->getConfig()->getTitle()->prepend(
                __("Edit Reason '%1'", $this->escaper->escapeHtml($reason->getName())));
            $this->_addBreadcrumb(
                __('Reason'),
                __('Reason'),
                $this->getUrl('*/*/')
            );
            $this->_addBreadcrumb(
                __('Edit Reason '),
                __('Edit Reason ')
            );

            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__('The Reason does not exist.'));
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            return $resultRedirect->setPath('*/*/');
        }
    }
}
