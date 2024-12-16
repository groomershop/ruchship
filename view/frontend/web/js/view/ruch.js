var ruch_widget_loaded = false;
var ruch_widget_started = false;
var ruch_wid;
var ruch_cod;
var ruch_cod_shown = -1;
var ruch_c;
var ruch_pkt = null;
var ruch_pkt_tmp = null;
var ruch_jq;
var ruch_sandbox = null;

define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/checkout-data',
        'mage/translate',
        'Magento_Ui/js/modal/alert',
        'ruch_leaflet',
        'ruch_leaflet_lib1',
        'ruch_leaflet_lib2'
    ],function (
        $,
        quote,
        shippingService,
        checkoutData,
        $t,
        fn_alert,
        rWidget_leaflet,
        rWidget_leaflet1,
        rWidget_leaflet2
        ) {
    'use strict';

    window.LRW = rWidget_leaflet;
    window.GeoSearch = rWidget_leaflet2;

    return {

		ruchInit: function() {
		    var _this = this;
		    if(ruch_widget_loaded) {
		        _this.renderRuchHtml();
		        return true;
		    }
		    if(checkoutConfig.quoteData.ruch_desc) {
		        var pkt = JSON.parse(checkoutConfig.quoteData.ruch_desc);
		        pkt['p'] = checkoutConfig.quoteData.ruch_point;
		        pkt['t'] = checkoutConfig.quoteData.ruch_type;
		        pkt['id'] = checkoutConfig.quoteData.ruch_destinationcode;
		        ruch_pkt = pkt;
		    }
		    $.ajax({
		        success: function() {
		            ruch_widget_loaded = true;
		        },
		        url: ruch_widget_url,
		        dataType: "script",
		        cache: true,
		        showLoader: true,
		        complete: function() {
		            _this.renderRuchHtml();
		        }
		    }).fail(function (jqXHR, textStatus, errorThrown) {
		            console.log('status=' + textStatus + ", error=" + errorThrown);
		            fn_alert({title: 'Orlen Paczka', content: 'Błąd wczytywania mapy'});
		    });
		    
		    return true;
		},
	
	    renderRuchHtml: function() {
            if(ruch_widget_loaded) {
                if(!$('#ruch_widget_sel').length) {
                    $('#label_carrier_95_0_ruch,#label_carrier_95_1_ruch').html('<div id="ruch_wrapper" style="display: none;"><div id="ruch_widget_sel"></div><div><button class="ruch_sel_point_button" type="button">Wybierz punkt odbioru</button></div></div>');
                    if(ruch_pkt) {
                        $('#ruch_widget_sel').html('<p>Wybrany punkt:</p><p>' + ruch_pkt.r + ' ' + ruch_pkt.p + ' ' + ruch_pkt.a + '</p>');
			    
			const shippingPointIdInput = ruch_jq(
			    '[name="swissup_checkout_field[shipping_point_id]"]'
			);
			if (shippingPointIdInput !== null) {
			    shippingPointIdInput.value = ruch_pkt.id;
			}
			        
			const shippingPointNameInput = ruch_jq(
			    '[name="swissup_checkout_field[shipping_point_name]"]'
			);
			if (shippingPointNameInput !== null) {
			    shippingPointNameInput.value = ruch_pkt.r + ' ' + ruch_pkt.p + ' ' + ruch_pkt.a;
			}
                    }
                    $('#ruch_wrapper').slideDown(250);
                }
            }
	    },

		MageRuchInit: function() {
		    var _this = this;
		    shippingService.isLoading.subscribe(function (isLoading) {
		        if(!isLoading) {
		            _this.ruchInit();
		        }
		    });
		    _this.ruchInit();
		},
	
		ruchWidget: function(shippingMethod, $) {
		    var _this = this;
		    var elem = document.getElementById('ruch_widget');
		    if(!ruch_widget_loaded) return;
		    if((shippingMethod != null) && (shippingMethod.carrier_code == 'ruch')) {
		        var tmp = shippingMethod.method_code.split('_');
		        ruch_cod = tmp[1];
		        ruch_c = [shippingMethod.amount, shippingMethod.amount, shippingMethod.amount, shippingMethod.amount];
		        if(!elem) {
		            $('body').trigger('processStart');
		            var node1 = document.createElement("div");
		            node1.id = 'ruch_widget';
		            document.getElementById('checkout-shipping-ruch-init').appendChild(node1);
		            ruch_wid = new RuchWidget('ruch_widget',
		                {
		                    readyCb: _this.ruch_on_ready,
		                    selectCb: _this.ruch_on_select,
		                    initialAddress: '',
		                    sandbox: ruch_is_test,
							showPointTypeFilter: ruch_show_point_type_filter,
							showDeliveryFilter: ruch_show_delivery_filter,
							initialDelivery: ruch_delivery_filter_value,
							initialType: ruch_point_type_filter_value,
		                }, $
		            );
		            ruch_jq = $;
		            ruch_wid.init();
		            ruch_widget_started = true;
		            $('body').trigger('processStop');
		        }
		        else {
		            if(!ruch_wid.isVisible() || (ruch_cod_shown != ruch_cod)) {
			            ruch_wid.showWidget(
			                ruch_cod,
			                {
			                    'R': ruch_c[0],
			                    'P': ruch_c[1],
			                    'U': ruch_c[2],
			                    'A': ruch_c[3]
			                },
			                {
			                    'R': 'ruch_' + ruch_cod,
			                    'P': 'partner_' + ruch_cod,
			                    'U': 'partner_' + ruch_cod,
			                    'A': 'orlen_' + ruch_cod
			                }
			            );
			            ruch_cod_shown = ruch_cod;
		            }
		            $('html, body').animate({
		                scrollTop: $("#ruch_widget").offset().top
		            }, 1500);
		        };
		    }
		    else {
		        if(elem) {
		            ruch_wid.hideWidget();
		        }
		    }
		},
	        
		ruch_on_ready: function() {
		    ruch_wid.showWidget(
		        ruch_cod,
		        {
		            'R': ruch_c[0],
		            'P': ruch_c[1],
		            'U': ruch_c[2],
		            'A': ruch_c[3]
		        },
		        {
		            'R': 'ruch_' + ruch_cod,
		            'P': 'partner_' + ruch_cod,
		            'U': 'partner_' + ruch_cod,
		            'A': 'orlen_' + ruch_cod
		        }
		    );
		    ruch_cod_shown = ruch_cod;
		    $('html, body').animate({
		        scrollTop: $("#ruch_widget").offset().top
		    }, 1500);
		},
	
		ruch_on_select: function(pkt) {
		    if(pkt == null) return;
		    ruch_pkt_tmp = pkt;
		    ruch_jq.ajax({
		        url: '../ruch/ajax/select',
		        type: 'POST',
		        dataType: "json",
		        contentType: "application/json; charset=utf-8",
		        data: JSON.stringify(pkt),
		        success: function (data) {
		            if(data.status != 1) fn_alert({title: 'Orlen Paczka', content: 'Błąd komunikacji'});
		            else {
		                ruch_pkt = ruch_pkt_tmp;
		                ruch_jq('#ruch_widget_sel').html('<p>Wybrany punkt:</p><p>' + ruch_pkt.r + ' ' + ruch_pkt.p + ' ' + ruch_pkt.a + '</p>');

				const shippingPointIdInput = ruch_jq(
			            '[name="swissup_checkout_field[shipping_point_id]"]'
			        );
			        if (shippingPointIdInput !== null) {
			            shippingPointIdInput.value = ruch_pkt.id;
			        }
			        
			        const shippingPointNameInput = ruch_jq(
			            '[name="swissup_checkout_field[shipping_point_name]"]'
			        );
			        if (shippingPointNameInput !== null) {
			            shippingPointNameInput.value = ruch_pkt.r + ' ' + ruch_pkt.p + ' ' + ruch_pkt.a;
			        }

		            }
		        },
		        async: true,
		        showLoader: true
		    });
		}
    }

});
