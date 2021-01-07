<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column\Stock;

use Amasty\MultiInventory\Ui\Component\Listing\Column\AbstractColumn;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Warehouse
 */
class Warehouse extends AbstractColumn
{
    /**
     * @param array $item
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareItem(array $item)
    {
        $warehouseId = $this->repository->getByCode($this->getName())->getId();
        $data = current($this->getProductStockData($item['entity_id'], $warehouseId));
        $result = [
            'qty' => (int)$data['qty'],
            'available' => (int)$data['available_qty'],
            'ship' => (int)$data['ship_qty'],
            'room' => $data['room_shelf'],
            'stock_status' => (int)$data['stock_status']
//            'backorders' => $data['backorders']
        ];

        return $this->jsonEncoder->encode($result);
    }
}
