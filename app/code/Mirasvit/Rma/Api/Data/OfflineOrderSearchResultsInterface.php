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



namespace Mirasvit\Rma\Api\Data;

interface OfflineOrderSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get rmas list.
     *
     * @return \Mirasvit\Rma\Api\Data\OfflineOrderInterface[]
     */
    public function getItems();

    /**
     * Set offline order list.
     *
     * @param array $items Array of \Mirasvit\Rma\Api\Data\OfflineOrderInterface[]
     * @return $this
     */
    public function setItems(array $items);
}
