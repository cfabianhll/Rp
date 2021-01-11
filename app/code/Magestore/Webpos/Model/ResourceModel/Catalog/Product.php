<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\ResourceModel\Catalog;

/**
 * ResourceModel Product
 */
class Product extends \Magento\Catalog\Model\ResourceModel\Product
{
    /**
     * Get Product Static Fields Information
     *
     * @param string[] $fields
     * @param array $productIds
     * @return array
     */
    public function getProductStaticFieldsInformation($fields = [], $productIds = [])
    {
        $select = $this->getConnection()->select()
            ->from([
                $this->getTable('catalog_product_entity'),
                []
            ]);
        if (count($fields)) {
            $select->columns($fields);
        } else {
            $select->columns('*');
        }
        if (count($productIds)) {
            $select->where('entity_id IN (?)', $productIds);
        }
        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Update updated time of products
     *
     * @param string $tableName
     * @param string $updatedAt
     * @param array $productIds
     */
    public function updateUpdatedTimeOfProducts($tableName, $updatedAt, $productIds)
    {
        $connection = $this->getConnection();
        if ($connection->isTableExists($tableName)) {
            $this->getConnection()->update(
                $tableName,
                ['updated_at' => $updatedAt],
                $connection->quoteInto("entity_id IN (?)", $productIds)
            );
        }
    }
}
