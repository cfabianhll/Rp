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

use Mirasvit\Rma\Api\Repository\StatusRepositoryInterface;
use Mirasvit\Rma\Api\Service\Message\MessageManagement\AddInterface;
use Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface;

class Event extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Mirasvit\Rma\Helper\Mail
     */
    private $rmaMail;
    /**
     * @var StatusRepositoryInterface
     */
    private $statusRepository;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaManagement;
    /**
     * @var AddInterface
     */
    private $messageAddManagement;
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Rma\CollectionFactory
     */
    private $rmaCollectionFactory;
    /**
     * @var \Mirasvit\Rma\Service\Config\RmaNumberConfig
     */
    private $numberConfig;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface
     */
    private $rmaSearchManagement;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    private $storeFactory;

    /**
     * Event constructor.
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement
     * @param \Mirasvit\Rma\Helper\Mail $rmaMail
     * @param \Mirasvit\Rma\Service\Config\RmaNumberConfig $numberConfig
     * @param \Mirasvit\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollectionFactory
     * @param AddInterface $messageAddManagement
     * @param StatusRepositoryInterface $statusRepository
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Mirasvit\Rma\Helper\Mail $rmaMail,
        \Mirasvit\Rma\Service\Config\RmaNumberConfig $numberConfig,
        \Mirasvit\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollectionFactory,
        AddInterface $messageAddManagement,
        StatusRepositoryInterface $statusRepository,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->storeFactory         = $storeFactory;
        $this->storeManager         = $storeManager;
        $this->messageAddManagement = $messageAddManagement;
        $this->rmaManagement        = $rmaManagement;
        $this->rmaSearchManagement  = $rmaSearchManagement;
        $this->numberConfig         = $numberConfig;
        $this->rmaCollectionFactory = $rmaCollectionFactory;
        $this->statusRepository     = $statusRepository;
        $this->rmaMail              = $rmaMail;

        parent::__construct($context);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     *
     * @return void
     */
    public function onRmaStatusChange($rma)
    {
        $status = $this->rmaManagement->getStatus($rma);
        $customerMessage = $this->statusRepository->getCustomerMessageForStore($status, $rma->getStoreId());
        $adminMessage    = $this->statusRepository->getAdminMessageForStore($status, $rma->getStoreId());
        $historyMessage  = $this->statusRepository->getHistoryMessageForStore($status, $rma->getStoreId());
        if ($customerMessage) {
            $this->rmaMail->sendNotificationCustomerEmail($rma, $customerMessage, true);
        }
        if ($adminMessage) {
            $this->rmaMail->sendNotificationAdminEmail($rma, $adminMessage, true);
        }

        if ($historyMessage) {
            $message = $this->rmaMail->parseVariables($historyMessage, $rma);

            $params = [
                'isNotified'    => $customerMessage != '',
                'isVisible'     => 1,
                'isHistory'     => 1,
                'statusChanged' => 1,
            ];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $user = $objectManager->create('\Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface')
                ->create(PerformerFactoryInterface::USER, $this->rmaManagement->getUser($rma));

            $this->messageAddManagement->addMessage($user, $rma, $message, $params);
        }
        if ($customerMessage || $historyMessage) {
            if ($rma->getUserId()) {
                $rma->setLastReplyName($this->rmaManagement->getFullName($rma))
                    ->save();
            }
        }
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     *
     * @return void
     */
    public function onRmaUserChange($rma)
    {
        $status  = $this->rmaManagement->getStatus($rma);
        $message = $this->statusRepository->getAdminMessageForStore($status, $rma->getStoreId());
        $message = $this->rmaMail->parseVariables($message, $rma);
        $this->rmaMail->sendNotificationAdminEmail($rma, $message);
    }
}