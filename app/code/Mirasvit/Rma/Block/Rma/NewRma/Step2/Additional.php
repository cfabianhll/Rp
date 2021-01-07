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



namespace Mirasvit\Rma\Block\Rma\NewRma\Step2;

class Additional extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Rma\Api\Config\AttachmentConfigInterface
     */
    private $attachmentConfig;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;
    /**
     * @var \Mirasvit\Rma\Helper\Attachment\Html
     */
    private $rmaAttachmentHtml;

    /**
     * Additional constructor.
     * @param \Mirasvit\Rma\Helper\Attachment\Html $rmaAttachmentHtml
     * @param \Mirasvit\Rma\Api\Config\AttachmentConfigInterface $attachmentConfig
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Rma\Helper\Attachment\Html $rmaAttachmentHtml,
        \Mirasvit\Rma\Api\Config\AttachmentConfigInterface $attachmentConfig,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->attachmentConfig = $attachmentConfig;
        $this->rmaAttachmentHtml = $rmaAttachmentHtml;
        $this->context = $context;
        parent::__construct($context, $data);
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
    public function getStoreId()
    {
        return $this->context->getStoreManager()->getStore()->getId();
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

}