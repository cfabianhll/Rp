<?php
/**
 *  Copyright © 2020 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Column source product barcode
 */
class SourceProductBarcode extends Column
{
    /**
     * Add scroll down to field
     *
     * @param string $html
     * @return string
     */
    public function addScrollToField(string $html)
    {
        return '<div style="max-height: 85px;overflow-y: auto;">
            '.$html.'
        </div>';
    }

    /**
     * Modify data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {

                $poArray = [];
                if ($item[$fieldName]) {
                    $item[$fieldName] = explode(',', $item[$fieldName]);
                    foreach ($item[$fieldName] as $barcode) {
                        $poArray[] = $barcode;
                    }
                    $item[$fieldName] = $this->addScrollToField(implode('</br>', $poArray));
                }
            }
        }
        return $dataSource;
    }
}
