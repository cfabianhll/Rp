<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Ui\DataProvider\Stocktaking\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\CollectionFactory;
use Magestore\Stocktaking\Service\Adminhtml\Stocktaking\Edit\GetCurrentStocktakingService;
use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem\Collection
    as StocktakingItemCollection;
use Magento\Framework\ObjectManagerInterface;
use Magestore\Stocktaking\Model\Source\Adminhtml\StocktakingType;
use Magestore\Stocktaking\Model\Source\Adminhtml\Status as StocktakingStatus;

/**
 * Undocumented class
 */
class Stocktaking extends AbstractDataProvider
{
    /**
     * @var PoolInterface
     */
    protected $pool;
    /**
     * @var GetCurrentStocktakingService
     */
    protected $getCurrentStocktakingService;

    /**
     * @var array
     */
    protected $loadedData = [];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Stocktaking constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param PoolInterface $pool
     * @param GetCurrentStocktakingService $getCurrentStocktakingService
     * @param ObjectManagerInterface $objectManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        PoolInterface $pool,
        GetCurrentStocktakingService $getCurrentStocktakingService,
        ObjectManagerInterface $objectManager,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->pool = $pool;
        $this->getCurrentStocktakingService = $getCurrentStocktakingService;
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        if ($this->loadedData) {
            return $this->loadedData;
        }

        $stocktaking = $this->getCurrentStocktakingService->getCurrentStocktaking();
        if ($stocktaking && $stocktaking->getId()) {
            $stocktakingData = $stocktaking->getData();

            $stocktakingData['is_preparing'] =
                $stocktakingData[StocktakingInterface::STATUS] == StocktakingInterface::STATUS_PREPARING;
            $stocktakingData['is_counting'] =
                $stocktakingData[StocktakingInterface::STATUS] == StocktakingInterface::STATUS_COUNTING;

            $stocktakingTypes = $this->objectManager
                ->get(StocktakingType::class)
                ->toOptionHash();
            $stocktakingData['stocktaking_type_info'] = $stocktakingTypes[
                $stocktakingData[StocktakingInterface::STOCKTAKING_TYPE]
            ];
            $stocktakingData['created_by_info'] = $stocktakingData[StocktakingInterface::CREATED_BY_FIRST_NAME]
                . ' ' . $stocktakingData[StocktakingInterface::CREATED_BY_LAST_NAME];
            $stocktakingData['assign_user_info'] = $stocktakingData[StocktakingInterface::ASSIGN_USER_FIRST_NAME]
                . ' ' . $stocktakingData[StocktakingInterface::ASSIGN_USER_LAST_NAME];
            
            $statusLabels = $this->objectManager
                ->get(StocktakingStatus::class)
                ->toOptionHash();
            $stocktakingData['status_info'] = $statusLabels[$stocktakingData[StocktakingInterface::STATUS]];
            $this->loadedData[$stocktaking->getId()]['general_information'] = $stocktakingData;

            $products = $this->getProductsListData($stocktaking->getId());
            if (count($products)) {
                foreach ($products as $product) {
                    $dataProduct = [
                        'id' => $product['product_id'],
                        'sku' => $product['product_sku'],
                        'name' => $product['product_name'],
                        'qty_in_source' => (float)$product['qty_in_source'],
                        'counted_qty' => (float)$product['counted_qty'],
                        'difference_reason' => $product['difference_reason']
                    ];
                    $this->loadedData[
                        $stocktaking->getId()
                    ][
                        'product_list'
                    ][
                        'product_dynamic_grid'
                    ][
                        'links'
                    ][
                        'product_list'
                    ][] = $dataProduct;
                }
            }
        }

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->loadedData = $modifier->modifyData($this->loadedData);
        }

        return $this->loadedData;
    }

    /**
     * @inheritDoc
     */
    public function getMeta(): array
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    /**
     * Get Products List Data
     *
     * @param int $stocktakingId
     * @return array
     */
    public function getProductsListData(int $stocktakingId): array
    {
        /** @var StocktakingItemCollection $collection */
        $collection = $this->objectManager->create(StocktakingItemCollection::class);
        $collection->addFieldToFilter(StocktakingItemInterface::STOCKTAKING_ID, $stocktakingId);
        return $collection->getData();
    }
}
