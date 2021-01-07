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



namespace Mirasvit\Rma\Observer;

use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Rma\Api\Config\AttachmentConfigInterface as Config;
use Mirasvit\Rma\Api\Repository\StatusRepositoryInterface;
use Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface;
use Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface;

/**
 * Notify about RMA changes
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RmaChangedObserver implements ObserverInterface
{
    /**
     * @var \Mirasvit\Rma\Helper\Helpdesk
     */
    private $helpdeskHelper;
    /**
     * @var \Mirasvit\Rma\Api\Config\HelpdeskConfigInterface
     */
    private $helpdeskConfig;
    /**
     * @var \Mirasvit\Rma\Helper\Ruleevent
     */
    private $rmaRuleEvent;
    /**
     * @var \Mirasvit\Rma\Helper\Rma\Event
     */
    private $eventHelper;
    /**
     * @var StatusRepositoryInterface
     */
    private $statusRepository;
    /**
     * @var RmaManagementInterface
     */
    private $rmaManagement;
    /**
     * @var AttachmentManagementInterface
     */
    private $attachmentManagement;

    /**
     * RmaChangedObserver constructor.
     * @param \Mirasvit\Rma\Api\Config\HelpdeskConfigInterface $helpdeskConfig
     * @param \Mirasvit\Rma\Helper\Ruleevent $rmaRuleEvent
     * @param \Mirasvit\Rma\Helper\Helpdesk $helpdeskHelper
     * @param \Mirasvit\Rma\Helper\Rma\Event $eventHelper
     * @param StatusRepositoryInterface $statusRepository
     * @param RmaManagementInterface $rmaManagement
     * @param AttachmentManagementInterface $attachmentManagement
     */
    public function __construct(
        \Mirasvit\Rma\Api\Config\HelpdeskConfigInterface $helpdeskConfig,
        \Mirasvit\Rma\Helper\Ruleevent $rmaRuleEvent,
        \Mirasvit\Rma\Helper\Helpdesk $helpdeskHelper,
        \Mirasvit\Rma\Helper\Rma\Event $eventHelper,
        StatusRepositoryInterface $statusRepository,
        RmaManagementInterface $rmaManagement,
        AttachmentManagementInterface $attachmentManagement
    ) {
        $this->helpdeskConfig       = $helpdeskConfig;
        $this->rmaRuleEvent         = $rmaRuleEvent;
        $this->helpdeskHelper       = $helpdeskHelper;
        $this->eventHelper          = $eventHelper;
        $this->statusRepository     = $statusRepository;
        $this->rmaManagement        = $rmaManagement;
        $this->attachmentManagement = $attachmentManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $rma = $observer->getData('rma');

        $this->attachmentManagement->saveAttachment(
            Config::ATTACHMENT_ITEM_RETURN_LABEL,
            $rma->getId(),
            Config::ATTACHMENT_ITEM_RETURN_LABEL
        );

        $this->notifyRmaChange($rma, $observer->getData('performer'));
    }


    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface                    $rma
     * @param \Mirasvit\Rma\Api\Service\Performer\PerformerInterface $performer
     *
     * @return void
     */
    public function notifyRmaChange($rma, $performer)
    {
        $status = $this->rmaManagement->getStatus($rma);
        if ($rma->getStatusId() != $rma->getOrigData('status_id')) {
            $this->eventHelper->onRmaStatusChange($rma);
        }
        if ($rma->getOrigData('rma_id')) {
            if (
                $rma->getUserId() != $rma->getOrigData('user_id') &&
                $this->statusRepository->getAdminMessageForStore($status, $rma->getStoreId())
            ) {
                $this->eventHelper->onRmaUserChange($rma);
            }
            $this->rmaRuleEvent->newEvent(
                \Mirasvit\Rma\Api\Config\RuleConfigInterface::RULE_EVENT_RMA_UPDATED, $rma
            );
        } else {
            $this->rmaRuleEvent->newEvent(
                \Mirasvit\Rma\Api\Config\RuleConfigInterface::RULE_EVENT_RMA_CREATED, $rma
            );
            if ($rma->getTicketId() && $this->helpdeskConfig->isHelpdeskActive()) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $objectManager->create('\Mirasvit\Helpdesk\Helper\Ruleevent')->newEvent(
                    \Mirasvit\Helpdesk\Model\Config::RULE_EVENT_TICKET_CONVERTED_TO_RMA,
                    $this->helpdeskHelper->getTicket($rma->getTicketId())
                );
            }
        }
    }
}