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

use Mirasvit\Rma\Api;

/**
 * @method Api\Data\ItemSearchResultsInterface getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
 */
interface OfflineOrderRepositoryInterface
{
    /**
     * @param \Mirasvit\Rma\Api\Data\OfflineOrderInterface $offlineOrder
     * @return \Mirasvit\Rma\Api\Data\OfflineOrderInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Mirasvit\Rma\Api\Data\OfflineOrderInterface $offlineOrder);

    /**
     * @param int $offlineOrderId
     * @return \Mirasvit\Rma\Api\Data\OfflineOrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($offlineOrderId);

    /**
     * @param \Mirasvit\Rma\Api\Data\OfflineOrderInterface $offlineOrder Offline order which will deleted
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(\Mirasvit\Rma\Api\Data\OfflineOrderInterface $offlineOrder);

    /**
     * @param int $itemId
     * @return bool Will returned True if deleted
     */
    public function deleteById($itemId);
}