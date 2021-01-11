<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Ui\DataProvider\Archive\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\CollectionFactory;
use Magestore\Stocktaking\Service\Adminhtml\Archive\View\CurrentStocktakingArchiveService;
use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem\Collection
    as StocktakingItemCollection;
use Magestore\Stocktaking\Helper\Data as StocktakingHelperData;

/**
 * Stocktaking Archive Form Data Provider
 */
class StocktakingArchive extends AbstractDataProvider
{
    /**
     * @var PoolInterface
     */
    protected $pool;
    /**
     * @var CurrentStocktakingArchiveService
     */
    protected $currentStocktakingArchiveService;

    /**
     * @var array
     */
    protected $loadedData = [];

    /**
     * @var StocktakingHelperData
     */
    protected $helper;

    /**
     * Stocktaking constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param PoolInterface $pool
     * @param CurrentStocktakingArchiveService $currentStocktakingArchiveService
     * @param StocktakingHelperData $helper
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        PoolInterface $pool,
        CurrentStocktakingArchiveService $currentStocktakingArchiveService,
        StocktakingHelperData $helper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->pool = $pool;
        $this->currentStocktakingArchiveService = $currentStocktakingArchiveService;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        if ($this->loadedData) {
            return $this->loadedData;
        }

        $stocktaking = $this->currentStocktakingArchiveService->getCurrentStocktakingArchive();
        if ($stocktaking && $stocktaking->getId()) {
            $stocktakingData = $stocktaking->getData();

            $stocktakingTypes = $this->helper->getStocktakingTypes();
            $stocktakingData['stocktaking_type_info'] = $stocktakingTypes[
                $stocktakingData[StocktakingInterface::STOCKTAKING_TYPE]
            ];
            $stocktakingData['created_by_info'] = $stocktakingData[StocktakingInterface::CREATED_BY_FIRST_NAME]
                . ' ' . $stocktakingData[StocktakingInterface::CREATED_BY_LAST_NAME];
            $stocktakingData['assign_user_info'] = $stocktakingData[StocktakingInterface::ASSIGN_USER_FIRST_NAME]
                . ' ' . $stocktakingData[StocktakingInterface::ASSIGN_USER_LAST_NAME];
            
            $statusLabels = $this->helper->getStocktakingStatus();
            $stocktakingData['status_info'] = $statusLabels[$stocktakingData[StocktakingInterface::STATUS]];
            $this->loadedData[$stocktaking->getId()]['general_information'] = $stocktakingData;
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
}
