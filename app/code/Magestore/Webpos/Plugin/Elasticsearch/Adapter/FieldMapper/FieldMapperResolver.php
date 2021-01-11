<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Plugin\Elasticsearch\Adapter\FieldMapper;

use Magento\Elasticsearch\Model\Config;

/**
 * Class FieldMapperResolver
 *
 * Plugin field map resolver to solve problem on magento 2.3.3
 */
class FieldMapperResolver
{
    /**
     * @var \Magestore\Webpos\Model\Adapter\FieldMapper\ProductFieldMapperProxy
     */
    private $productFieldMapper;

    /**
     * FieldMapperResolver constructor.
     *
     * @param \Magestore\Webpos\Model\Adapter\FieldMapper\ProductFieldMapperProxy $productFieldMapper
     */
    public function __construct(
        \Magestore\Webpos\Model\Adapter\FieldMapper\ProductFieldMapperProxy $productFieldMapper
    ) {
        $this->productFieldMapper = $productFieldMapper;
    }

    /**
     * Check if entity is webpos search, call webpos product field directly
     *
     * Resolve the problem can not reindex webpos search in magento 2.3.3
     *
     * @param \Magento\Elasticsearch\Model\Adapter\FieldMapper\FieldMapperResolver $subject
     * @param \Closure $proceed
     * @param string $attributeCode
     * @param array $context
     * @return mixed|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetFieldName(
        \Magento\Elasticsearch\Model\Adapter\FieldMapper\FieldMapperResolver $subject,
        \Closure $proceed,
        $attributeCode,
        $context = []
    ) {
        $entityType = isset($context['entityType']) ? $context['entityType'] : Config::ELASTICSEARCH_TYPE_DEFAULT;
        if ($entityType == 'webpos_productsearch_fulltext') {
            return $this->productFieldMapper->getFieldName($attributeCode, $context);
        } else {
            return $proceed($attributeCode, $context);
        }
    }

    /**
     * Check if entity is webpos search, call webpos product field directly
     *
     * Resolve the problem can not reindex webpos search in magento 2.3.3
     *
     * @param \Magento\Elasticsearch\Model\Adapter\FieldMapper\FieldMapperResolver $subject
     * @param \Closure $proceed
     * @param array $context
     * @return array|mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetAllAttributesTypes(
        \Magento\Elasticsearch\Model\Adapter\FieldMapper\FieldMapperResolver $subject,
        \Closure $proceed,
        $context = []
    ) {
        $entityType = isset($context['entityType']) ? $context['entityType'] : Config::ELASTICSEARCH_TYPE_DEFAULT;
        if ($entityType == 'webpos_productsearch_fulltext') {
            return $this->productFieldMapper->getAllAttributesTypes($context);
        } else {
            return $proceed($context);
        }
    }
}
