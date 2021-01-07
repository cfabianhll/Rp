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


namespace Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form;


class Message extends \Magento\Backend\Block\Template
{
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaManagement;

    /**
     * Message constructor.
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->rmaManagement = $rmaManagement;

        parent::__construct($context, $data);
    }
    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTemplateFormHtml()
    {
        return $this->getLayout()->createBlock('\Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form\QuickResponse')
            ->setTemplate('rma/edit/form/quick_response.phtml')
            ->toHtml();
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
        return $this->getData('rma');
    }
}