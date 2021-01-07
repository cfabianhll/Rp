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
 * @package   mirasvit/module-credit
 * @version   1.0.79
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Credit\Plugin\Aheadworks\OneStepCheckout\Block\Checkout;

class ModifyJsLayoutPlugin
{
    /**
     * @param \Aheadworks\OneStepCheckout\Block\Checkout $subject
     * @param \callable                                  $proceed
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetJsLayout(
        \Aheadworks\OneStepCheckout\Block\Checkout $subject, $proceed
    ) {
        $result = $proceed();

        $data = \Zend_Json::decode($result);
        $data['components']['checkout']['children']['payment-options']['children']['credit-form'] = [
            'component' => 'Mirasvit_Credit/js/view/payment/credit',
            'config'    => [
                'template' => 'Mirasvit_Credit/checkout/aheadworks/credit',
            ],
            'children'  => [
                'errors' => [
                    'sortOrder'   => 0,
                    'component'   => 'Mirasvit_Credit/js/view/payment/messages',
                    'displayArea' => 'messages',
                ],
            ],
        ];
        $result = \Zend_Json::encode($data);

        return $result;
    }
}