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


namespace Mirasvit\Rma\Controller\Adminhtml\Rma;

use Magento\Framework\Exception\LocalizedException;
use Mirasvit\Rma\Api\Data\OfflineItemInterface;

class PostDataProcessor
{
    /**
     * @var \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface
     */
    private $fieldManagement;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;
    /**
     * @var \Magento\Framework\View\Model\Layout\Update\ValidatorFactory
     */
    private $validatorFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    private $dateFilter;
    /**
     * @var \Mirasvit\Rma\Service\Order\OrderAbstractFactory
     */
    private $orderAbstractFactory;
    /**
     * @var \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface
     */
    private $offlineOrderRepository;

    /**
     * PostDataProcessor constructor.
     * @param \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface $offlineOrderRepository
     * @param \Mirasvit\Rma\Service\Order\OrderAbstractFactory $orderAbstractFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $fieldManagement
     */
    public function __construct(
        \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface $offlineOrderRepository,
        \Mirasvit\Rma\Service\Order\OrderAbstractFactory $orderAbstractFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $fieldManagement
    ) {
        $this->offlineOrderRepository = $offlineOrderRepository;
        $this->orderAbstractFactory   = $orderAbstractFactory;
        $this->dateFilter             = $dateFilter;
        $this->messageManager         = $messageManager;
        $this->validatorFactory       = $validatorFactory;
        $this->timezone               = $timezone;
        $this->fieldManagement        = $fieldManagement;
    }

    /**
     * @param array $data
     * @return array
     */
    public function createOfflineOrder($data)
    {
        foreach ($data['orders'] as $orderNumber => $orderInfo) {
            if (isset($orderInfo['order_id']) && $orderInfo['order_id'] > 0) {
                continue;
            }
            $orderInfo['is_offline']     = $data['is_offline'];
            $orderInfo['customer_id']    = $data['customer_id'];
            $orderInfo['store_id']       = $data['store_id'];
            $orderInfo['receipt_number'] = $orderInfo['order_name'];
            $order = $this->orderAbstractFactory->get($orderInfo);
            $order->setData($orderInfo);
            $this->offlineOrderRepository->save($order);
            $data['orders'][$orderNumber]['order_id'] = $order->getId();
            $data['order_id'] = $order->getId();//we need this to generate increment for new RMA
            foreach ($data['items'] as $k => $item) {
                if ($item['order_number'] == $orderNumber) {
                    $data['items'][$k]['offline_order_id'] = $order->getId();
                }
            }
        }

        return $data;
    }

    /**
     * Filtering posted data. Return only RMA data.
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function filterRmaData($data)
    {
        $newData = $data;
        unset($newData['items']);

        if (empty($newData['return_address'])) {
            unset($newData['return_address']);
        }
        foreach($this->fieldManagement->getStaffCollection() as $field) {
            if ($field->getType() == 'date' && isset($newData[$field->getCode()])) {
                $newData[$field->getCode()] = $this->dateFilter->filter($newData[$field->getCode()]);
            }
        }

        return $newData;
    }
  
    /**
     * Filtering posted data. Return only RMA items.
     *
     * @param array $data
     * @return array
     */
    public function filterRmaItems($data)
    {
        $items = $data['items'];
        foreach ($items as $k => $item) {
            if (!(int) $item['reason_id']) {
                unset($item['reason_id']);
            }
            if (!(int) $item['resolution_id']) {
                unset($item['resolution_id']);
            }
            if (!(int) $item['condition_id']) {
                unset($item['condition_id']);
            }
            $items[$k] = $item;
        }
        return $items;
    }

    /**
     * Validate post data
     *
     * @param array $data
     * @return bool     Return FALSE if someone item is invalid
     */
    public function validate($data)
    {
        return $this->validateRequireEntry($data) && $this->validateItemsQty($data);
    }

    /**
     * Check if required fields is not empty
     *
     * @param array $data
     * @return bool
     */
    public function validateRequireEntry(array $data)
    {
        $requiredFields = [
            'items' => __('Items'),
        ];
        $errorNo = true;
        foreach ($requiredFields as $field => $value) {
            if (!isset($data[$field]) || $data[$field] == '') {
                $errorNo = false;
                $this->messageManager->addErrorMessage(
                    __('To apply changes you should fill in required "%1" field', $requiredFields[$field])
                );
            }
        }
        return $errorNo;
    }

    /**
     * Check if any item has qty > 0
     *
     * @param array $data
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
            $this->messageManager->addError(
                __("Please, add order items to the RMA (set 'Qty to Return')")
            );
            return false;
        }
        return true;
    }
}
