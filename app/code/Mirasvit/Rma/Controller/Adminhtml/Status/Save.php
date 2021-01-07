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



namespace Mirasvit\Rma\Controller\Adminhtml\Status;

use Magento\Framework\Controller\ResultFactory;

class Save extends \Mirasvit\Rma\Controller\Adminhtml\Status
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($data = $this->getRequest()->getParams()) {
            $status = $this->_initStatus();

            $status->setName($data['name']);
            if (isset($data['admin_message'])) {
                $status->setAdminMessage($data['admin_message']);
            }
            if (isset($data['customer_message'])) {
                $status->setCustomerMessage($data['customer_message']);
            }
            if (isset($data['history_message'])) {
                $status->setHistoryMessage($data['history_message']);
            }
            unset($data['name'], $data['admin_message'], $data['customer_message'], $data['history_message']);

            $status->addData($data);

            try {
                $this->statusRepository->save($status);

                $this->messageManager->addSuccessMessage(__('Status was successfully saved'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        ['id' => $status->getId(), 'store' => $status->getStoreId()]
                    );
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->backendSession->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find Status to save'));
        return $resultRedirect->setPath('*/*/');
    }
}
