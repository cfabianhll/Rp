<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\WebposStripe\Api;

/**
 * Interface - Stripe Service Interface
 */
interface StripeServiceInterface
{
    /**
     * Is Enable
     *
     * @return bool
     */
    public function isEnable();

    /**
     * Get Configuration Error
     *
     * @return string
     */
    public function getConfigurationError();

    /**
     * Can Connect To Api
     *
     * @return bool
     */
    public function canConnectToApi();

    /**
     * Finish Payment
     *
     * @param string $token
     * @param string $amount
     * @return string
     */
    public function finishPayment($token, $amount);

    /**
     * API refund payment intent
     *
     * @param  \Magestore\WebposStripe\Api\Data\RefundPaymentChargeRequestInterface $request
     *
     * @return \Magestore\WebposStripe\Api\Data\RefundPaymentChargeResponseInterface
     */
    public function refundPaymentCharge($request);
}
