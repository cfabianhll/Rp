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



namespace Mirasvit\Rma\Model\Config\Source\Rma\Grid;

use Mirasvit\Rma\Api\Config\BackendConfigInterface as Config;

class Columns implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface
     */
    private $rmaField;
    /**
     * @var \Magento\Framework\Model\Context
     */
    private $context;

    /**
     * Columns constructor.
     * @param \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $rmaField
     * @param \Magento\Framework\Model\Context $context
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $rmaField,
        \Magento\Framework\Model\Context $context
    ) {
        $this->rmaField = $rmaField;
        $this->context  = $context;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $options = [
            Config::RMA_GRID_COLUMNS_INCREMENT_ID       => __('RMA #'),
            Config::RMA_GRID_COLUMNS_ORDER_INCREMENT_ID => __('Order #'),
            Config::RMA_GRID_COLUMNS_CUSTOMER_EMAIL     => __('Customer Email'),
            Config::RMA_GRID_COLUMNS_CUSTOMER_NAME      => __('Customer Name'),
            Config::RMA_GRID_COLUMNS_USER_ID            => __('Owner'),
            Config::RMA_GRID_COLUMNS_LAST_REPLY_NAME    => __('Last Replier'),
            Config::RMA_GRID_COLUMNS_STATUS_ID          => __('Status'),
            Config::RMA_GRID_COLUMNS_STORE_ID           => __('Store'),
            Config::RMA_GRID_COLUMNS_CREATED_AT         => __('Created At'),
            Config::RMA_GRID_COLUMNS_UPDATED_AT         => __('Last Activity'),
            Config::RMA_GRID_COLUMNS_ACTION             => __('View link'),
            Config::RMA_GRID_COLUMNS_ITEMS              => __('Items'),
        ];
        foreach ($this->rmaField->getStaffCollection() as $field) {
            $options[$field->getCode()] = $field->getName();
        }

        return $options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->toArray() as $k => $v) {
            $result[] = ['value' => $k, 'label' => $v];
        }

        return $result;
    }

    /************************/
}
