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


namespace Mirasvit\Rma\Block\Adminhtml\Order\View\Tab;

class Rma extends \Magento\Framework\View\Element\Text\ListText implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Mirasvit\Rma\Api\Service\Order\OrderManagementInterface
     */
    private $orderManagement;
    /**
     * @var \Magento\Framework\Authorization\PolicyInterface
     */
    private $policyInterface;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $adminSession;

    /**
     * Rma constructor.
     * @param \Mirasvit\Rma\Api\Service\Order\OrderManagementInterface $orderManagement
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\Authorization\PolicyInterface $policyInterface
     * @param \Magento\Backend\Model\Auth\Session $adminSession
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Order\OrderManagementInterface $orderManagement,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Authorization\PolicyInterface $policyInterface,
        \Magento\Backend\Model\Auth\Session $adminSession,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->orderManagement = $orderManagement;
        $this->registry        = $registry;
        $this->policyInterface = $policyInterface;
        $this->adminSession    = $adminSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('RMAs (%1)', $this->orderManagement->getRmaAmount($this->getOrder()));
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('RMAs (%1)', $this->orderManagement->getRmaAmount($this->getOrder()));
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        $roleId = $this->adminSession->getUser()->getRole()->getRoleId();

        if ($this->policyInterface->isAllowed($roleId, 'Mirasvit_Rma::add')) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    private function getOrder()
    {
        return $this->registry->registry('current_order');
    }
}
