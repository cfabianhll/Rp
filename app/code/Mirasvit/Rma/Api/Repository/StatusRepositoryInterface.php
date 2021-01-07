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


interface StatusRepositoryInterface
{
    /**
     * @param \Mirasvit\Rma\Api\Data\StatusInterface $status
     *
     * @return \Mirasvit\Rma\Api\Data\StatusInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Mirasvit\Rma\Api\Data\StatusInterface $status);

    /**
     * @return \Mirasvit\Rma\Model\ResourceModel\Status\Collection
     */
    public function getCollection();

    /**
     * @param int $statusId
     *
     * @return \Mirasvit\Rma\Api\Data\StatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($statusId);

    /**
     * @param string $code
     *
     * @return \Mirasvit\Rma\Api\Data\StatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCode($code);

    /**
     * @param \Mirasvit\Rma\Api\Data\StatusInterface $status status which will deleted
     *
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(\Mirasvit\Rma\Api\Data\StatusInterface $status);

    /**
     * @param int $statusId
     *
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($statusId);

    /**
     * @param \Mirasvit\Rma\Api\Data\StatusInterface $status
     * @param int                                    $storeId
     *
     * @return string
     */
    public function getCustomerMessageForStore($status, $storeId);

    /**
     * @param \Mirasvit\Rma\Api\Data\StatusInterface $status
     * @param int                                    $storeId
     *
     * @return string
     */
    public function getAdminMessageForStore($status, $storeId);

    /**
     * @param \Mirasvit\Rma\Api\Data\StatusInterface $status
     * @param int                                    $storeId
     *
     * @return string
     */
    public function getHistoryMessageForStore($status, $storeId);
}