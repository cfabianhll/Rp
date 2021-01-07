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


namespace Mirasvit\Rma\Service\Rma;

use Mirasvit\Rma\Api\Data\RmaInterface;

/**
 * We put here only methods directly connected with RMA properties
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RmaManagement implements \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
{
    /**
     * @var \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface
     */
    private $rmaItemRepository;
    /**
     * @var \Mirasvit\Rma\Api\Repository\StatusRepositoryInterface
     */
    private $statusRepository;
    /**
     * @var \Mirasvit\Rma\Api\Config\RmaConfigInterface
     */
    private $rmaConfig;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface
     */
    private $rmaOrderService;
    /**
     * @var \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface
     */
    private $attachmentManagement;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    /**
     * @var \Magento\User\Model\UserFactory
     */
    private $userFactory;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;
    /**
     * @var array
     */
    private $orderRmas;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $rmaItemRepository
     * @param \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository
     * @param \Mirasvit\Rma\Api\Repository\StatusRepositoryInterface $statusRepository
     * @param \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface $attachmentManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface $rmaOrderService
     * @param \Mirasvit\Rma\Api\Config\RmaConfigInterface $rmaConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $rmaItemRepository,
        \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository,
        \Mirasvit\Rma\Api\Repository\StatusRepositoryInterface $statusRepository,
        \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface $attachmentManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface $rmaOrderService,
        \Mirasvit\Rma\Api\Config\RmaConfigInterface $rmaConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->rmaItemRepository      = $rmaItemRepository;
        $this->rmaRepository          = $rmaRepository;
        $this->statusRepository       = $statusRepository;
        $this->rmaConfig              = $rmaConfig;
        $this->localeDate             = $localeDate;
        $this->countryFactory         = $countryFactory;
        $this->rmaOrderService        = $rmaOrderService;
        $this->attachmentManagement   = $attachmentManagement;
        $this->customerFactory        = $customerFactory;
        $this->userFactory            = $userFactory;
        $this->storeRepository        = $storeRepository;
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
        $this->escaper                = $escaper;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(RmaInterface $rma)
    {
        return $this->statusRepository->get($rma->getStatusId());
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder(RmaInterface $rma)
    {
        return $this->rmaOrderService->getOrder($rma);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrders(RmaInterface $rma)
    {
        return $this->rmaOrderService->getOrders($rma);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomer(RmaInterface $rma)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->customerFactory->create()->load($rma->getCustomerId());
        if ($customer && $customer->getId()) {
            return $customer;
        }

        $order = $this->getOrder($rma);
        if (!$order) {
            $customer->setFirstname($this->escaper->escapeHtml($rma->getFirstname()));
            $customer->setLastname($this->escaper->escapeHtml($rma->getLastname()));

            return $customer;
        }
        $customer->setEmail($this->escaper->escapeHtml($order->getCustomerEmail()));
        if ($address = $order->getBillingAddress()) {
            $customer->setFirstname($this->escaper->escapeHtml($address->getFirstname()));
            $customer->setLastname($this->escaper->escapeHtml($address->getLastname()));
        } elseif ($address = $order->getShippingAddress()) {
            $customer->setFirstname($this->escaper->escapeHtml($address->getFirstname()));
            $customer->setLastname($this->escaper->escapeHtml($address->getLastname()));
        } else {
            $customer->setFirstname($this->escaper->escapeHtml($rma->getFirstname()));
            $customer->setLastname($this->escaper->escapeHtml($rma->getLastname()));
        }

        return $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(RmaInterface $rma)
    {
        return $this->userFactory->create()->load($rma->getUserId());
    }

    /**
     * {@inheritdoc}
     */
    public function getStore(RmaInterface $rma)
    {
        return $this->storeRepository->getById($rma->getStoreId());
    }

    /**
     * {@inheritdoc}
     */
    public function getTicket(RmaInterface $rma)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Mirasvit\Rma\Api\Config\HelpdeskConfigInterface $helpdeskConfig */
        $helpdeskConfig = $objectManager->create('\Mirasvit\Rma\Api\Config\HelpdeskConfigInterface');
        if (!$rma->getTicketId() || !$helpdeskConfig->isHelpdeskActive()) {
            return false;
        }
        /** @var \Mirasvit\Helpdesk\Model\Ticket $ticket */
        $ticket = $objectManager->create('\Mirasvit\Helpdesk\Model\TicketFactory')->create();
        $ticket->getResource()->load($ticket, $rma->getTicketId());

        return $ticket;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullName(RmaInterface $rma)
    {
        if (empty($rma->getFirstname()) && empty($rma->getLastname()) && $rma->getCustomerId()) {
            $customer = $this->getCustomer($rma);
            $name = $customer->getName();
        } else {
            $name = $this->escaper->escapeHtml($rma->getFirstname() .' '.$rma->getLastname());
        }

        return $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getReturnLabel($rma)
    {
        return $this->attachmentManagement->getAttachment(
            \Mirasvit\Rma\Api\Config\AttachmentConfigInterface::ATTACHMENT_ITEM_RETURN_LABEL, $rma->getId()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAddressHtml(RmaInterface $rma)
    {
        $items = [];
        $items[] = $this->escaper->escapeHtml($rma->getFirstname().' '.$rma->getLastname());
        if ($rma->getEmail()) {
            $items[] = $this->escaper->escapeHtml($rma->getEmail());
        }
        if ($rma->getTelephone()) {
            $items[] = $this->escaper->escapeHtml($rma->getTelephone());
        }
        if ($rma->getCompany()) {
            $items[] = $this->escaper->escapeHtml($rma->getCompany());
        }
        if ($rma->getStreet()) {
            $items[] = $this->escaper->escapeHtml($rma->getStreet());
        }
        if ($rma->getCity()) {
            $items[] = $this->escaper->escapeHtml($rma->getCity());
        }
        if ($rma->getRegion()) {
            $items[] = $this->escaper->escapeHtml($rma->getRegion());
        }
        if ($rma->getCountryId()) {
            $country = $this->countryFactory->create()->loadByCode($rma->getCountryId());
            $items[] = $country->getName();
        }

        return implode('<br>', $items);
    }

    /**
     * {@inheritdoc}
     */
    public function getReturnAddressHtml(RmaInterface $rma)
    {
        $address = $rma->getReturnAddress();
        if (!$address) {
            $address = $this->rmaConfig->getReturnAddress($rma->getStoreId());
        }

        return $address;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(RmaInterface $rma)
    {
        return $rma->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAtFormated(RmaInterface $rma)
    {
        $format = \IntlDateFormatter::MEDIUM;
        $date = new \DateTime($rma->getCreatedAt());

        return $this->localeDate->formatDateTime($date, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAtFormated(RmaInterface $rma)
    {
        $format = \IntlDateFormatter::MEDIUM;
        $date = new \DateTime($rma->getUpdatedAt());

        return $this->localeDate->formatDateTime($date, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function getRmasByOrder($order)
    {
        if (isset($this->orderRmas[$order->getId()])) {
            return $this->orderRmas[$order->getId()];
        }
        /** @var \Magento\Sales\Model\Order $order */
        $orderItemIds = $order->getItemsCollection()->getAllIds();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_item_id', $orderItemIds, 'in')
        ;

        $rmaItems = $this->rmaItemRepository->getList($searchCriteria->create())->getItems();
        $rmaIds = [];
        foreach ($rmaItems as $rmaItem) {
            $rmaIds[] = $rmaItem->getRmaId();
        }
        if (!$rmaIds) {
            return [];
        }
        $rmaIds = array_unique($rmaIds);

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rmaIds, 'in')
        ;

        $this->orderRmas[$order->getId()] = $this->rmaRepository->getList($searchCriteria->create())->getItems();

        return $this->orderRmas[$order->getId()];
    }
}

