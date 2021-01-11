<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\WebposPaynl\Block;

/**
 * Class Container
 * @package Magestore\WebposPaynl\Block
 */
class Container extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magestore\WebposPaynl\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Container constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\WebposPaynl\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\WebposPaynl\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_storeManager = $context->getStoreManager();
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function isEnableSendInvoice(){
        return ($this->helper->isAllowCustomerPayWithEmail())?'true':'false';
    }

    /**
     * @return bool
     */
    public function isEnablePaynl(){
        return $this->helper->isEnablePaynl();
    }

}
