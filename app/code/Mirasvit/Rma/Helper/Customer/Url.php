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



namespace Mirasvit\Rma\Helper\Customer;

class Url extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Backend\Model\Url
     */
    private $backendUrlManager;
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;

    /**
     * Url constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\Model\Url $backendUrlManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Url $backendUrlManager
    ) {
        $this->context = $context;
        $this->backendUrlManager = $backendUrlManager;
        parent::__construct($context);
    }

    /**
     * @param int $customerId
     * @return string
     */
    public function getBackendUrl($customerId)
    {
        return $this->backendUrlManager->getUrl('customer/index/edit', ['id' => $customerId]);
    }
}