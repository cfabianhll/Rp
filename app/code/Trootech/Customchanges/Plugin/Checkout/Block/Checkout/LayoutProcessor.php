<?php
namespace Trootech\Customchanges\Plugin\Checkout\Block\Checkout;
class LayoutProcessor
{
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {        
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['identification_card'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [],
                'id' => 'identification_card'
            ],
            'dataScope' => 'shippingAddress.custom_attributes.identification_card',
            'label' => 'Ciudad',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'sortOrder' => 80,
            'id' => 'identification_card'
        ];
 
        unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode']);

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['customer-email']
        ['children']['street[0]']['sortOrder'] = 50;

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children']['street[0]']['sortOrder'] = 50;


        // unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        // ['children']['shippingAddress']['children']['shipping-address-fieldset']
        // ['children']['city']);

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['corregimiento'] = [
            'component' => 'Trootech_Townshipcitymapping/js/view/form/element/corregimiento',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes.corregimiento',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'id' => 'corregimiento'
            ],
            'dataScope' => 'shippingAddress.custom_attributes.corregimiento',
            'label' => __('Corregimiento'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'options' => [
                [
                    'value' => '',
                    'label' => 'Corregimiento',
                ]
            ],
            'validation' => [
                'required-entry' => true
            ],
            'sortOrder' => 111,
            'id' => 'corregimiento'
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['region_id']['sortOrder'] = 90;
        return $jsLayout;
    }
}