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



namespace Mirasvit\Rma\Model\Rule\Condition;

class Custom extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Rule\Model\Condition\Context
     */
    private $context;

    /**
     * Custom constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->resource = $resource;
        $this->context  = $context;
        $this->registry = $registry;

        parent::__construct($context, $data);
    }


    /**
     * {@inheritdoc}
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'clicks_7'  => __('Last 7-days Clicks'),
            'orders_7'  => __('Last 7-days Orders'),
            'revenue_7' => __('Last 7-days Revenue'),
            'cr_7'      => __('Last 7-days Conversation Rate (%)'),
        ];

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    /**
     * @param string $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        $attribute = $this->getAttribute();

        $arr = explode('_', $attribute);
        $type = $arr[0];
        $period = $arr[1];

        $date = new \Zend_Date();
        $date->sub($period * 24 * 60 * 60);

        $resource = $this->resource;
        $connection = $resource->getConnection('core_read');

        switch ($type) {
            case 'clicks':
                $expr = new \Zend_Db_Expr('SUM(clicks)');
                break;

            case 'orders':
                $expr = new \Zend_Db_Expr('SUM(orders)');
                break;

            case 'revenue':
                $expr = new \Zend_Db_Expr('SUM(revenue)');
                break;

            case 'cr':
                $expr = new \Zend_Db_Expr('SUM(orders) / SUM(clicks) * 100');
                break;
        }

        $select = $connection->select();
        $select->from(['ta' => $resource->getTableName('mst_rma_performance_aggregated')], [$expr])
            ->where('ta.product_id = e.entity_id')
            ->where('ta.period >= ?', $date->toString('YYYY-MM-dd'));

        $select = $productCollection->getSelect()->columns([$attribute => $select]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInputType()
    {
        return 'string';
    }

    /**
     * {@inheritdoc}
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        $attrCode = $this->getAttribute();
        $value = $object->getData($attrCode);

        return $this->validateAttribute($value);
    }

    /**
     * @return string
     */
    public function getJsFormObject()
    {
        return 'rule_conditions_fieldset';
    }
}
