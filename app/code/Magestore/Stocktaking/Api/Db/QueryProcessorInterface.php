<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Api\Db;

use Magestore\Stocktaking\Model\ResourceModel\Db\QueryProcessor as QueryProcessorResource;

interface QueryProcessorInterface
{

    /**
     * Define query types
     */
    const QUERY_TYPE_UPDATE = 'update';
    const QUERY_TYPE_INSERT = 'insert';
    const QUERY_TYPE_DELETE = 'delete';

    /**
     * Add query to processor
     *
     * @param array $queryData
     * @param string|null $process
     * @return $this
     */
    public function addQuery(array $queryData, $process = null);

    /**
     * Add queries to processor
     *
     * @param array $queryData
     * @param string|null $process
     * @return $this
     */
    public function addQueries(array $queryData, $process = null);

    /**
     * Get queries in queue
     *
     * @param string|null $process
     * @return array
     */
    public function getQueryQueue($process = null);

    /**
     * Process queries in queue
     *
     * @param string $process
     * @return $this
     */
    public function process($process = null);

    /**
     * Remove queries in the queue
     *
     * @param string $process
     * @return $this
     */
    public function resetQueue($process = null);

    /**
     * Start processing
     *
     * @param string $process
     * @return $this
     */
    public function start($process = null);

    /**
     * Get Resource
     *
     * @return QueryProcessorResource
     */
    public function getResource();
}
