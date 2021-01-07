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



namespace Mirasvit\Rma\Controller\Adminhtml\Field;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Mirasvit\Rma\Controller\Adminhtml\Field
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $field = $this->_initField();

        if ($field->getId()) {
            $this->initPage($resultPage);
            $resultPage->getConfig()->getTitle()->prepend(
                __("Edit Field '%1'", $this->escaper->escapeHtml($field->getName())));
            $this->_addBreadcrumb(
                __('Custom Fields'),
                __('Custom Fields'),
                $this->getUrl('*/*/')
            );
            $this->_addBreadcrumb(
                __('Edit Field '),
                __('Edit Field ')
            );

            return $resultPage;
        } else {
            $this->messageManager->addError(__('The Field does not exist.'));
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            return $resultRedirect->setPath('*/*/');
        }
    }
}
