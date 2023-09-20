define(['jquery'], function ($) {
    'use strict';

    return function (Select) {
        return Select.extend({
            initialize: function () {
                this._super();
                // Define your custom options here
                this.options([
                    { 'value': 'option1', 'label': 'Option 1' },
                    { 'value': 'option2', 'label': 'Option 2' },
                    // Add more options as needed
                ]);
            }
        });
    };
});
