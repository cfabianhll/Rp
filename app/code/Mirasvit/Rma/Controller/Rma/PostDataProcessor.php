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



namespace Mirasvit\Rma\Controller\Rma;

class PostDataProcessor
{
    /**
     * @var array
     */
    private $errorMessages = [];

    /**
     * @var \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface
     */
    private $offlineOrderRepository;
    /**
     * @var \Mirasvit\Rma\Service\Order\OrderAbstractFactory
     */
    private $orderAbstractFactory;
    /**
     * @var \Magento\Framework\View\Model\Layout\Update\ValidatorFactory
     */
    private $validatorFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    private $dateFilter;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface
     */
    private $itemQuantityManagement;
    /**
     * @var \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface
     */
    private $rmaPolicyConfig;
    /**
     * @var \Mirasvit\Rma\Api\Config\RmaRequirementConfigInterface
     */
    private $reasonsConfig;
    /**
     * @var \Mirasvit\Rma\Service\Item\ItemListBuilder
     */
    private $itemListBuilder;

    /**
     * PostDataProcessor constructor.
     * @param \Mirasvit\Rma\Service\Item\ItemListBuilder $itemListBuilder
     * @param \Mirasvit\Rma\Api\Config\RmaRequirementConfigInterface $reasonsConfig
     * @param \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface $rmaPolicyConfig
     * @param \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface $offlineOrderRepository
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface $itemQuantityManagement
     * @param \Mirasvit\Rma\Service\Order\OrderAbstractFactory $orderAbstractFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory
     */
    public function __construct(
        \Mirasvit\Rma\Service\Item\ItemListBuilder $itemListBuilder,
        \Mirasvit\Rma\Api\Config\RmaRequirementConfigInterface $reasonsConfig,
        \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface $rmaPolicyConfig,
        \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface $offlineOrderRepository,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface $itemQuantityManagement,
        \Mirasvit\Rma\Service\Order\OrderAbstractFactory $orderAbstractFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory
    ) {
        $this->itemListBuilder        = $itemListBuilder;
        $this->reasonsConfig          = $reasonsConfig;
        $this->rmaPolicyConfig        = $rmaPolicyConfig;
        $this->itemQuantityManagement = $itemQuantityManagement;
        $this->productRepository      = $productRepository;
        $this->dateFilter             = $dateFilter;
        $this->validatorFactory       = $validatorFactory;
        $this->orderAbstractFactory   = $orderAbstractFactory;
        $this->offlineOrderRepository = $offlineOrderRepository;
    }

