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



namespace Mirasvit\Rma\Controller\Adminhtml\Rma;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Rma\Controller\Adminhtml\Rma;

class ConvertTicket extends Rma
{
    /**
     * @var \Mirasvit\Rma\Helper\Helpdesk
     */
    private $helpdeskHelper;

    /**
     * ConvertTicket constructor.
     * @param \Mirasvit\Rma\Helper\Helpdesk $helpdeskHelper
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Mirasvit\Rma\Helper\Helpdesk $helpdeskHelper,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->helpdeskHelper = $helpdeskHelper;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Mirasvit\Helpdesk\Model\Ticket $ticket */
        $ticket = $this->helpdeskHelper->getTicket((int)$this->getRequest()->getParam('id'));

        $this->_redirect('*/*/add', ['order_id' => $ticket->getOrderId(), 'ticket_id' => $ticket->getId()]);
    }
}
