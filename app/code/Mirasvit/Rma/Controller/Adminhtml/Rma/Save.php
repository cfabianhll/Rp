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



namespace Mirasvit\Rma\Controller\Adminhtml\Rma;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Mirasvit\Rma\Controller\Adminhtml\Rma;
use \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface;

class Save extends Rma
{
    /**
     * @var PostDataProcessor
     */
    private $dataProcessor;
    /**
     * @var PerformerFactoryInterface
     */
    private $performer;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SaveInterface
     */
    private $rmaSaveService;

    /**
     * Save constructor.
     * @param PerformerFactoryInterface $performer
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SaveInterface $rmaSaveService
     * @param PostDataProcessor $dataProcessor
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface $performer,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SaveInterface $rmaSaveService,
        \Mirasvit\Rma\Controller\Adminhtml\Rma\PostDataProcessor $dataProcessor,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->dataProcessor      = $dataProcessor;
        $this->performer          = $performer;
        $this->rmaSaveService     = $rmaSaveService;

        parent::__construct($context);
    }


    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($data = $this->getRequest()->getParams()) {
            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect->setPath('*/*/edit',
                    ['id' => $data['rma_id'], '_current' => true]);
            }
            try {
                if (!empty($data['is_offline']) && !empty($data['orders'])) {
                    $data = $this->dataProcessor->createOfflineOrder($data);
                }

                $performer = $this->performer->create(PerformerFactoryInterface::USER, $this->_auth->getUser());
                $rma = $this->rmaSaveService->saveRma(
                    $performer,
                    $this->dataProcessor->filterRmaData($data),
                    $this->dataProcessor->filterRmaItems($data)
                );

                $this->messageManager->addSuccessMessage(__('RMA was successfully saved'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $rma->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->backendSession->setFormData($data);
                $id = $this->getRequest()->getParam('id');
                if (!empty($rma) && $rma->getId()) {
                    $id = $rma->getId();
                }
                if ($id) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
                }
                if (!empty($data['rma_id'])) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $data['rma_id']]);
                } else {
                    return $resultRedirect->setPath(
                        '*/*/add',
                        ['order_id' => $this->getRequest()->getParam('order_id')]
                    );
                }
            }
        }
        $this->messageManager->addError(__('Unable to find rma to save'));

        return $resultRedirect->setPath('*/*/');
    }
}