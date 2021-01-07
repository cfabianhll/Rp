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



namespace Mirasvit\Rma\Controller\Adminhtml\Rule;

use Magento\Framework\Controller\ResultFactory;

class Add extends \Mirasvit\Rma\Controller\Adminhtml\Rule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->_initRule();

        $this->initPage($resultPage);
        $resultPage->getConfig()->getTitle()->prepend(__('New Rule'));
        $this->_addBreadcrumb(
            __('Rule Manager'),
            __('Rule Manager'),
            $this->getUrl('*/*/')
        );
        $this->_addBreadcrumb(__('Add Rule '), __('Add Rule'));

        $resultPage->getLayout()
            ->getBlock('head')
            ;
        $resultPage->getLayout()->getBlock('head');

        return $resultPage;
    }
}
