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


namespace Mirasvit\Rma\Service\Notification;

class RmaStrategy implements \Mirasvit\Rma\Api\Service\Notification\NotificationInterface
{
    /**
     * @var \Mirasvit\Rma\Helper\Mail
     */
    private $rmaMail;
    /**
     * @var \Mirasvit\Rma\Helper\Rma\Data
     */
    private $rmaData;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface
     */
    private $messageManagement;

    /**
     * RmaStrategy constructor.
     * @param \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface $messageManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Mirasvit\Rma\Helper\Rma\Data $rmaData
     * @param \Mirasvit\Rma\Helper\Mail $rmaMail
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface $messageManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Rma\Helper\Rma\Data $rmaData,
        \Mirasvit\Rma\Helper\Mail $rmaMail
    ) {
        $this->messageManagement = $messageManagement;
        $this->rmaManagement     = $rmaManagement;
        $this->storeManager      = $storeManager;
        $this->rmaData           = $rmaData;
        $this->rmaMail           = $rmaMail;
    }

    /**
     * {@inheritdoc}
     */
    public function send($rma, $performer)
    {

    }
}