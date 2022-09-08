<?php

namespace MagePsycho\RegionCityPro\Plugin\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use MagePsycho\RegionCityPro\Helper\Data as RegionCityProHelper;
use MagePsycho\RegionCityPro\Api\Data\CityInterface;
use Magento\Checkout\Helper\Data as CheckoutDataHelper;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LayoutProcessorPlugin
{
    const CITY_ID_SORT_ORDER_OLD = 81;
    const CITY_ID_SORT_ORDER_NEW = 91;

    /**
     * @var CheckoutDataHelper
     */
    protected $checkoutHelper;

    /**
     * @var RegionCityProHelper
     */
    protected $regionCityProHelper;

    /**
     * @var int
     */
    private $cityIdOrder;

    public function __construct(
        CheckoutDataHelper $checkoutDataHelper,
        RegionCityProHelper $regionCityProHelper
    ) {
        $this->checkoutHelper = $checkoutDataHelper;
        $this->regionCityProHelper = $regionCityProHelper;
    }

    public function afterProcess(
        LayoutProcessor $subject,
        array $jsLayout
    ) {
        if ($this->regionCityProHelper->isFxnSkipped()) {
            return $jsLayout;
        }

        // Add/update shipping address fields
        $renderOptions = $this->prepareRenderOptions();
        $jsLayout = $this->addShippingAddressCityIdField($jsLayout, $renderOptions);
        $jsLayout = $this->addShippingAddressCountryTemplate($jsLayout, $renderOptions);
        $jsLayout = $this->addShippingAddressRegionTemplate($jsLayout, $renderOptions);
        $jsLayout = $this->addShippingAddressCityVisibility($jsLayout);

        // Add/update billing address fields
        if ($this->checkoutHelper->isDisplayBillingOnPaymentMethodAvailable()) {
            $paymentForms = $jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']['payments-list']['children'];

            foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {
                $paymentMethodCode = str_replace('-form', '', $paymentMethodForm);
                if (! isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form'])) {
                    continue;
                }
                $componentConfig = [
                    'scope' => 'billingAddress' . $paymentMethodCode,
                    'customScope' => 'billingAddress' . $paymentMethodCode . '.custom_attributes',
                    'paymentNode' => 'payments-list',
                    'formNode' => $paymentMethodCode . '-form',
                ];
                $jsLayout = $this->addBillingAddressCityIdField($jsLayout, $componentConfig, $renderOptions);
                $jsLayout = $this->addBillingAddressCountryTemplate($jsLayout, $componentConfig, $renderOptions);
                $jsLayout = $this->addBillingAddressRegionTemplate($jsLayout, $componentConfig, $renderOptions);
                $jsLayout = $this->addBillingAddressCityVisibility($jsLayout, $componentConfig);
            }
        } else {
            $componentConfig = [
                'scope' => 'billingAddressshared',
                'customScope' => 'billingAddressshared',
                'paymentNode' => 'afterMethods',
                'formNode' => 'billing-address-form',
            ];
            $jsLayout = $this->addBillingAddressCityIdField($jsLayout, $componentConfig, $renderOptions);
            $jsLayout = $this->addBillingAddressCountryTemplate($jsLayout, $componentConfig, $renderOptions);
            $jsLayout = $this->addBillingAddressRegionTemplate($jsLayout, $componentConfig, $renderOptions);
            $jsLayout = $this->addBillingAddressCityVisibility($jsLayout, $componentConfig);
        }

        return $jsLayout;
    }

    private function addShippingAddressCityIdField($jsLayout, $renderOptions = [])
    {
        $cityIdField = [
            'component' => 'MagePsycho_RegionCityPro/js/form/element/city',
            'config'    => [
                'customScope'   => 'shippingAddress.custom_attributes',
                'customEntry'   => 'shippingAddress.city',
                'template'      => 'ui/form/field',
                'elementTmpl'   => 'ui/form/element/select',
            ],
            'label' => __('City'),
            //'value' => '',
            'dataScope' => 'shippingAddress.custom_attributes.' . CityInterface::ID,
            'provider' => 'checkoutProvider',
            'sortOrder' => $renderOptions['sortOrder'],
            'customEntry' => null,
            'visible' => true,
            'options' => [],
            'validation' => [
                'required-entry' => true,
            ],
            'filterBy'  => [
                'target' => '${ $.provider }:shippingAddress.region_id',
                'field'  => 'region_id'
            ],
            'imports'   => [
                'initialOptions'    => 'index = checkoutProvider:dictionaries.' . CityInterface::ID,
                'setOptions'        => 'index = checkoutProvider:dictionaries.' . CityInterface::ID
            ]
        ];
        if ($renderOptions['searchable_city']) {
            $cityIdField['config']['elementTmpl'] = 'MagePsycho_RegionCityPro/form/element/select2';
        }

        $jsLayout['components']['checkout']['children']['steps']['children']
        ['shipping-step']['children']['shippingAddress']['children']
        ['shipping-address-fieldset']['children'][CityInterface::ID] = $cityIdField;
        return $jsLayout;
    }

    private function addShippingAddressCityVisibility($jsLayout)
    {
        if (isset(
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['city']
        )) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']
            ['children']['city']['visible'] = false;
        }
        return $jsLayout;
    }

    private function addShippingAddressCountryTemplate($jsLayout, $renderOptions = [])
    {
        if ($renderOptions['searchable_country']) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['country_id']['component']
                = 'MagePsycho_RegionCityPro/js/form/element/region';
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['country_id']['config']['elementTmpl']
                = 'MagePsycho_RegionCityPro/form/element/select2';
        }
        return $jsLayout;
    }

    private function addShippingAddressRegionTemplate($jsLayout, $renderOptions = [])
    {
        if ($renderOptions['searchable_region']) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['region_id']['component']
                = 'MagePsycho_RegionCityPro/js/form/element/region';
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['region_id']['config']['elementTmpl']
                = 'MagePsycho_RegionCityPro/form/element/select2';
        }
        return $jsLayout;
    }

    private function addBillingAddressCityIdField($jsLayout, $componentConfig, $renderOptions = [])
    {
        $cityIdField = [
            'component' => 'MagePsycho_RegionCityPro/js/form/element/city',
            'config' => [
                'customScope'   => $componentConfig['customScope'],
                'customEntry'   => $componentConfig['scope'] . '.city',
                'template'      => 'ui/form/field',
                'elementTmpl'   => 'ui/form/element/select'
            ],
            'label' => __('City'),
            //'value' => '',
            'dataScope' =>  $componentConfig['customScope'] . '.' . CityInterface::ID,
            'provider' => 'checkoutProvider',
            'sortOrder' => $renderOptions['sortOrder'],
            'customEntry' => null,
            'visible' => true,
            'options' => [],
            'validation' => [
                'required-entry' => true,
            ],
            'filterBy' => [
                'target'    => '${ $.provider }:${ $.parentScope }.region_id',
                'field'     => 'region_id'
            ],
            'imports'   => [
                'initialOptions'    => 'index = checkoutProvider:dictionaries' . '.' . CityInterface::ID,
                'setOptions'        => 'index = checkoutProvider:dictionaries' . '.' . CityInterface::ID
            ]
        ];
        if ($renderOptions['searchable_city']) {
            $cityIdField['config']['elementTmpl'] = 'MagePsycho_RegionCityPro/form/element/select2';
        }

        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]['children']
        ['form-fields']['children'][CityInterface::ID] = $cityIdField;
        return $jsLayout;
    }

    private function addBillingAddressCityVisibility($jsLayout, $componentConfig)
    {
        if (isset(
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]
            ['children']['form-fields']['children']['city']
        )) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]
            ['children']['form-fields']['children']['city']['visible'] = false;
        }
        return $jsLayout;
    }

    private function addBillingAddressCountryTemplate($jsLayout, $componentConfig, $renderOptions = [])
    {
        if ($renderOptions['searchable_country']) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]
            ['children']['form-fields']['children']['country_id']['component']
                = 'MagePsycho_RegionCityPro/js/form/element/region';

            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]
            ['children']['form-fields']['children']['country_id']['config']['elementTmpl']
                = 'MagePsycho_RegionCityPro/form/element/select2';
        }

        return $jsLayout;
    }

    private function addBillingAddressRegionTemplate($jsLayout, $componentConfig, $renderOptions = [])
    {
        if ($renderOptions['searchable_region']) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]
            ['children']['form-fields']['children']['region_id']['component']
                = 'MagePsycho_RegionCityPro/js/form/element/region';

            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]
            ['children']['form-fields']['children']['region_id']['config']['elementTmpl']
                = 'MagePsycho_RegionCityPro/form/element/select2';
        }

        return $jsLayout;
    }

    private function prepareRenderOptions()
    {
        return [
            'sortOrder' => $this->getCityIdSortOrder(),
            'searchable_city' => $this->regionCityProHelper->getConfigHelper()->isCitySearchable(),
            'searchable_country' => $this->regionCityProHelper->getConfigHelper()->isCountrySearchable(),
            'searchable_region' => $this->regionCityProHelper->getConfigHelper()->isRegionSearchable(),
        ];
    }

    private function getCityIdSortOrder()
    {
        if ($this->cityIdOrder) {
            return $this->cityIdOrder;
        }

        $sortOrder = $this->regionCityProHelper->getConfigHelper()->getCitySortOrder();
        if (!$sortOrder) {
            $sortOrder = version_compare($this->regionCityProHelper->getMageVersion(), '2.4.0', '<')
                ? self::CITY_ID_SORT_ORDER_OLD : self::CITY_ID_SORT_ORDER_NEW;
        }
        $this->cityIdOrder = $sortOrder;
        return $this->cityIdOrder;
    }
}
