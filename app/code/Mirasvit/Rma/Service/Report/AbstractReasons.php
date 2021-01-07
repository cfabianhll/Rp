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


namespace Mirasvit\Rma\Service\Report;

use Mirasvit\Rma\Helper\Locale;
use Magento\Framework\App\ResourceConnection;
use Mirasvit\ReportApi\Config\Loader\Map;
use Mirasvit\ReportApi\Api\SchemaInterface;

abstract class AbstractReasons
{
    /**
     * @var SchemaInterface
     */
    private $provider;
    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @var Map
     */
    private $mapper;
    /**
     * @var Locale
     */
    private $localeData;

    /**
     * AbstractReasons constructor.
     * @param SchemaInterface $provider
     * @param Locale $localeData
     * @param Map $mapper
     * @param ResourceConnection $resource
     */
    public function __construct(
        SchemaInterface $provider,
        Locale $localeData,
        Map $mapper,
        ResourceConnection $resource
    ) {
        $this->localeData = $localeData;
        $this->mapper = $mapper;
        $this->resource = $resource;
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    abstract public function getItemTable();

    /**
     * @return string
     */
    abstract public function getReasonsTable();

    /**
     * @return string
     */
    abstract public function getItemReasonsField();

    /**
     * @return string
     */
    abstract public function getReasonsField();

    /**
     * @param string $prefix
     * @return void
     */
    public function add($prefix)
    {
        $results = $this->resource->getConnection()->query($this->getReasonsSql());
        foreach ($results->fetchAll() as $rule) {
            $object = new \Magento\Framework\DataObject($rule);
            $name = $this->localeData->getLocaleValue($object, 'name');
            $this->mapper->initColumn([
                'name'   => $name . '_' . $rule['id'],
                'fields' => $this->getItemReasonsField(),
                'expr'   => 'SUM(IF(' . $this->getItemReasonsField() . ' = ' . $rule['id'] . ', 1, 0))',
                'label'  => $prefix . ': ' . $name,
                'type'   => 'qty',
                'table'  => $this->provider->getTable($this->getItemTable())
            ]);
        }
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    protected function getReasonsSql()
    {
        return $this->resource->getConnection()
            ->select()
            ->from(
                [$this->resource->getTableName($this->getReasonsTable())],
                ['name', 'id' => $this->getReasonsField()]
            );
    }
}