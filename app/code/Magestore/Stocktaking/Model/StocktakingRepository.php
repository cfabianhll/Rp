<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model;

use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterfaceFactory;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking as StocktakingResource;
use Magestore\Stocktaking\Api\StocktakingRepositoryInterface;
use Psr\Log\LoggerInterface as Logger;
use Magento\User\Model\UserFactory;
use Magestore\Stocktaking\Api\StocktakingItemRepositoryInterface;
use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magestore\Stocktaking\Api\StocktakingManagementInterface;

/**
 * Class StocktakingRepository
 *
 * Used for stocktaking repository
 */
class StocktakingRepository implements StocktakingRepositoryInterface
{
    /**
     * @var StocktakingInterfaceFactory
     */
    protected $stocktakingFactory;

    /**
     * @var StocktakingResource
     */
    protected $stocktakingResource;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @var StocktakingItemRepositoryInterface
     */
    protected $stocktakingItemRepository;

    /**
     * @var StocktakingManagementInterface
     */
    protected $stocktakingManagement;

    /**
     * StocktakingRepository constructor.
     *
     * @param StocktakingInterfaceFactory $stocktakingFactory
     * @param StocktakingResource $stocktakingResource
     * @param Logger $logger
     * @param UserFactory $userFactory
     * @param StocktakingItemRepositoryInterface $stocktakingItemRepository
     * @param StocktakingManagementInterface $stocktakingManagement
     */
    public function __construct(
        StocktakingInterfaceFactory $stocktakingFactory,
        StocktakingResource $stocktakingResource,
        Logger $logger,
        UserFactory $userFactory,
        StocktakingItemRepositoryInterface $stocktakingItemRepository,
        StocktakingManagementInterface $stocktakingManagement
    ) {
        $this->stocktakingFactory = $stocktakingFactory;
        $this->stocktakingResource = $stocktakingResource;
        $this->logger = $logger;
        $this->userFactory = $userFactory;
        $this->stocktakingItemRepository = $stocktakingItemRepository;
        $this->stocktakingManagement = $stocktakingManagement;
    }

