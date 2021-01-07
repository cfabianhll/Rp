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



namespace Mirasvit\Rma\Api\Config;


interface RmaPolicyConfigInterface
{
    /**
     * @param null|int $store
     * @return array
     */
    public function getAllowRmaInOrderStatuses($store = null);

    /**
     * @param null|int $store
     * @return int
     */
    public function getReturnPeriod($store = null);

    /**
     * @param null|int $store
     * @return bool
     */
    public function isAllowRmaRequestOnlyShipped($store = null);

    /**
     * @param null|int $store
     * @return bool
     */
    public function isAllowMultipleOrders($store = null);

    /**
     * @param null|int $store
     * @return bool|int
     */
    public function getIsActive($store = null);

    /**
     * @param null|int $store
     * @return string
     */
    public function getPolicyBlock($store = null);
}

