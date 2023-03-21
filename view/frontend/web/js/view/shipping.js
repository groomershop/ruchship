define(
    [
        'jquery',
        'underscore',
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'uiRegistry',
        'mage/translate',
        'Magento_Checkout/js/model/shipping-rate-service',
        'Magento_Ui/js/modal/alert',
        'ruch_leaflet',
        'ruch_leaflet_lib1',
        'ruch_leaflet_lib2',
        'ruch_shipping'
    ],function (
        $,
        _,
        Component,
        ko,
        customer,
        addressList,
        addressConverter,
        quote,
        createShippingAddress,
        selectShippingAddress,
        shippingRatesValidator,
        formPopUpState,
        shippingService,
        selectShippingMethodAction,
        rateRegistry,
        setShippingInformationAction,
        stepNavigator,
        modal,
        checkoutDataResolver,
        checkoutData,
        registry,
        $t,
        rateServce,
        alert,
        rWidget_leaflet,
        rWidget_leaflet1,
        rWidget_leaflet2,
        ruch_shipping
        ) {
    'use strict';

    if(!ruch_is_active) {
        return function (target) {
            return target;
        }
    }

    var mixin = {

        selectShippingMethod: function (shippingMethod) {
            this._super(shippingMethod);
            ruch_shipping.ruchWidget(shippingMethod, $);
            return true;
        },
        
        setShippingInformation: function() { 
            var shippingMethod = quote.shippingMethod();
            if((shippingMethod != null) && (shippingMethod.carrier_code == 'ruch')) {
    		    var needWidget = false;
	            if(!ruch_pkt) {
	                this.errorValidationMessage($t('Wybrana metoda dostawy wymaga określenia punktu odbioru.'));
	                needWidget = true;                                           
	            }
	            if((ruch_cod == '1') && !ruch_pkt.c) {
	                this.errorValidationMessage($t('Wybrana metoda dostawy wymaga punktu odbioru obsługującego płatność przy odbiorze.'));
	                needWidget = true;                                           
	            }
	            if(needWidget) {
	                ruch_shipping.ruchWidget(shippingMethod, $);
	                return false;
	            }
        	}
        	this._super();
        },
        
        ruchInit: function() {
            ruch_shipping.ruchInit();
        }
        
    };

    return function (target) {
    	return target.extend(mixin);
    };

});
