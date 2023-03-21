var config = {
    config: {
	    mixins: {
			'Magento_Checkout/js/view/shipping': {
				'Magento_RuchShip/js/view/shipping': true
			},
			'Magento_Checkout/js/checkout-loader': {
                'Magento_RuchShip/js/view/checkout-loader': true
            },
			
		}
    },
    paths: {
        'ruch_leaflet': 'Magento_RuchShip/js/leaflet',
        'ruch_leaflet_lib1': 'Magento_RuchShip/js/leaflet.markercluster',
        'ruch_leaflet_lib2': 'Magento_RuchShip/js/bundle.min'
    },
    map: {
        '*': {
            'ruch_shipping': 'Magento_RuchShip/js/view/ruch',
            'Magento_Checkout/template/shipping-address/shipping-method-list.html': 'Magento_RuchShip/template/shipping-address/shipping-method-list.html'
        }
    },
    shim: {
        'ruch_leaflet': {
            'exports': 'LRW'
        },
        'ruch_leaflet_lib1': {
            'deps': ['ruch_leaflet']
        },
        'ruch_leaflet_lib2': {
            'exports': 'GeoSearch',
            'deps': ['ruch_leaflet']
        }
    }
}
