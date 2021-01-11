<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\ResourceModel\Db;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magestore\Stocktaking\Api\Db\QueryProcessorInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Resource model Query Processor
 */
class QueryProcessor extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct() //phpcs:ignore
    {
        /* do nothing */
    }

    /**
     * Process queries
     *
     * @param array $queries
     * @return $this
     * @throws LocalizedException
     */
    public function processQueries(array $queries)
    {
        if (!count($queries)) {
            return $this;
        }
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            foreach ($queries as $queryData) {
                switch ($queryData['type']) {
                    case QueryProcessorInterface::QUERY_TYPE_INSERT:
                        $connection->insertMultiple($queryData['table'], $queryData['values']);
                        break;
                    case QueryProcessorInterface::QUERY_TYPE_UPDATE:
                        $connection->update($queryData['table'], $queryData['values'], $queryData['condition']);
                        break;
                    case QueryProcessorInterface::QUERY_TYPE_DELETE:
                        $connection->delete($queryData['table'], $queryData['condition']);
                        break;
                }
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new LocalizedException(__($e->getMessage()), $e);
        }
        return $this;
    }
}
