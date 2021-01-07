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



namespace Mirasvit\Rma\Api\Service\Resolution;

use Mirasvit\Rma\Api\Data\RmaInterface;

interface ResolutionManagementInterface
{
    /**
     * @param string $code
     * @return array
     */
    public function getResolutionByCode($code);

    /**
     * @param RmaInterface $rma
     * @return bool
     */
    public function isExchangeAllowed(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return bool
     */
    public function isReplacementAllowed(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return bool
     */
    public function isCreditmemoAllowed(RmaInterface $rma);
}