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


interface ShippingManagementInterface
{
    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return bool
     */
    public function isShowShippingBlock(\Mirasvit\Rma\Api\Data\RmaInterface $rma);

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return bool
     */
    public function isRequireShippingConfirmation(\Mirasvit\Rma\Api\Data\RmaInterface $rma);


    /**
     * @param int|\Magento\Store\Api\Data\StoreInterface $storeId
     * @return string
     */
    public function getShippingConfirmationText($storeId);


    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @param array $data
     * @return void
     */
    public function confirmShipping(\Mirasvit\Rma\Api\Data\RmaInterface $rma, $data = []);
}