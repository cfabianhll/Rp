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

use Mage;
use Magento\Framework\Controller\ResultFactory;

class MassSelectOrders extends \Mirasvit\Rma\Controller\Adminhtml\Rma
{
    /**
     * @var \Mirasvit\Rma\Model\RmaFactory
     */
    private $rmaFactory;

    /**
     * MassSelectOrders constructor.
     * @param \Mirasvit\Rma\Model\RmaFactory $rmaFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Mirasvit\Rma\Model\RmaFactory $rmaFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->rmaFactory = $rmaFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $ids = $this->getRequest()->getParam('selected_orders');
        if (!is_array($ids)) {
            $this->messageManager->addErrorMessage(__('Please select order(s)'));
        } else {
            $data     = [
                'orders_id' => implode(',', $ids)
            ];
            $ticketId = $this->getRequest()->getParam('ticket_id');
            if ($ticketId) {
                $data['ticket_id'] = $ticketId;
            }
            $this->_redirect('*/*/add', $data);

            return;
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
