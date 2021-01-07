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


interface RmaRequirementConfigInterface
{

    const RMA_CUSTOMER_REQUIRES_REASON = 'reason';
    const RMA_CUSTOMER_REQUIRES_CONDITION = 'condition';
    const RMA_CUSTOMER_REQUIRES_RESOLUTION = 'resolution';

    /**
     * @param null|int $store
     * @return string
     */
    public function getGeneralCustomerRequirement($store = null);

    /**
     * @param null|int $store
     * @return bool
     */
    public function isCustomerReasonRequired($store = null);

    /**
     * @param null|int $store
     * @return bool
     */
    public function isCustomerConditionRequired($store = null);

    /**
     * @param null|int $store
     * @return bool
     */
    public function isCustomerResolutionRequired($store = null);
}