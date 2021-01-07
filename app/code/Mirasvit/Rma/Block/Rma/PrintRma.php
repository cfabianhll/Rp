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



namespace Mirasvit\Rma\Block\Rma;

class PrintRma extends \Mirasvit\Rma\Block\Rma\View
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($rma = $this->getRma()) {
            $this->pageConfig->getTitle()->set(__('RMA #%1', $this->escapeHtml($rma->getIncrementId())));
            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle(__('RMA #%1', $this->escapeHtml($rma->getIncrementId())));
            }
        }
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return string
     */
    public function getReturnAddressHtml(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $this->rmaManagement->getReturnAddressHtml($rma);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return string
     */
    public function getShippingAddressHtml(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $this->rmaManagement->getShippingAddressHtml($rma);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @param bool                                $isEdit
     * @return \Mirasvit\Rma\Model\Field[]
     */
    public function getCustomFields(\Mirasvit\Rma\Api\Data\RmaInterface $rma, $isEdit = false)
    {
        return $this->fieldManagement->getVisibleCustomerCollection($rma->getStatusId(), $isEdit);
    }

    /**
     * @return \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface
     */
    public function getRmaField()
    {
        return $this->fieldManagement;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return \Mirasvit\Rma\Api\Data\MessageInterface[]
     */
    public function getMessages(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $this->messageSearchManagement->getVisibleInFront($rma);
    }

    /**
     * @return \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface
     */
    public function getMessageManagement()
    {
        return $this->messageManagement;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getOrderLabel($order)
    {
        return $this->rmaOrderHtml->getOrderLabel($order);
    }
}