    /**
     * Filtering posted data. Return only RMA data.
     *
     * @param array $data
     *
     * @return array
     */
    public function filterRmaData($data)
    {
        $newData = $data;
        unset($newData['items']);

        return $newData;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function createOfflineOrder($data)
    {
        $order = $this->orderAbstractFactory->get($data);
        if ($order->getIsOffline()) {
            foreach ($data['receipt_number'] as $orderNumber => $receiptNumber) {
                $orderData                    = [];
                $orderData ['is_offline']     = $data['is_offline'];
                $orderData ['customer_id']    = $data['customer_id'];
                $orderData ['store_id']       = $data['store_id'];
                $orderData ['receipt_number'] = $receiptNumber;
                $order->setData($orderData);
                $this->offlineOrderRepository->save($order);
                $data['orders'][$orderNumber]['order_id'] = $order->getId();
                $data['order_id']                         = $order->getId();//we need this to generate increment for new RMA
                foreach ($data['items'] as $k => $item) {
                    if ($item['order_id'] == $orderNumber) {
                        $data['items'][$k]['offline_order_id'] = $order->getId();
                    }
                }
            }
            $data['order_id'] = $order->getId();
        }

        return $data;
    }

    /**
     * Filtering posted data. Return only RMA items.
     *
     * @param array $data
     *
     * @return array
     */
    public function filterRmaItems($data)
    {
        if (isset($data['order_ids'])) {
            foreach ((array)$data['order_ids'] as $orderId) {
                $params['order_id']        = $orderId;
                $order                     = $this->orderAbstractFactory->get($params);
                $itemCollections[$orderId] = $this->itemListBuilder->getList($order);
            }
        }

        $items = $data['items'];
        foreach ($items as $k => $item) {
            $item                  = $this->filterConditions($item);
            $item['order_item_id'] = $k;

            if (empty($item['is_offline']) || $item['is_offline'] == 0) {
                $orderItem = $itemCollections[$item['order_id']][$k];
                if ($orderItem) {
                    $item['product_sku'] = $this->getProductSku($orderItem);
                }
            }

            $items[$k] = $item;
        }

        return $items;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     *
     * @return string
     */
    private function getProductSku($orderItem)
    {
        $options = $orderItem->getProductOptions();
        if (!empty($options['simple_sku'])) {
            $productSku = $this->productRepository->get($options['simple_sku'])->getsku();
        } else {
            $productSku = $orderItem->getProductSku() ? : $orderItem->getSku();
        }

        return $productSku;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    private function filterConditions($item)
    {
        if (isset($item['reason_id']) && !(int)$item['reason_id']) {
            unset($item['reason_id']);
        }
        if (isset($item['resolution_id']) && !(int)$item['resolution_id']) {
            unset($item['resolution_id']);
        }
        if (isset($item['condition_id']) && !(int)$item['condition_id']) {
            unset($item['condition_id']);
        }

        return $item;
    }

    /**
     * Returns array of validation error messages
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * Validate post data
     *
     * @param array $data
     *
     * @return bool Return FALSE if someone item is invalid
     */
    public function validate($data)
    {
        return $this->validateMultiOrders($data) && $this->validateRequireEntry($data) && $this->validateItemsQty($data) &&
            $this->isAvailableItemsQty($data) && $this->validateReasons($data);
    }

    /**
     * Is multiple orders allowed per RMA
     *
     * @param array $data
     *
     * @return bool
     */
    public function validateMultiOrders(array $data)
    {
        if (!$this->rmaPolicyConfig->isAllowMultipleOrders()) {
            $orderFound = false;
            $keys       = ['order_ids', 'receipt_number'];
            foreach ($keys as $key) {
                if (isset($data[$key])) {
                    if ($orderFound) { // store and offline orders at the same time
                        $this->errorMessages[] = __('Allowed only one order per RMA');

                        return false;
                    }
                    if (count($data[$key]) > 1) {
                        return false;
                    } elseif (count($data[$key]) === 1) {
                        $orderFound = true;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Check if required fields is not empty
     *
     * @param array $data
     *
     * @return bool
     */
    public function validateReasons(array $data)
    {
        $items   = $data['items'];
        $reasons = explode(',', $this->reasonsConfig->getGeneralCustomerRequirement());

        foreach ($items as $item) {
            if (!$item['qty_requested']) {
                continue;
            }
            foreach ($reasons as $reason) {
                if ($item[$reason . '_id'] < 1) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if required fields is not empty
     *
     * @param array $data
     *
     * @return bool
     */
    public function validateRequireEntry(array $data)
    {
        $requiredFields = [
            'items' => __('Items'),
        ];
        $errorNo        = true;
        foreach ($data as $field => $value) {
            if (in_array($field, array_keys($requiredFields)) && $value == '') {
                $errorNo = false;
                $this->errorMessages[] = __(
                    'To apply changes you should fill in required "%1" field', $requiredFields[$field]);
            }
        }

        return $errorNo;
    }

    /**
     * Check if any item has qty > 0
     *
     * @param array $data
     *
     * @return bool
     */
    public function validateItemsQty(array $data)
    {
        $isEmpty = true;
        foreach ($data['items'] as $item) {
            if ((int)$item['qty_requested'] > 0) {
                $isEmpty = false;
                break;
            }
        }
        if ($isEmpty) {
            $this->errorMessages[] = __("Please, add order items to the RMA (set 'Qty to Return')");

            return false;
        }

        return true;
    }

    /**
     * Check if requested items is available for RMA
     *
     * @param array $data
     *
     * @return bool
     */
    public function isAvailableItemsQty(array $data)
    {
        $order       = $this->orderAbstractFactory->get($data);
        $items       = $this->itemListBuilder->getList($order);
        $isAvailable = true;
        foreach ($items as $orderItem) {
            foreach ($data['items'] as $k => $item) {
                if ($orderItem->getOrderItemId() != $k || (int)$item['qty_requested'] <= 0) {
                    continue;
                }
                if (!$this->itemQuantityManagement->getQtyAvailable($orderItem)) {
                    $this->errorMessages[] = __("Please, set correct order items Quantity to return");
                    $isAvailable = false;
                }
            }
        }

        return $isAvailable;
    }
}
