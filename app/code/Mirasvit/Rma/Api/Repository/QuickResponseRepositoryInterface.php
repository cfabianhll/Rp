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

use \Mirasvit\Rma\Api\Data\QuickResponseInterface as DataQuickResponseInterface;

interface QuickResponseRepositoryInterface
{
    /**
     * @param DataQuickResponseInterface $response
     * @return DataQuickResponseInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(DataQuickResponseInterface $response);

    /**
     * @param int $responseId
     * @return DataQuickResponseInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($responseId);

    /**
     * @param string $name
     * @return DataQuickResponseInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByName($name);

    /**
     * @param int $storeId
     * @return \Mirasvit\Rma\Model\ResourceModel\QuickResponse\Collection
     */
    public function getListByStoreId($storeId);

    /**
     * @param DataQuickResponseInterface $response quick response which will deleted
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(DataQuickResponseInterface $response);

    /**
     * @param int $responseId
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($responseId);
}