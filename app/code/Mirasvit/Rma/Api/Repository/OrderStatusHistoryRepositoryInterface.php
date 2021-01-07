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



namespace Mirasvit\Rma\Api\Repository;


interface OrderStatusHistoryRepositoryInterface
{
    /**
     * @param \Mirasvit\Rma\Api\Data\OrderStatusHistoryInterface $row
     * @return \Mirasvit\Rma\Api\Data\OrderStatusHistoryInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save($row);

    /**
     * @param int $id
     * @return \Mirasvit\Rma\Api\Data\OrderStatusHistoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id);

    /**
     * @param int    $orderId
     * @param string $status
     * @return \Mirasvit\Rma\Api\Data\OrderStatusHistoryInterface
     */
    public function getByOrderStatus($orderId, $status);

    /**
     * @param int $orderId
     * @return \Mirasvit\Rma\Api\Data\OrderStatusHistoryInterface
     */
    public function getByOrderId($orderId);


    /**
     * @param \Mirasvit\Rma\Api\Data\OrderStatusHistoryInterface $row
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(\Mirasvit\Rma\Api\Data\OrderStatusHistoryInterface $row);

    /**
     * @param int $id
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($id);
}