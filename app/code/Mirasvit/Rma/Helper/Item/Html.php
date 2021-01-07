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



namespace Mirasvit\Rma\Helper\Item;

/**
 * Helper which creates different html code
 */
class Html extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface
     */
    private $itemManagement;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Html constructor.
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);

        $this->itemManagement = $itemManagement;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getItemSku($item)
    {
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        $orderItem = $this->itemManagement->getOrderItem($item);

        return $orderItem->getSku();
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getItemOrderIncrement($item)
    {
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        $orderItem = $this->itemManagement->getOrderItem($item);

        $order = $this->orderRepository->get($orderItem->getOrderId());
        return $order->getIncrementId();
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getItemLabel($item)
    {
        $orderItem = $this->itemManagement->getOrderItem($item);
        $name = $orderItem->getName();
        $options = $this->getItemOptions($orderItem);
        if (count($options)) {
            $name .= ' (';
            foreach ($options as $option) {
                $name .= $option['label'] . ': ' . $option['value'] . ', ';
            }
            $name = substr($name, 0, -2); //remove last ,
            $name .= ')';
        }

        return $name;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface   $item
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getItemPrice($item, $order)
    {
        $orderItem = $this->itemManagement->getOrderItem($item);

        $precision = 2;
        if ($order->isCurrencyDifferent()) {
            $res = '';
            $res .= $order->formatBasePricePrecision($orderItem->getBasePriceInclTax(), $precision);
            $res .= '<br>';
            $res .= $order->formatPricePrecision($orderItem->getPriceInclTax(), $precision, true);
        } else {
            $res = $order->formatPricePrecision($orderItem->getPriceInclTax(), $precision);
        }

        return $res;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return array
     */
    protected function getItemOptions($item)
    {
        $result = [];
        if ($options = $item->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }

}