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


namespace Mirasvit\Rma\Model\UI\Rma;

use Mirasvit\Rma\Api\Config\OfflineOrderConfigInterface;
use Mirasvit\Rma\Api\Service\Field\FieldManagementInterface;
use Mirasvit\Rma\Api\Config\FieldConfigInterface as FieldConfig;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;

class RmaGridDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var FieldManagementInterface
     */
    private $fieldManagement;
    /**
     * @var OfflineOrderConfigInterface
     */
    private $offlineConfig;

    /**
     * RmaGridDataProvider constructor.
     * @param OfflineOrderConfigInterface $offlineConfig
     * @param FieldManagementInterface $fieldManagement
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        OfflineOrderConfigInterface $offlineConfig,
        FieldManagementInterface $fieldManagement,
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder,
            $request, $filterBuilder, $meta, $data);

        $this->fieldManagement = $fieldManagement;
        $this->offlineConfig = $offlineConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == 'status_id') {
            $filter->setField('main_table.status_id');
        }
        if ($filter->getField() == 'created_at') {
            $filter->setField('main_table.created_at');
        }
        if ($filter->getField() == 'updated_at') {
            $filter->setField('main_table.updated_at');
        }
        if ($filter->getField() == 'customer_name') {
            $filter->setField('main_table.name');
        }
        if ($filter->getField() == 'increment_id') {
            $filter->setField('main_table.increment_id');
        }
        if ($filter->getField() == 'order_id') {
            $filter->setField('order.increment_id');
        }

        parent::addFilter($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function addOrder($field, $direction)
    {
        if ($field == 'customer_name') {
            $field = 'name';
        }

        parent::addOrder($field, $direction);
    }

    /**
     * Returns Search result
     *
     * @return \Mirasvit\Rma\Model\ResourceModel\Rma\Collection
     */
    public function getSearchResult()
    {
        $groups     = [];
        $rmaFilter = $fieldValue = $fieldExchangeOrderValue = $fieldReplacementOrderValue = $orderValue = '';
        /** @var \Magento\Framework\Api\Search\FilterGroup $group */
        foreach ($this->getSearchCriteria()->getFilterGroups() as $group) {
            if (empty($group->getFilters())) {
                continue;
            }
            $isAdded = $isExchangeAdded = false;
            $filters = [];
            /** @var \Magento\Framework\Api\Filter $filter */
            foreach ($group->getFilters() as $filter) {
                if ($filter->getField() == 'rma_id' && !$isAdded) {
                    $rmaFilter = $filter->getValue();
                    continue;
                }
                if ($filter->getField() == 'main_table.name' && !$isAdded) {
                    $fieldValue = $filter->getValue();
                    continue;
                }
                if ($filter->getField() == 'exchange_order_ids') {
                    $fieldExchangeOrderValue = $filter->getValue();
                    continue;
                }
                if ($filter->getField() == 'order.increment_id') {
                    $orderValue = $filter->getValue();
                    continue;
                }
                if ($filter->getField() == 'replacement_order_ids') {
                    $fieldReplacementOrderValue = $filter->getValue();
                    continue;
                }
                $customFields = $this->fieldManagement->getStaffCollection();
                foreach ($customFields as $field) {
                    if ($filter->getField() == $field->getCode() && !$isAdded &&
                        $field->getType() == FieldConfig::FIELD_TYPE_CHECKBOX && $filter->getValue() == [0]
                    ) {
                        $filter->setConditionType('null');
                        break;
                    }
                }

                $filters[] = $filter;
            }
            $group->setFilters($filters);
            $groups[] = $group;
        }
        $this->getSearchCriteria()->setFilterGroups($groups);
        /** @var \Mirasvit\Rma\Model\ResourceModel\Rma\Grid\Collection $collection */
        $collection = $this->reporting->search($this->getSearchCriteria());
        if ($fieldValue) {
            $collection->getSelect()->where(
                '(CONCAT(main_table.firstname, " ", main_table.lastname) like ?) OR
                    (SELECT CONCAT(customer_table.firstname, " ", customer_table.lastname)
                    FROM ' . $collection->getTable('customer_entity') . ' AS `customer_table`
                    WHERE customer_table.entity_id = main_table.customer_id) like ? ',
                $fieldValue, $fieldValue
            );
        }
        if ($rmaFilter) {
            // IN for mass actions
            $collection->getSelect()->where('main_table.rma_id IN (?)', $rmaFilter );
        }
        if ($fieldExchangeOrderValue) {
            $collection->getSelect()->where(
                'EXISTS(
                    SELECT *
                    FROM ' . $collection->getTable('mst_rma_rma_order') . ' AS `rma_order_table`
                	INNER JOIN ' . $collection->getTable('sales_order') . ' sorder
                	    ON sorder.entity_id = rma_order_table.re_exchange_order_id AND sorder.increment_id = ?
                    WHERE rma_order_table.re_rma_id = main_table.rma_id
                )',
                trim($fieldExchangeOrderValue, '%')
            );
        }
        if ($fieldReplacementOrderValue) {
            $collection->getSelect()->where(
                'EXISTS(
                    SELECT *
                    FROM ' . $collection->getTable('mst_rma_rma_replacement_order') . ' AS `rma_replacement_order_table`
                	INNER JOIN ' . $collection->getTable('sales_order') . ' sorder1
                	    ON sorder1.entity_id = rma_replacement_order_table.replacement_order_id
                	        AND sorder1.increment_id = ?
                    WHERE rma_replacement_order_table.rma_id = main_table.rma_id
                )',
                trim($fieldReplacementOrderValue, '%')
            );
        }
        if ($orderValue) {
            $collection->getSelect()->where(
                '
                    EXISTS(
                        SELECT *
                        FROM ' . $collection->getTable('mst_rma_item') . ' as item 
                        LEFT JOIN
                            ' . $collection->getTable('sales_order_item') . ' as order_item ON order_item.item_id = item.order_item_id
                        LEFT JOIN
                            ' . $collection->getTable('sales_order') . ' as `order` ON `order`.entity_id = order_item.order_id
                        WHERE (`order`.`increment_id` LIKE ?) AND item.rma_id = main_table.rma_id
                    ) OR 
                    EXISTS(
                        SELECT *
                        FROM ' . $collection->getTable('mst_rma_offline_item') . ' as item
                        LEFT JOIN
                            ' . $collection->getTable('mst_rma_offline_order') . ' as `order` ON `order`.offline_order_id = item.offline_order_id
                        WHERE (`order`.`receipt_number` LIKE ?) AND item.rma_id = main_table.rma_id
                    )
                ',
                $orderValue
            );
        }

        return $collection;
    }
}
