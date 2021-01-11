<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Api\Db\QueryProcessorInterface;
use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;

/**
 * Class Stocktaking
 *
 * Used for stocktaking resource model
 */
class Stocktaking extends AbstractDb
{
    /**
     * Main table primary key field name
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @var QueryProcessorInterface
     */
    protected $queryProcessor;

    /**
     * Resource Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ms_stocktaking', StocktakingInterface::ID);
    }

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param QueryProcessorInterface $queryProcessor
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        QueryProcessorInterface $queryProcessor,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->queryProcessor = $queryProcessor;
    }

    /**
     * Move To Archive
     *
     * @param StocktakingInterface $stocktaking
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function moveToArchive(StocktakingInterface $stocktaking)
    {
        $this->queryProcessor->start('moveStocktakingToArchive');
        // Move stocktaking to stocktaking archive table
        $this->queryProcessor->addQuery(
            [
                'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
                'values' => $stocktaking->getData(),
                'table' => $this->getTable('ms_stocktaking_archive')
            ],
            'moveStocktakingToArchive'
        );

        // Move stocktaking items to stocktaking archive item table
        $connection = $this->getConnection();
        $stocktakingItemSelect = $connection->select()
            ->from($this->getTable('ms_stocktaking_item'))
            ->where(StocktakingItemInterface::STOCKTAKING_ID . " = ?", $stocktaking->getId());

        $stocktakingItem = $connection->fetchAll($stocktakingItemSelect);
        if (count($stocktakingItem)) {
            $this->queryProcessor->addQuery(
                [
                    'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
                    'values' => $stocktakingItem,
                    'table' => $this->getTable('ms_stocktaking_archive_item')
                ],
                'moveStocktakingToArchive'
            );
        }

        // Delete stocktaking and it's items
        $this->queryProcessor->addQuery(
            [
                'type' => QueryProcessorInterface::QUERY_TYPE_DELETE,
                'condition' => [StocktakingInterface::ID . ' = ?' => $stocktaking->getId()],
                'table' => $this->getMainTable()
            ],
            'moveStocktakingToArchive'
        );
        $this->queryProcessor->process('moveStocktakingToArchive');
    }
}
