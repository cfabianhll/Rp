<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Controller\Adminhtml\Stocktaking;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\User\Model\UserFactory;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterfaceFactory;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking as StocktakingResource;
use Magento\Backend\App\Action\Context;

/**
 * Class Save
 *
 * Use for saving stocktaking
 */
class Save extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization
     */
    const ADMIN_RESOURCE = 'Magestore_Stocktaking::create_stocktaking';
    
    /**
     * @var AuthSession
     */
    protected $authSession;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @var SourceRepositoryInterface
     */
    protected $sourceRepository;

    /**
     * @var StocktakingInterfaceFactory
     */
    protected $stocktakingInterfaceFactory;

    /**
     * @var StocktakingResource
     */
    protected $stocktakingResource;

    /**
     * @var UserResource
     */
    protected $userResource;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param AuthSession $authSession
     * @param UserFactory $userFactory
     * @param UserResource $userResource
     * @param SourceRepositoryInterface $sourceRepository
     * @param StocktakingInterfaceFactory $stocktakingInterfaceFactory
     * @param StocktakingResource $stocktakingResource
     */
    public function __construct(
        Context $context,
        AuthSession $authSession,
        UserFactory $userFactory,
        UserResource $userResource,
        SourceRepositoryInterface $sourceRepository,
        StocktakingInterfaceFactory $stocktakingInterfaceFactory,
        StocktakingResource $stocktakingResource
    ) {
        parent::__construct($context);
        $this->authSession = $authSession;
        $this->userFactory = $userFactory;
        $this->userResource = $userResource;
        $this->sourceRepository = $sourceRepository;
        $this->stocktakingInterfaceFactory = $stocktakingInterfaceFactory;
        $this->stocktakingResource = $stocktakingResource;
    }

    /**
     * Save action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->getRequest()->getPostValue()) {
            return $resultRedirect->setPath('*/*/');
        }
        
        $data = $this->prepareStocktakingData();

        /** @var StocktakingInterface $source */
        $model = $this->stocktakingInterfaceFactory->create();
        $model->setData($data);
        try {
            $this->stocktakingResource->save($model);
            if (!$model->getCode()) {
                $id = $model->getId();
                $stocktakingCode = 'ST' . str_pad((string) $id, 8, '0', STR_PAD_LEFT);
                $model->setCode($stocktakingCode);
                $this->stocktakingResource->save($model);
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__("The stock-taking could not be saved."));
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/new');
        }

        $this->messageManager->addSuccessMessage(__("The stock-taking has been saved successfully"));

        if ($this->getRequest()->getParam('back') == 'edit') {
            return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
        }
        return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
    }

    /**
     * Get Stocktaking Data
     *
     * @return array
     */
    public function prepareStocktakingData(): array
    {
        $data = $this->getRequest()->getPostValue();
        if (!isset($data[StocktakingInterface::ID])) {
            $data[StocktakingInterface::CODE] = '';
            $data[StocktakingInterface::STATUS] =
                $data[StocktakingInterface::STOCKTAKING_TYPE] == StocktakingInterface::STOCKTAKING_TYPE_FULL ?
                    StocktakingInterface::STATUS_COUNTING
                    : StocktakingInterface::STATUS_PREPARING;
            /** @var \Magento\User\Model\User $curUser */
            $curUser = $this->authSession->getUser();
            $data[StocktakingInterface::CREATED_BY_ID] = $curUser->getId();
            $data[StocktakingInterface::CREATED_BY_FIRST_NAME] = $curUser->getFirstName();
            $data[StocktakingInterface::CREATED_BY_LAST_NAME] = $curUser->getLastName();
            
            if (isset($data[StocktakingInterface::ASSIGN_USER_ID])) {
                /** @var \Magento\User\Model\User $assignUser */
                $assignUser = $this->userFactory->create();
                $this->userResource->load($assignUser, $data[StocktakingInterface::ASSIGN_USER_ID]);
                if ($assignUser->getId()) {
                    $data[StocktakingInterface::ASSIGN_USER_FIRST_NAME] = $assignUser->getFirstName();
                    $data[StocktakingInterface::ASSIGN_USER_LAST_NAME] = $assignUser->getLastName();
                }
            }

            if (isset($data[StocktakingInterface::SOURCE_CODE])) {
                /** @var \Magento\InventoryApi\Api\Data\SourceInterface $source */
                $source = $this->sourceRepository->get($data[StocktakingInterface::SOURCE_CODE]);
                if ($source && $source->getSourceCode()) {
                    $data[StocktakingInterface::SOURCE_NAME] = $source->getName();
                }
            }
        }
        return $data;
    }
}
