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


namespace Mirasvit\Rma\Service\Rma\RmaManagement;

/**
 * Save RMA
 */
class Save implements \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SaveInterface
{
    /**
     * @var \Mirasvit\Rma\Api\Repository\MessageRepositoryInterface
     */
    private $messageRepository;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface
     */
    private $rmaSearchManagement;
    /**
     * @var \Mirasvit\Rma\Service\Item\Update
     */
    private $itemUpdateService;
    /**
     * @var \Mirasvit\Rma\Api\Service\Message\MessageManagement\AddInterface
     */
    private $messageAddService;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface
     */
    private $rmaOrderService;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Mirasvit\Rma\Model\RmaFactory
     */
    private $rmaFactory;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var \Mirasvit\Rma\Service\Order\OrderAbstractFactory
     */
    private $orderAbstractFactory;

    /**
     * Save constructor.
     * @param \Mirasvit\Rma\Api\Repository\MessageRepositoryInterface $messageRepository
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement
     * @param \Mirasvit\Rma\Api\Service\Message\MessageManagement\AddInterface $messageAddService
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface $rmaOrderService
     * @param \Mirasvit\Rma\Model\RmaFactory $rmaFactory
     * @param \Mirasvit\Rma\Service\Item\Update $itemUpdateService
     * @param \Mirasvit\Rma\Service\Order\OrderAbstractFactory $orderAbstractFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Mirasvit\Rma\Api\Repository\MessageRepositoryInterface $messageRepository,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Mirasvit\Rma\Api\Service\Message\MessageManagement\AddInterface $messageAddService,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface $rmaOrderService,
        \Mirasvit\Rma\Model\RmaFactory $rmaFactory,
        \Mirasvit\Rma\Service\Item\Update $itemUpdateService,
        \Mirasvit\Rma\Service\Order\OrderAbstractFactory $orderAbstractFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $registry
    ) {
        $this->messageRepository      = $messageRepository;
        $this->rmaSearchManagement    = $rmaSearchManagement;
        $this->itemUpdateService      = $itemUpdateService;
        $this->messageAddService      = $messageAddService;
        $this->rmaManagement          = $rmaManagement;
        $this->rmaOrderService        = $rmaOrderService;
        $this->registry               = $registry;
        $this->rmaFactory             = $rmaFactory;
        $this->request                = $request;
        $this->eventManager           = $eventManager;
        $this->orderAbstractFactory   = $orderAbstractFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function saveRma($performer, $data, $items)
    {
        $rma = $this->rmaFactory->create();
        if (isset($data['rma_id']) && $data['rma_id']) {
            $rma->load($data['rma_id']);
        }
        unset($data['rma_id']);

        $rma = $this->updateRma($performer, $rma, $data);

        $this->itemUpdateService->updateItems($rma, $items);

        $this->eventManager->dispatch('rma_update_rma_after', ['rma' => $rma, 'performer' => $performer]);

        $filesData = $this->request->getFiles();
        $files = $filesData->toArray();
        if (
            (isset($data['reply']) && $data['reply'] != '') ||
            !empty($files['attachment'][0]['name'])
        ) {
            $this->messageAddService->addMessage($performer, $rma, $data['reply'], $data);
        }

        return $rma;
    }

    /**
     * @param \Mirasvit\Rma\Api\Service\Performer\PerformerInterface $performer
     * @param \Mirasvit\Rma\Api\Data\RmaInterface                    $rma
     * @param array                                                  $data
     *
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    protected function updateRma($performer, $rma, $data)
    {
        if (isset($data['street2']) && $data['street2'] != '') {
            $data['street'] .= "\n".$data['street2'];
            unset($data['street2']);
        }

        $order = $this->orderAbstractFactory->get($data);
        if (!empty($data['is_offline'])) {
            $orderInfo = current($data['orders']);
            $order->getResource()->load($order, $orderInfo['order_id']);
        } else {
            $orderId = isset($data['order_ids']) ? current($data['order_ids']) : null;
            $order->getResource()->load($order, $orderId);
        }
        if (!$order->getId()) {
            $order = $this->rmaOrderService->getOrder($rma);
        }

        $rma->addData($data);
        $rma->setIfOffline($order->getIsOffline());

        $storeId = $order->getStoreId();
        if (!$storeId && isset($data['store_id'])) {
            $storeId = (int)$data['store_id'];
        }
        $customerId = $order->getCustomerId();
        if (!$customerId && isset($data['customerId'])) {
            $customerId = (int)$data['customerId'];
        }
        $rma->setCustomerId($customerId);
        $rma->setStoreId($storeId);

        if (!$order->getCustomerId() && empty($rma->getEmail())) {
            $this->setRmaCustomerInfo($rma, $performer);
        } else {
            $this->setRmaAddress($rma);
        }

        $performer->setRmaAttributesBeforeSave($rma);

        $rma->save();

        $this->registry->register('current_rma', $rma);

        return $rma;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return void
     */
    protected function setRmaAddress($rma)
    {
        $customer = $this->rmaManagement->getCustomer($rma);
        $address = $customer->getDefaultBillingAddress();
        if ($address) {
            $this->setRmaAddressData($rma, $address);
        } else {
            $this->setRmaCustomerInfo($rma, $customer);
        }
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @param \Magento\Customer\Model\Address     $address
     * @return void
     */
    public function setRmaAddressData($rma, $address)
    {
        if (empty($rma->getFirstname())) {
            $rma->setFirstname($address->getFirstname());
        }
        if (empty($rma->getLastname())) {
            $rma->setLastname($address->getLastname());
        }
        if (empty($rma->getCompany())) {
            $rma->setCompany($address->getCompany());
        }
        if (empty($rma->getTelephone())) {
            $rma->setTelephone($address->getTelephone());
        }
        $rma
            ->setStreet(implode("\n", $address->getStreet()))
            ->setCity($address->getCity())
            ->setCountryId($address->getCountryId())
            ->setRegionId($address->getRegionId())
            ->setRegion($address->getRegion())
            ->setPostcode($address->getPostcode());
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @param \Magento\Customer\Model\Customer    $customer
     * @return void
     */
    public function setRmaCustomerInfo($rma, $customer)
    {
        if (!method_exists($customer, 'getEmail') || !$customer->getEmail()) {
            return;
        }
        if (empty($rma->getFirstname())) {
            $rma->setFirstname($customer->getFirstname());
        }
        if (empty($rma->getLastname())) {
            $rma->setLastname($customer->getLastname());
        }
        if (empty($rma->getEmail())) {
            $rma->setEmail($customer->getEmail());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function markAsReadForCustomer(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        /** @var \Mirasvit\Rma\Api\Data\MessageInterface $message */
        foreach ($this->rmaSearchManagement->getCustomerUnread($rma) as $message) {
            $message->setIsRead(true);
            $this->messageRepository->save($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function markAsReadForUser(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        /** @var \Mirasvit\Rma\Api\Data\MessageInterface $message */
        foreach ($this->rmaSearchManagement->getUserUnread($rma) as $message) {
            $message->setIsRead(true);
            $this->messageRepository->save($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function markAsUnreadForCustomer(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        /** @var \Mirasvit\Rma\Api\Data\MessageInterface $message */
        foreach ($this->rmaSearchManagement->getCustomerRead($rma) as $message) {
            $message->setIsRead(false);
            $this->messageRepository->save($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function markAsUnreadForUser(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        /** @var \Mirasvit\Rma\Api\Data\MessageInterface $message */
        foreach ($this->rmaSearchManagement->getUserRead($rma) as $message) {
            $message->setIsRead(false);
            $this->messageRepository->save($message);
        }
    }
}