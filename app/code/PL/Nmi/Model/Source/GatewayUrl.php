<?php
/**
 * PL Development.
 *
 * @category    PL
 * @author      Linh Pham <plinh5@gmail.com>
 * @copyright   Copyright (c) 2016 PL Development. (http://www.polacin.com)
 */
namespace PL\Nmi\Model\Source;

class GatewayUrl implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Possible actions on order place
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'https://secure.networkmerchants.com/api/transact.php',
                'label' => __('NMI'),
            ],
            [
                'value' => 'https://secure.paylinedatagateway.com/api/transact.php',
                'label' => __('Payline'),
            ],
            [
                'value' => 'https://paykings.transactiongateway.com/api/transact.php',
                'label' => __('Paykings'),
            ],
            [
                'value' => 'https://cxpay.transactiongateway.com/api/transact.php',
                'label' => __('CXPay'),
            ],
            [
                'value' => 'https://secure.payscapegateway.com/api/transact.php',
                'label' => __('Payscape'),
            ],
            [
                'value' => 'https://secure.skybankgateway.com/api/transact.php',
                'label' => __('SkyBank Financial'),
            ],
            [
                'value' => 'https://secure.t1paymentsgateway.com/api/transact.php',
                'label' => __('T1 Payments'),
            ],
            [
                'value' => 'https://secure.durango-direct.com/api/transact.php',
                'label' => __('Durango Merchant Services'),
            ],
            [
                'value' => 'https://secure.transactiongateway.com/api/transact.php',
                'label' => __('BlueDog'),
            ],
            [
                'value' => 'other_gateway_url',
                'label' => __('Other'),
            ]

        ];
    }
}