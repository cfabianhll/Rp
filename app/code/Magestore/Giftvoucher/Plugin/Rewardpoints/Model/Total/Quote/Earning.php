<?php

namespace Magestore\Giftvoucher\Plugin\Rewardpoints\Model\Total\Quote;

/**
 * Add gift card as a ignoring product
 */
class Earning
{
    /**
     * Add gift card as a ignoring product
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
        $result[] = \Magestore\Giftvoucher\Model\Product\Type\Giftvoucher::GIFT_CARD_TYPE;
        return $result;
    }
}
