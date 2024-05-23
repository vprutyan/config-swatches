/**
 * Copyright © Brander, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {

        $.widget('mage.SwatchRenderer', widget, {
            _getSelectedAttributes: function () {

                let isSwatchPreselectEnabled = typeof this.options.jsonConfig.isSwatchPreselectEnabled !== 'undefined' &&
                this.options.jsonConfig.isSwatchPreselectEnabled ? 1 : 0;

                if (!isSwatchPreselectEnabled) {
                    this._super();
                } else {
                    return typeof this.options.jsonConfig.preselected_swatches !== 'undefined' ?
                        this.options.jsonConfig.preselected_swatches :
                        {};
                }
            }
        });

        return $.mage.SwatchRenderer;
    };
});
