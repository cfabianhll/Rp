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
 * @package   mirasvit/module-credit
 * @version   1.0.79
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Credit\Controller\Adminhtml\Earning;

use Magento\Framework\Controller\ResultFactory;

class Save extends \Mirasvit\Credit\Controller\Adminhtml\Earning
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            $earning = $this->initModel();

            $earning->addData($data);
            if (isset($data['rule'])) {
                $earning->loadPost($data['rule']);
            }

            try {
                $earning->save();

                $this->messageManager->addSuccess(__('Earning Rule was successfully saved'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(
                        '*/*/edit', ['id' => $earning->getId()]
                    );

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->backendSession->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);

                return;
            }
        }
        $this->messageManager->addError(__('Unable to find Earning Rule to save'));
        $this->_redirect('*/*/');
    }
}
