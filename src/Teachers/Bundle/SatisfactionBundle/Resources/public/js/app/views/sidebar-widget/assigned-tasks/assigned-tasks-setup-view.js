define(function(require) {
    'use strict';

    const __ = require('orotranslation/js/translator');
    const BaseWidgetSetupView = require('orosidebar/js/app/views/base-widget/base-widget-setup-view');

    const AssignedSatisfactionsSetupView = BaseWidgetSetupView.extend({
        template: require('tpl-loader!teacherssatisfaction/templates/sidebar-widget/assigned-satisfactions/assigned-satisfactions-setup-view.html'),

        widgetTitle: function() {
            return __('teachers.satisfaction.assigned_satisfactions_widget.settings');
        },

        /**
         * @inheritDoc
         */
        constructor: function AssignedSatisfactionsSetupView(options) {
            AssignedSatisfactionsSetupView.__super__.constructor.call(this, options);
        },

        validation: {
            perPage: {
                NotBlank: {},
                Regex: {pattern: '/^\\d+$/'},
                Number: {min: 1, max: 20}
            }
        },

        fetchFromData: function() {
            const data = AssignedSatisfactionsSetupView.__super__.fetchFromData.call(this);
            data.perPage = Number(data.perPage);
            return data;
        }
    });

    return AssignedSatisfactionsSetupView;
});
