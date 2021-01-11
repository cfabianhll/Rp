<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Plugin\Customer\Ui\Component\Listing;

use Magento\Customer\Api\Data\AttributeMetadataInterface as AttributeMetadata;

/**
 * Class Columns
 *
 * Plugin Columns data to change data type
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class Columns
{
    /**
     * Used to plugin select data type
     *
     * @param \Magento\Customer\Ui\Component\Listing\Columns $columns
     * @param array $result
     * @param array $attributeData
     * @param string $newAttributeCode
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateColumn(
        \Magento\Customer\Ui\Component\Listing\Columns $columns,
        $result,
        array $attributeData,
        $newAttributeCode
    ) {
        if ($attributeData[AttributeMetadata::ATTRIBUTE_CODE] == 'created_location_id') {
            $component = $columns->getComponent('created_location_id');
            $component->setData(
                'config',
                array_merge(
                    $component->getData('config'),
                    ['dataType' => 'select']
                )
            );
        }
    }
}
