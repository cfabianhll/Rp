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



namespace Mirasvit\Rma\Helper\Rma;

class Calculate extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface
     */
    private $itemProductManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface
     */
    private $itemManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface
     */
    private $rmaSearchManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface
     */
    private $rmaOrder;

    /**
     * Calculate constructor.
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface $rmaOrder
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface $itemProductManagement
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface $rmaOrder,
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface $itemProductManagement
    ) {
        $this->rmaSearchManagement   = $rmaSearchManagement;
        $this->rmaOrder              = $rmaOrder;
        $this->itemManagement        = $itemManagement;
        $this->itemProductManagement = $itemProductManagement;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return array
     */
    public function calculateExchangeAmounts(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        $result = [
            'oldAmount' => 0,
            'newAmount' => 0
        ];
        $order = $this->rmaOrder->getOrder($rma);
        if (!$order || $order->getIsOffline()) {
            return $result;
        }
        foreach ($this->rmaSearchManagement->getItems($rma) as $item) {
            if ($this->itemManagement->isExchange($item) || $this->itemManagement->isCredit($item)) {
                $result['oldAmount'] += $this->itemManagement->getOrderItem($item)
                        ->getPriceInclTax() * $item->getQtyRequested();
            }
            if ($this->itemManagement->isExchange($item)) {
                $result['newAmount'] += $this->itemProductManagement->getExchangeProduct($item)
                        ->getFinalPrice() * $item->getQtyRequested();
            }
        }

        return $result;
    }
}