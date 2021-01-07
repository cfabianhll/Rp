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


namespace Mirasvit\Rma\Plugin\Report;

use Mirasvit\Rma\Service\Report\Reason;
use Mirasvit\Rma\Service\Report\Resolution;
use Mirasvit\Rma\Service\Report\Condition;

class ReasonConfigMapPlugin
{
    /**
     * @var Condition
     */
    private $condition;
    /**
     * @var Resolution
     */
    private $resolution;
    /**
     * @var Reason
     */
    private $reason;

    /**
     * ReasonConfigMapPlugin constructor.
     * @param Reason $reason
     * @param Resolution $resolution
     * @param Condition $condition
     */
    public function __construct(
        Reason $reason,
        Resolution $resolution,
        Condition $condition
    ) {
        $this->reason = $reason;
        $this->resolution = $resolution;
        $this->condition = $condition;
    }

    /**
     * @return void
     */
    public function afterLoad()
    {
        $this->reason->add(__('Reason'));
        $this->resolution->add(__('Resolution'));
        $this->condition->add(__('Condition'));
    }
}