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



namespace Mirasvit\Rma\Block\Rma;

use Mirasvit\Rma\Api\Data\StatusInterface;
use Mirasvit\Rma\Api\Repository\StatusRepositoryInterface;
use Mirasvit\Rma\Api\Service\Status\StatusManagementInterface;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var int
     */
    private   $currentStatusId;
    /**
     * @var \Magento\Framework\Registry
     */
    private   $registry;
    /**
     * @var StatusRepositoryInterface
     */
    private   $statusRepository;

    /**
     * @var \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface
     */
    protected $fieldManagement;
    /**
     * @var \Mirasvit\Rma\Helper\Mail
     */
    protected $mailHelper;
    /**
     * @var \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface
     */
    protected $messageManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Message\MessageManagement\SearchInterface
     */
    protected $messageSearchManagement;
    /**
     * @var \Mirasvit\Rma\Helper\Rma\Html
     */
    protected $rmaHtmlHelper;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    protected $rmaManagement;
    /**
     * @var \Mirasvit\Rma\Helper\Order\Html
     */
    protected $rmaOrderHtml;
    /**
     * @var \Mirasvit\Rma\Helper\StatusTree
     */
    protected $rmaStatusTreeHelper;
    /**
     * @var StatusManagementInterface
     */
    protected $statusManagement;

    /**
     * View constructor.
     * @param \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface $messageManagement
     * @param \Mirasvit\Rma\Api\Service\Message\MessageManagement\SearchInterface $messageSearchManagement
     * @param StatusManagementInterface $statusManagement
     * @param \Mirasvit\Rma\Helper\Mail $mailHelper
     * @param \Mirasvit\Rma\Helper\Order\Html $rmaOrderHtml
     * @param \Mirasvit\Rma\Helper\StatusTree $rmaStatusTreeHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $fieldManagement
     * @param StatusRepositoryInterface $statusRepository
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface $messageManagement,
        \Mirasvit\Rma\Api\Service\Message\MessageManagement\SearchInterface $messageSearchManagement,
        StatusManagementInterface $statusManagement,
        \Mirasvit\Rma\Helper\Mail $mailHelper,
        \Mirasvit\Rma\Helper\Order\Html $rmaOrderHtml,
        \Mirasvit\Rma\Helper\StatusTree $rmaStatusTreeHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $fieldManagement,
        StatusRepositoryInterface $statusRepository,
        array $data = []
    ) {
        $this->mailHelper              = $mailHelper;
        $this->messageManagement       = $messageManagement;
        $this->messageSearchManagement = $messageSearchManagement;
        $this->rmaOrderHtml            = $rmaOrderHtml;
        $this->rmaStatusTreeHelper     = $rmaStatusTreeHelper;
        $this->registry                = $registry;
        $this->rmaManagement           = $rmaManagement;
        $this->statusManagement        = $statusManagement;
        $this->fieldManagement         = $fieldManagement;
        $this->statusRepository        = $statusRepository;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($rma = $this->getRma()) {
            $this->pageConfig->getTitle()->set(__('RMA #%1', $this->escapeHtml($rma->getIncrementId())));
            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle(
                    __('RMA #%1 - %2', $this->escapeHtml($rma->getIncrementId()),
                    $this->escapeHtml($this->rmaManagement->getStatus($rma)->getName()))
                );
            }
        }
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        return $this->registry->registry('current_rma');
    }

    /**
     * @return string
     */
    public function getStatusMessage()
    {
        $rma    = $this->getRma();
        $status = $this->rmaManagement->getStatus($rma);

        $message = $this->statusRepository->getHistoryMessageForStore($status, $rma->getStoreId());

        return $this->mailHelper->parseVariables($message, $rma);
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface[]|\Mirasvit\Rma\Model\OfflineOrder[]
     */
    public function getOrders()
    {
        return $this->rmaManagement->getOrders($this->getRma());
    }


    /**
     * @return array
     */
    public function getProgress()
    {
        $statuses   = $this->statusRepository->getCollection()->addActiveFilter();
        $statusTree = $this->rmaStatusTreeHelper->getRmaBranch($this->getRma());
        $isOldTree  = true; // after update to v2.0.61 statuses do not organized in tree yet, so we use old method
        /** @var StatusInterface $status */
        foreach ($statuses as $status) {
            if (count($status->getChildrenIds())) {
                $isOldTree = false;
            }
        }

        $progress = [];
        foreach ($statusTree as $statusId) {
            $status = $statuses->getItemById($statusId);

            // v2.0.60 does not show "rejected" on frontend
            if ($isOldTree && $status->getCode() == 'rejected') {
                continue;
            }
            $progress[] = [
                'label'   => $status->getName(),
                'active'  => false,
                'visible' => $status->getIsVisible(),
            ];

            if ($status->getId() === $this->getCurrentStatusId()) {
                foreach (array_keys($progress) as $key) {
                    $progress[$key]['active'] = true;
                }
            }
        }

        return $progress;
    }

    /**
     * @return int
     */
    public function getCurrentStatusId()
    {
        if (!$this->currentStatusId) {
            $this->currentStatusId = $this->getRma()->getStatusId();
        }

        return $this->currentStatusId;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface|\Mirasvit\Rma\Model\OfflineOrder $order
     *
     * @return string
     */
    public function getOrderLabel($order)
    {
        if ($order->getIsOffline()) {
            return $this->escapeHtml($order->getReceiptNumber());
        } else {
            return '#' . $this->escapeHtml($order->getIncrementId());
        }
    }
}
