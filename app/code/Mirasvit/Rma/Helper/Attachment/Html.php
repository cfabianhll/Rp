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



namespace Mirasvit\Rma\Helper\Attachment;

class Html extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Mirasvit\Rma\Api\Config\AttachmentConfigInterface
     */
    private $attachmentConfig;
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;

    /**
     * Html constructor.
     * @param \Mirasvit\Rma\Api\Config\AttachmentConfigInterface $attachmentConfig
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Mirasvit\Rma\Api\Config\AttachmentConfigInterface $attachmentConfig,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->attachmentConfig = $attachmentConfig;
        $this->context = $context;
        parent::__construct($context);
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getAttachmentLimits($storeId)
    {
        $item = [];
        $allowedExtensions = $this->attachmentConfig->getFileAllowedExtensions($storeId);
        if (count($allowedExtensions)) {
            $item[] = __('Allowed extensions:').' '.implode(', ', $allowedExtensions);
        }
        if ($allowedSize = $this->attachmentConfig->getFileSizeLimit($storeId)) {
            $item[] = __('Maximum size:').' '.$allowedSize.'Mb';
        }

        return implode('<br>', $item);
    }

    /**
     * Also add to layout
     * <action method="addJs"><script>mirasvit/core/jquery.min.js</script></action>
     * <action method="addJs"><script>mirasvit/core/jquery.MultiFile.js</script></action>
     * @param int $storeId
     * @return string
     */
    public function getFileInputHtml($storeId)
    {
        $allowedFileExtensions = $this->attachmentConfig->getFileAllowedExtensions($storeId);
        $accept = '';
        if (count($allowedFileExtensions)) {
            $accept = "accept='".implode('|', $allowedFileExtensions)."'";
        }

        return "<input type='file' class='multi' name='attachment[]' id='attachment' $accept>";
    }


}
