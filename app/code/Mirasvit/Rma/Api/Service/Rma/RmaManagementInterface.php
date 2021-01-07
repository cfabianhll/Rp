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



namespace Mirasvit\Rma\Api\Service\Rma;

use Mirasvit\Rma\Api\Data\RmaInterface;

interface RmaManagementInterface
{
    /**
     * @param RmaInterface $rma
     * @return \Mirasvit\Rma\Api\Data\StatusInterface
     */
    public function getStatus(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return \Magento\Sales\Api\Data\OrderInterface|\Mirasvit\Rma\Model\OfflineOrder
     */
    public function getOrder(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return \Magento\Sales\Api\Data\OrderInterface[]|\Mirasvit\Rma\Model\OfflineOrder[]
     */
    public function getOrders(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return \Magento\User\Api\Data\UserInterface
     */
    public function getUser(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return bool|\Mirasvit\Helpdesk\Model\Ticket
     */
    public function getTicket(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getFullName(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return \Mirasvit\Rma\Api\Data\AttachmentInterface
     */
    public function getReturnLabel($rma);

    /**
     * @param RmaInterface $rma
     * @return string
     */
    public function getReturnAddressHtml(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return string
     */
    public function getShippingAddressHtml(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return string
     */
    public function getCode(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return string
     */
    public function getCreatedAtFormated(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return string
     */
    public function getUpdatedAtFormated(RmaInterface $rma);

    /**
     * @param \Mirasvit\Rma\Api\Data\OfflineOrderInterface $order
     * @return \Mirasvit\Rma\Api\Data\RmaInterface[]
     */
    public function getRmasByOrder($order);
}