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



namespace Mirasvit\Rma\Block\Rma\View;

class Message extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaManagement;
    /**
     * @var \Mirasvit\Rma\Helper\Controller\Rma\AbstractStrategy
     */
    private $strategy;
    /**
     * @var \Mirasvit\Rma\Helper\Message\Url
     */
    private $rmaMessageUrl;
    /**
     * @var \Mirasvit\Rma\Helper\Attachment\Html
     */
    private $rmaAttachmentHtml;
    /**
     * @var \Mirasvit\Rma\Api\Config\AttachmentConfigInterface
     */
    private $attachmentConfig;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;

    /**
     * Message constructor.
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory
     * @param \Mirasvit\Rma\Helper\Message\Url $rmaMessageUrl
     * @param \Mirasvit\Rma\Helper\Attachment\Html $rmaAttachmentHtml
     * @param \Magento\Framework\Registry $registry
     * @param \Mirasvit\Rma\Api\Config\AttachmentConfigInterface $attachmentConfig
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory,
        \Mirasvit\Rma\Helper\Message\Url $rmaMessageUrl,
        \Mirasvit\Rma\Helper\Attachment\Html $rmaAttachmentHtml,
        \Magento\Framework\Registry $registry,
        \Mirasvit\Rma\Api\Config\AttachmentConfigInterface $attachmentConfig,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->rmaManagement     = $rmaManagement;
        $this->strategy          = $strategyFactory->create($context->getRequest());
        $this->rmaMessageUrl     = $rmaMessageUrl;
        $this->rmaAttachmentHtml = $rmaAttachmentHtml;
        $this->attachmentConfig  = $attachmentConfig;
        $this->registry          = $registry;
        $this->context           = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface|\Mirasvit\Rma\Model\OfflineOrder|false
     */
    public function getOrder()
    {
        return $this->rmaManagement->getOrder($this->getRma());
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
    public function getMessagePostUrl()
    {
        return $this->rmaMessageUrl->getPostUrl();
    }

    /**
     * @return string
     */
    public function getFileInputHtml()
    {
        return $this->rmaAttachmentHtml->getFileInputHtml($this->getStoreId());
    }

    /**
     * @return int
     */
    public function getAttachmentLimits()
    {
        $limit = '';
        $fileSize = $this->attachmentConfig->getFileSizeLimit($this->getStoreId());
        if ($fileSize) {
            $limit = __('Max file size: %1Mb', $fileSize);
        }
        $extensions = $this->attachmentConfig->getFileAllowedExtensions($this->getStoreId());
        if ($extensions) {
            $limit .= ' ' . __('Allowed extensions: %1', implode(', ', $extensions));
        }
        return $limit;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->context->getStoreManager()->getStore()->getId();
    }

    /**
     * @return int
     */
    public function getRmaId()
    {
        $rma = $this->getRma();

        return $this->strategy->getRmaId($rma);
    }

}
