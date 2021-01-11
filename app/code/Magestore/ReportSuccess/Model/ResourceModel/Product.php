<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\ReportSuccess\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Config\Model\Config\Backend\Admin\Custom;

/**
 * ResourceModel Product
 */
class Product extends \Magento\Catalog\Model\ResourceModel\Product
{
    /**
     * Get Product Static Fields Information
     *
     * @return void
     */
    public function initMacValue()
    {
        $connection = $this->getConnection();
        $macAttributeId = ObjectManager::getInstance()->create(Attribute::class)
            ->getIdByCode(ProductAttributeInterface::ENTITY_TYPE_CODE, 'mac');
        $costAttributeId = ObjectManager::getInstance()->create(Attribute::class)
            ->getIdByCode(ProductAttributeInterface::ENTITY_TYPE_CODE, 'cost');

        $macValueSelect = $connection->select()
            ->from(
                $this->getTable('catalog_product_entity_decimal'),
                [$this->getProductLinkedField()]
            )->where('attribute_id = ?', $macAttributeId)
            ->where('store_id = ?', Custom::CONFIG_SCOPE_ID);
        $existedMacProducts = $connection->fetchCol($macValueSelect);
        $costValueSelect = $connection->select()
            ->from($this->getTable('catalog_product_entity_decimal'))
            ->where('attribute_id = ?', $costAttributeId)
            ->where('store_id = ?', Custom::CONFIG_SCOPE_ID);
        if (count($existedMacProducts)) {
            $costValueSelect->where(
                $this->getProductLinkedField() . ' NOT IN (?)',
                array_values($existedMacProducts)
            );
        }

        $insertSelect = $connection->select()
            ->from(
                $costValueSelect,
                [
                    'attribute_id' => new \Zend_Db_Expr($macAttributeId),
                    'store_id',
                    'value',
                    $this->getProductLinkedField()
                ]
            );

        $connection->query(
            $insertSelect->insertFromSelect(
                $this->getTable('catalog_product_entity_decimal'),
                ['attribute_id', 'store_id', 'value', $this->getProductLinkedField()]
            )
        );
    }

    /**
     * Get linked field of product
     *
     * @return string
     */
    public function getProductLinkedField()
    {
        /** @var MetadataPool $metapool */
        $metaPool = ObjectManager::getInstance()->create(MetadataPool::class);
        $metadata = $metaPool->getMetadata(ProductInterface::class);
        return $metadata->getLinkField();
    }
}