    /**
     * Used for load stocktaking
     *
     * @param int $id
     * @return StocktakingInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function load(int $id)
    {
        $stocktaking = $this->stocktakingFactory->create();
        $this->stocktakingResource->load($stocktaking, $id);
        return $stocktaking;
    }

    /**
     * @inheritDoc
     */
    public function save(StocktakingInterface $stocktaking)
    {
        try {
            return $this->stocktakingResource->save($stocktaking);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
    }

    /**
     * @inheritDoc
     */
    public function cancel(int $id): bool
    {
        $stocktaking = $this->load($id);
        if (!$stocktaking->getId()) {
            return false;
        }
        $stocktaking->setStatus(StocktakingInterface::STATUS_CANCELED);
        try {
            $this->stocktakingResource->moveToArchive($stocktaking);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            return false;
        }
        
        return true;
    }

    /**
     * @inheritDoc
     */
    public function saveFormData(int $id, array $data, int $updateStatus = null)
    {
        try {
            /** @var StocktakingInterface $model */
            $model = $this->load($id);
            if ($model->getId() && $data) {
                // Update status
                if ($updateStatus && is_int($updateStatus)) {
                    $model->setStatus($updateStatus);
                }

                //Save General Information
                $generalData = $data['general_information'];
                $model->setCreatedAt($generalData[StocktakingInterface::CREATED_AT]);
                if (isset($generalData[StocktakingInterface::ASSIGN_USER_ID])) {
                    /** @var \Magento\User\Model\User $assignUser */
                    $assignUser = $this->userFactory->create();
                    $assignUser->getResource()->load($assignUser, $generalData[StocktakingInterface::ASSIGN_USER_ID]);
                    if ($assignUser && $assignUser->getId()) {
                        $model->setAssignUserId($assignUser->getId());
                        $model->setAssignUserFirstName($assignUser->getFirstName());
                        $model->setAssignUserLastName($assignUser->getLastName());
                    }
                }
                $model->setDescription($generalData[StocktakingInterface::DESCRIPTION]);
                $this->save($model);

                // Save stocktaking items
                $stocktakingItems = $this->prepareStocktakingItemsData(
                    $id,
                    json_decode($data['product_list']['product_dynamic_grid']['links']['product_list'], true)
                );
                $this->stocktakingItemRepository->setStocktakingItems(
                    $id,
                    $stocktakingItems
                );

            }
            return [
                'status' => true,
                'message' => __("The stock-taking has been saved successfully")
            ];
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            return [
                'status' => false,
                'message' => __("The stock-taking could not be saved.")
            ];
        }
    }

    /**
     * Prepare Stocktaking Items Data From Form
     *
     * @param int $stocktakingId
     * @param array $productListData
     * @return array
     */
    public function prepareStocktakingItemsData(int $stocktakingId, array $productListData)
    {
        $stocktakingItems = [];
        foreach ($productListData as $product) {
            $stocktakingItems[] = [
                StocktakingItemInterface::STOCKTAKING_ID => $stocktakingId,
                StocktakingItemInterface::PRODUCT_ID => $product['id'],
                StocktakingItemInterface::PRODUCT_NAME => $product['name'],
                StocktakingItemInterface::PRODUCT_SKU => $product['sku'],
                StocktakingItemInterface::QTY_IN_SOURCE =>
                    isset($product['qty_in_source']) ? $product['qty_in_source'] : 0,
                StocktakingItemInterface::COUNTED_QTY =>
                    isset($product['counted_qty']) ? $product['counted_qty'] : 0,
                StocktakingItemInterface::DIFFERENCE_REASON => $product['difference_reason']
            ];
        }
        return $stocktakingItems;
    }

    /**
     * @inheritDoc
     */
    public function startCounting(int $stocktakingId, array $data)
    {
        $result = $this->saveFormData($stocktakingId, $data, StocktakingInterface::STATUS_COUNTING);
        if (!$result['status']) {
            return [
                'status' => false,
                'message' => __("The stock-taking can not start counting.")
            ];
        }
        return [
            'status' => true,
            'message' => __("The stock-taking has been ready to count.")
        ];
    }

    /**
     * @inheritDoc
     */
    public function backToPrepare(int $stocktakingId, array $data)
    {
        $result = $this->saveFormData($stocktakingId, $data, StocktakingInterface::STATUS_PREPARING);
        if (!$result['status']) {
            return [
                'status' => false,
                'message' => __("The stock-taking can not back to prepare.")
            ];
        }
        return [
            'status' => true,
            'message' => __("The stock-taking has been ready to prepare.")
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function completeCounting(int $stocktakingId, array $data)
    {
        $result = $this->saveFormData($stocktakingId, $data, StocktakingInterface::STATUS_VERIFYING);
        if (!$result['status']) {
            return [
                'status' => false,
                'message' => __("The stock-taking can not start verifying.")
            ];
        }
        return [
            'status' => true,
            'message' => __("The stock-taking has been ready to verify.")
        ];
    }

    /**
     * @inheritDoc
     */
    public function complete(int $stocktakingId, bool $createAdjustStock = false)
    {
        try {
            /** @var StocktakingInterface $stocktaking */
            $stocktaking = $this->load($stocktakingId);
            if (!$stocktaking->getId()) {
                return [
                    'status' => false,
                    'message' => __("The stock-taking can not be completed.")
                ];
            }
            $stocktaking->setStatus(StocktakingInterface::STATUS_COMPLETED);
            
            if ($stocktaking->getStocktakingType() == StocktakingInterface::STOCKTAKING_TYPE_FULL) {
                $this->stocktakingManagement->addUncountedProductToStocktaking($stocktakingId);
            }

            if ($createAdjustStock) {
                $result = $this->stocktakingManagement->createAdjustStock($stocktaking);
                if (!$result) {
                    return [
                        'status' => false,
                        'message' => __("The stock-taking can not be completed.")
                    ];
                }
            }

            $this->stocktakingResource->moveToArchive($stocktaking);
            return [
                'status' => true,
                'message' => __("The stock-taking has been completed.")
            ];
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            return [
                'status' => false,
                'message' => __("The stock-taking can not be completed.")
            ];
        }
    }
}
