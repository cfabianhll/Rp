<?php

namespace Magestore\Customercredit\Plugin\Rewardpoints\Model\Total\Quote;

/**
 * Add store credit as a ignoring product
 */
class Earning
{
    /**
     * Add store credit as a ignoring product
     *
     * @param \Magestore\Rewardpoints\Model\Total\Quote\Earning $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetIgnoringProducts(
        \Magestore\Rewardpoints\Model\Total\Quote\Earning $subject,
        $result
    ) {
        $result[] = \Magestore\Customercredit\Model\Product\Type::TYPE_CODE;
        return $result;
    }
}
