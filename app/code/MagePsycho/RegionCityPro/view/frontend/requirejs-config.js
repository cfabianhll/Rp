var config = {
	map: {
		'*': {
			cityUpdater: 'MagePsycho_RegionCityPro/js/city-updater',
			select2: 'MagePsycho_RegionCityPro/js/select2.min'
		}
	},
	config: {
		mixins: {
            'Magento_Checkout/js/action/create-shipping-address': {
                'MagePsycho_RegionCityPro/js/action/create-shipping-address-mixin': true
            },
            'Magento_Checkout/js/action/select-shipping-address' : {
                'MagePsycho_RegionCityPro/js/action/select-shipping-address-mixin': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
				'MagePsycho_RegionCityPro/js/action/set-shipping-information-mixin': true
			},
            'Magento_Checkout/js/action/select-billing-address' : {
                'MagePsycho_RegionCityPro/js/action/select-billing-address-mixin': true
            },
            'Magento_Checkout/js/action/set-payment-information': {
                'MagePsycho_RegionCityPro/js/action/set-payment-information-mixin': true
            },
            'Magento_Checkout/js/view/billing-address': {
                'MagePsycho_RegionCityPro/js/view/billing-address-mixin': true
            },
            'Magento_Checkout/js/view/shipping-address/address-renderer/default': {
                'MagePsycho_RegionCityPro/js/view/shipping-address/address-renderer/default-mixin': true
            },
            'Magento_Checkout/js/view/shipping-information/address-renderer/default': {
                'MagePsycho_RegionCityPro/js/view/shipping-information/address-renderer/default-mixin': true
            }
		}
	}
};
