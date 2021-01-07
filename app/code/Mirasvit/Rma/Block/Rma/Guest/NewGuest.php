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



namespace Mirasvit\Rma\Block\Rma\Guest;

class NewGuest extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;

    /**
     * NewGuest constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Request RMA'));
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle(__('Request RMA'));
        }
    }

    /**
     * @return string
     */
    public function getStep1PostUrl()
    {
        return $this->context->getUrlBuilder()->getUrl('returns/guest/new');
    }

    /**
     * @return object
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * @return int
     */
    public function getOrderIncrementId()
    {
        return $this->getRequest()->getParam('order_increment_id');
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->getRequest()->getParam('email');
    }
}
