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


namespace Mirasvit\Rma\Helper;

use \Mirasvit\Rma\Api\Data\RmaInterface;
use \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface;

class Process
{
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;
    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    private $userCollectionFactory;
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Rma\CollectionFactory
     */
    private $rmaCollectionFactory;
    /**
     * @var \Mirasvit\Rma\Api\Service\Message\MessageManagement\AddInterface
     */
    private $messageAddManagement;
    /**
     * @var PerformerFactoryInterface
     */
    private $performer;
    /**
     * @var \Mirasvit\Rma\Api\Config\HelpdeskConfigInterface
     */
    private $helpdeskConfig;

    /**
     * Process constructor.
     * @param \Mirasvit\Rma\Api\Config\HelpdeskConfigInterface $helpdeskConfig
     * @param PerformerFactoryInterface $performer
     * @param \Mirasvit\Rma\Api\Service\Message\MessageManagement\AddInterface $messageAddManagement
     * @param \Mirasvit\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollectionFactory
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Mirasvit\Rma\Api\Config\HelpdeskConfigInterface $helpdeskConfig,
        \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface $performer,
        \Mirasvit\Rma\Api\Service\Message\MessageManagement\AddInterface $messageAddManagement,
        \Mirasvit\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollectionFactory,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->helpdeskConfig        = $helpdeskConfig;
        $this->performer             = $performer;
        $this->messageAddManagement  = $messageAddManagement;
        $this->rmaCollectionFactory  = $rmaCollectionFactory;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->context               = $context;
    }

    /**
     * @param \Mirasvit\Helpdesk\Model\Email $email
     * @param string                         $code
     *
     * @return bool|\Mirasvit\Helpdesk\Model\Ticket
     *
     * @throws \Exception
     */
    public function processEmail($email, $code)
    {
        if (!$this->helpdeskConfig->isHelpdeskActive()) {
            return false;
        }
        $performerType = PerformerFactoryInterface::GUEST;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        //try to find RMA for this email
        $guestId = str_replace(RmaInterface::MESSAGE_CODE, '', $code);
        $rmas = $this->rmaCollectionFactory->create()->addFieldToFilter('guest_id', $guestId);
        if (!$rmas->count()) {// echo 'Can\'t find a RMA by guest id '.$guestId;
            return false;
        }

        $rma = $rmas->getFirstItem();

        //try to find staff user for this email
        $users = $this->userCollectionFactory->create()
            ->addFieldToFilter('email', $email->getFromEmail())
        ;
        if ($users->count()) {
            $performer = $users->getFirstItem();
            $rma->setUserId($performer->getId());
            $rma->save();
            $performerType = PerformerFactoryInterface::USER;
        } else {
            $performer = $objectManager->create('\Mirasvit\Helpdesk\Helper\Customer')->getCustomerByEmail($email);
            if ($performer->getId()) {
                $performerType = PerformerFactoryInterface::CUSTOMER;
            }
        }

        $performer = $this->performer->create($performerType, $performer);

        //add message to rma
        $body = $objectManager->create('\Mirasvit\Helpdesk\Helper\StringUtil')
            ->parseBody($email->getBody(), $email->getFormat());

        $ticket = $objectManager->create('\Mirasvit\Helpdesk\Model\Ticket')->load($rma->getTicketId());
        $ticket->setRmaId($rma->getId());

        $this->context->getEventManager()->dispatch(
            'helpdesk_process_email',
            ['body' => $body, 'performer' => $performer, 'ticket' => $ticket, 'email' => $email]
        );

        return $rma;
    }
}
