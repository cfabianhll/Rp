<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\WebposStripe\Model;

use Zxing\NotFoundException;
use Magento\Framework\App\ObjectManager;

/**
 * Webpos Stripe - Model Stripe
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Stripe implements \Magestore\WebposStripe\Api\StripeInterface
{
    const PAYMENT_METHOD = 'stripe';

    /**
     * @var \Magestore\WebposStripe\Helper\Data
     */
    protected $helper;

    /**
     * Stripe constructor.
     *
     * @param \Magestore\WebposStripe\Helper\Data $helper
     */
    public function __construct(
        \Magestore\WebposStripe\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function validateRequiredSDK()
    {
        return (class_exists(\Stripe\Stripe::class) && class_exists(\Stripe\Charge::class))
            ? true
            : false;
    }

    /**
     * @inheritDoc
     */
    public function getConfig($key = '')
    {
        $configs = $this->helper->getStripeConfig();
        return ($key) ? $configs[$key] : $configs;
    }

    /**
     * Complete Payment
     *
     * @param string $token
     * @param string $amount
     * @return string
     * @throws \Exception
     */
    public function completePayment($token, $amount)
    {
        $response = '';
        $transactionId = '';
        if ($amount && $token) {
            $storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Store\Model\StoreManagerInterface::class);
            $currency = $storeManager->getStore()->getBaseCurrencyCode();
            $secretKey = $this->helper->getSecretKey();
            $cents = 100;
            if ($this->helper->isZeroDecimal($currency)) {
                $cents = 1;
            }
            $amount = $amount * $cents;
            try {
                \Stripe\Stripe::setApiKey($secretKey); // phpstan:ignore
                $response = \Stripe\Charge::create( // phpstan:ignore
                    [
                        "amount" => $amount,
                        "currency" => $currency,
                        "source" => $token,
                        "description" => __('Charge for POS')
                    ]
                );
            } catch (\Exception $e) {
                if ($e->getHttpStatus() == 401) {
                    throw new \Magento\Framework\Exception\StateException(
                        __('Connection failed. Please contact admin to check the configuration of API.')
                    );
                }
                throw new \Magento\Framework\Exception\StateException(
                    __($e->getMessage())
                );
            }
        }
        if ($response) {
            if (isset($response['id'])) {
                $transactionId = $response['id'];
            }
        } else {
            throw new \Magento\Framework\Exception\StateException(
                __('Transaction is failed')
            );
        }
        return $transactionId;
    }

    /**
     * Test connect stripe API
     *
     * @return bool
     */
    public function canConnectToApi()
    {
        \Stripe\Stripe::setApiKey($this->helper->getSecretKey()); // phpstan:ignore
        $connected = true;
        try {
            $this->testCreate();
        } catch (\Exception $e) {
            $connected = false;
        }
        return $connected;
    }

    /**
     * Test create stripe payment
     */
    public function testCreate()
    {
        $card = [
            'number' => '4242424242424242',
            'exp_month' => 5,
            'exp_year' => date('Y') + 1
        ];

        \Stripe\Charge::create( // phpstan:ignore
            [
                'amount' => 100,
                'currency' => 'usd',
                'card' => $card
            ]
        );
    }

    /**
     * CreateToken
     *
     * @param array $params
     * @return mixed|null|string
     * @throws \Exception
     */
    public function createToken($params)
    {
        \Stripe\Stripe::setApiKey($this->helper->getSecretKey()); // phpstan:ignore
        try {
            $token = \Stripe\Token::create($params); // phpstan:ignore

            if (empty($token['id']) || strpos($token['id'], 'tok_') !== 0) {
                throw new NotFoundException(
                    __('Sorry, this payment method can not be used at the moment. Try again later.')
                );
            }

            return $token['id'];
        } catch (\Stripe\Error\Card $e) { // phpstan:ignore
            throw new NotFoundException(__($e->getMessage()));
        }
    }

    /**
     * Place Order StripeCard
     *
     * @param array $additionalData
     * @param float $amount
     * @return string|float|int|null
     * @throws \Exception
     */
    public function placeOrderStripeCard($additionalData, $amount)
    {
        $params = [
            "card" => [
                "name" => $additionalData['cc_owner'],
                "number" => $additionalData['cc_number'],
                "cvc" => $additionalData['cc_cid'],
                "exp_month" => $additionalData['cc_exp_month'],
                "exp_year" => $additionalData['cc_exp_year']
            ]
        ];

        $token = $this->createToken($params);
        if ($token) {
            return $this->completeAppPayment($token, $amount); // phpstan:ignore
        } else {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function refundPaymentCharge($request)
    {
        try {
            \Stripe\Stripe::setApiKey($this->helper->getSecretKey()); // phpstan:ignore

            $data = $request->getData();
            $data['charge'] = $request->getPaymentChargeId();
            unset($data['payment_charge_id']); // phpstan:ignore

            $response = \Stripe\Refund::create($data);
            /**
             * @var \Magestore\WebposStripe\Api\Data\RefundPaymentChargeResponseInterface $responseData
             */
            $responseData = ObjectManager::getInstance()
                ->create(\Magestore\WebposStripe\Api\Data\RefundPaymentChargeResponseInterface::class);

            if ($response instanceof \Stripe\StripeObject) { // phpstan:ignore
                $responseData->setData($response->toArray());
                return $responseData;
            }

            return $responseData;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __($e->getMessage())
            );
        }
    }
}
