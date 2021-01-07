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



namespace Mirasvit\Rma\Controller\Adminhtml\Resolution;

use \Magento\Framework\Controller\ResultFactory;
use \Mirasvit\Rma\Api\Data\ResolutionInterface;

class Delete extends \Mirasvit\Rma\Controller\Adminhtml\Resolution
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            try {
                if (in_array($id, ResolutionInterface::RESERVED_IDS)) {
                    $this->messageManager->addWarningMessage(
                        __(
                            'This resolution "%1" is reserved. You can only deactivate it',
                            $this->resolutionRepository->get($id)->getName()
                        )
                    );
                } else {
                    $this->resolutionRepository->deleteById($id);
                    $this->messageManager->addSuccessMessage(__('Resolution was successfully deleted'));
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
