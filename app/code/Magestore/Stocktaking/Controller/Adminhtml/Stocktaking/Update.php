<?php

/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Controller\Adminhtml\Stocktaking;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magento\Backend\App\Action\Context;
use Magestore\Stocktaking\Api\StocktakingRepositoryInterface;
use Magento\Backend\App\Action;

/**
 * Class Update Stocktaking
 */
class Update extends Action implements HttpPostActionInterface
{
    /**
     * Authorization
     */
    const ADMIN_RESOURCE = 'Magestore_Stocktaking::edit_stocktaking';

    const ACTION_TYPE_START_COUNTING = 'start_counting';
    const ACTION_TYPE_COMPLETE_COUNTING = 'complete_counting';
    const ACTION_TYPE_BACK_TO_PREPARE = 'back_to_prepare';

    /**
     * @var StocktakingRepositoryInterface
     */
    protected $stocktakingRepositoryInterface;

    /**
     * Cancel constructor
     *
     * @param Context $context
     * @param StocktakingRepositoryInterface $stocktakingRepositoryInterface
     */
    public function __construct(
        Context $context,
        StocktakingRepositoryInterface $stocktakingRepositoryInterface
    ) {
        parent::__construct($context);
        $this->stocktakingRepositoryInterface = $stocktakingRepositoryInterface;
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $id = (int) $data['general_information']['id'];
        /** @var StocktakingInterface $model */
        $model = $this->stocktakingRepositoryInterface->load($id);
        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This stock-taking no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }
        
        $actionType = $this->getRequest()->getParam('type');
        try {
            switch ($actionType) {
                case self::ACTION_TYPE_START_COUNTING:
                    $result = $this->stocktakingRepositoryInterface->startCounting($id, $data);
                    break;

                case self::ACTION_TYPE_BACK_TO_PREPARE:
                    $result = $this->stocktakingRepositoryInterface->backToPrepare($id, $data);
                    break;
    
                case self::ACTION_TYPE_COMPLETE_COUNTING:
                    $result = $this->stocktakingRepositoryInterface->completeCounting($id, $data);
                    break;
    
                default:
                    $result = $this->stocktakingRepositoryInterface->saveFormData($model->getId(), $data);
                    break;
            }

            if ($result['status']) {
                $this->messageManager->addSuccessMessage($result['message']);
            } else {
                $this->messageManager->addErrorMessage($result['message']);
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        
        return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
    }
}
