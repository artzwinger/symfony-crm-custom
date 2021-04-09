define(function(require) {
    'use strict';

    const $ = require('jquery');
    const mediator = require('oroui/js/mediator');
    const routing = require('routing');
    const LoadingMask = require('oroui/js/app/views/loading-mask-view');
    const BaseView = require('oroui/js/app/views/base/view');
    const template =
        require('tpl-loader!teacherssatisfaction/templates/sidebar-widget/assigned-satisfactions/assigned-satisfactions-content-view.html');

    const AssignedSatisfactionsContentView = BaseView.extend({
        defaultPerPage: 5,

        template: template,

        events: {
            'click .satisfaction-widget-row': 'onClickSatisfaction'
        },

        listen: {
            refresh: 'reloadSatisfactions'
        },

        /**
         * @inheritDoc
         */
        constructor: function AssignedSatisfactionsContentView(options) {
            AssignedSatisfactionsContentView.__super__.constructor.call(this, options);
        },

        render: function() {
            this.reloadSatisfactions();
            return this;
        },

        onClickSatisfaction: function(event) {
            const satisfactionUrl = $(event.currentTarget).data('url');
            mediator.execute('redirectTo', {url: satisfactionUrl});
        },

        reloadSatisfactions: function() {
            const view = this;
            const settings = this.model.get('settings');
            settings.perPage = settings.perPage || this.defaultPerPage;

            const routeParams = {
                perPage: settings.perPage,
                r: Math.random()
            };
            const url = routing.generate('teachers_satisfaction_widget_sidebar_satisfactions', routeParams);

            const loadingMask = new LoadingMask({
                container: view.$el
            });
            loadingMask.show();

            $.get(url, function(content) {
                loadingMask.dispose();
                view.$el.html(view.template({content: content}));
            });
        }
    });

    return AssignedSatisfactionsContentView;
});
