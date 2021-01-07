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

class Save extends \Mirasvit\Rma\Controller\Adminhtml\Rule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($data = $this->getRequest()->getParams()) {

            $rule = $this->_initRule();

            $rule->setName($data['name']);
            $rule->setEmailSubject($data['email_subject']);
            $rule->setEmailBody($data['email_body']);
            unset($data['name'], $data['email_subject'], $data['email_body']);

            if (empty($data['status_id'])) {
                $data['status_id'] = null;
            }
            if (empty($data['user_id'])) {
                $data['user_id'] = null;
            }

            $rule->addData($data);
            if (isset($data['rule'])) {
                $rule->loadPost($data['rule']);
            }

            try {
                $rule->save();

                $this->messageManager->addSuccessMessage(__('Rule was successfully saved'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $rule->getId(),
                        'store' => $rule->getStoreId()]);

                    return;
                }
                return $resultRedirect->setPath('*/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->backendSession->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);

                return;
            }
        }
        $this->messageManager->addError(__('Unable to find Rule to save'));
        return $resultRedirect->setPath('*/*/');
    }
}
