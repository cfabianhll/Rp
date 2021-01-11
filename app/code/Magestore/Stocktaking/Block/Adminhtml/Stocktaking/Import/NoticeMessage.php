<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Block\Adminhtml\Stocktaking\Import;

/**
 * Class NoticeMessage
 *
 * Used for notice message block
 */
class NoticeMessage extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * NoticeMessage constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context
    ) {
        $this->backendSession = $context->getBackendSession();
        parent::__construct($context);
    }

    /**
     * Is has error
     *
     * @return boolean
     */
    public function isHasError()
    {
        return $this->backendSession->getData('is_error_'.$this->getRequest()->getParam('id'), true);
    }

    /**
     * Get invalid file csv
     *
     * @return string
     */
    public function getInvalidFileCsvUrl()
    {
        return $this->getUrl(
            'stocktaking/stocktaking/downloadInvalidCsv',
            [
                'id' => $this->getRequest()->getParam('id')
            ]
        );
    }
}
