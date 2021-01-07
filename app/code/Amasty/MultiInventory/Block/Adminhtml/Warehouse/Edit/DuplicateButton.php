<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Warehouse\Edit;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class ResetButton
 */
class DuplicateButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @throws LocalizedException
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getWarehouse()) {
            if (!$this->getWarehouse()->isGeneral()) {
                $data = [
                    'label' => __('Duplicate'),
                    'class' => 'delete',
                    'on_click' => 'deleteConfirm("' . __('Are you sure you want to do this?')
                        . '", "' . $this->getDuplicateUrl() . '")',
                    'sort_order' => 20,
                ];
            }
        }

        return $data;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getDuplicateUrl()
    {
        return $this->getUrl('*/*/duplicate', ['warehouse_id' => $this->getWarehouse()->getId()]);
    }
}
