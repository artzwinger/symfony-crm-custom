define(function(require) {
    'use strict';

    const __ = require('orotranslation/js/translator');
    const BaseWidgetSetupView = require('orosidebar/js/app/views/base-widget/base-widget-setup-view');

    const AssignedBidsSetupView = BaseWidgetSetupView.extend({
        template: require('tpl-loader!teachersbid/templates/sidebar-widget/assigned-bids/assigned-bids-setup-view.html'),

        widgetTitle: function() {
            return __('teachers.bid.assigned_bids_widget.settings');
        },

        /**
         * @inheritDoc
         */
        constructor: function AssignedBidsSetupView(options) {
            AssignedBidsSetupView.__super__.constructor.call(this, options);
        },

        validation: {
            perPage: {
                NotBlank: {},
                Regex: {pattern: '/^\\d+$/'},
                Number: {min: 1, max: 20}
            }
        },

        fetchFromData: function() {
            const data = AssignedBidsSetupView.__super__.fetchFromData.call(this);
            data.perPage = Number(data.perPage);
            return data;
        }
    });

    return AssignedBidsSetupView;
});
