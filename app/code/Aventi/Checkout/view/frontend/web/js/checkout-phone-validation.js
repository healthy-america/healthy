define([
    'jquery'
], function ($) {
    'use strict';

    function applyPhoneValidation(selector) {
        $(document).on('input', selector, function () {
            // Remove non-digit characters
            this.value = this.value.replace(/\D/g, '');

            // Limit to 10 digits
            if (this.value.length > 10) {
                this.value = this.value.substring(0, 10);
            }
        });
    }

    return function (Component) {
        return Component.extend({
            initialize: function () {
                this._super();

                // Apply validation to shipping telephone field
                applyPhoneValidation('input[name="telephone"]');

                // Apply validation to billing telephone field (depending on the payment method, the name may vary)
                applyPhoneValidation('input[name="billingAddress.telephone"]');

                return this;
            }
        });
    };
});
