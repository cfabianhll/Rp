<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\WebposStripe\Api;

/**
 * Interface - Stripe Interface
 */
interface StripeInterface
{
    /**
     * Validate Required SDK
     *
     * @return bool
     */
    public function validateRequiredSDK();

    /**
     * Get Config
     *
     * @param string $key
     * @return array
     */
    public function getConfig($key = '');

    /**
     * API refund payment intent
     *
     * @param  \Magestore\WebposStripe\Api\Data\RefundPaymentChargeRequestInterface $request
     *
     * @return \Magestore\WebposStripe\Api\Data\RefundPaymentChargeResponseInterface
     * @throws \Magento\Framework\Exception\StateException
     */
    public function refundPaymentCharge($request);
}
