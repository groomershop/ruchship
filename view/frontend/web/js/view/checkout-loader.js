define([
    'rjsResolver',
    'ruch_shipping'
], function (resolver, ruch_shipping) {
    'use strict';

    return function (target) {

        function hideLoader($loader) {
            $loader.parentNode.removeChild($loader);
            if(ruch_is_active) ruch_shipping.MageRuchInit();
        }

        target = function (config, $loader) {
            resolver(hideLoader.bind(null, $loader));
        };

        return target;
    }

});
