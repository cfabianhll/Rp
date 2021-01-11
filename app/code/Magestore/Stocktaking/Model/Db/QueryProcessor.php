<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\Db;

use Magestore\Stocktaking\Api\Db\QueryProcessorInterface;
use Magestore\Stocktaking\Model\ResourceModel\Db\QueryProcessor as QueryProcessorResource;

/**
 * Sql Query Processor
 */
class QueryProcessor implements QueryProcessorInterface
{

    /**
     * @var array
     */
    private $_queryQueue = [];

    /**
     * @var QueryProcessorResource
     */
    protected $_resource;

    /*
     * @var string
     */
    protected $defaultProcess = 'default';

    /**
     * QueryProcessor constructor
     *
     * @param QueryProcessorResource $resourceQueryProcessor
     */
    public function __construct(
        QueryProcessorResource $resourceQueryProcessor
    ) {
        $this->_resource = $resourceQueryProcessor;
    }

    /**
     * @inheritDoc
     */
    public function addQuery(array $queryData, $process = null)
    {
        $process = $process ? $process : $this->defaultProcess;
        if (isset($this->_queryQueue[$process])) {
            $this->_queryQueue[$process][] = $queryData;
        } else {
            $this->_queryQueue[$process] = [$queryData];
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addQueries(array $queries, $process = null)
    {
        if (!count($queries)) {
            return $this;
        }
        $process = $process ? $process : $this->defaultProcess;
        foreach ($queries as $query) {
            $this->addQuery($query, $process);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getQueryQueue($process = null)
    {
        $process = $process ? $process : $this->defaultProcess;
        return isset($this->_queryQueue[$process]) ? $this->_queryQueue[$process] : [];
    }

    /**
     * @inheritDoc
     */
    public function start($process = null)
    {
        $this->resetQueue($process);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function process($process = null)
    {
        $this->getResource()->processQueries($this->getQueryQueue($process));
        $this->resetQueue($process);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function resetQueue($process = null)
    {
        $process = $process ? $process : $this->defaultProcess;
        $this->_queryQueue[$process] = [];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getResource()
    {
        return $this->_resource;
    }
}
