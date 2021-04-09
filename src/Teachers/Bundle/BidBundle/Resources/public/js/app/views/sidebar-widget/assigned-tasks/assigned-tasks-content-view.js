define(function(require) {
    'use strict';

    const $ = require('jquery');
    const mediator = require('oroui/js/mediator');
    const routing = require('routing');
    const LoadingMask = require('oroui/js/app/views/loading-mask-view');
    const BaseView = require('oroui/js/app/views/base/view');
    const template =
        require('tpl-loader!teachersbid/templates/sidebar-widget/assigned-bids/assigned-bids-content-view.html');

    const AssignedBidsContentView = BaseView.extend({
        defaultPerPage: 5,

        template: template,

        events: {
            'click .bid-widget-row': 'onClickBid'
        },

        listen: {
            refresh: 'reloadBids'
        },

        /**
         * @inheritDoc
         */
        constructor: function AssignedBidsContentView(options) {
            AssignedBidsContentView.__super__.constructor.call(this, options);
        },

        render: function() {
            this.reloadBids();
            return this;
        },

        onClickBid: function(event) {
            const bidUrl = $(event.currentTarget).data('url');
            mediator.execute('redirectTo', {url: bidUrl});
        },

        reloadBids: function() {
            const view = this;
            const settings = this.model.get('settings');
            settings.perPage = settings.perPage || this.defaultPerPage;

            const routeParams = {
                perPage: settings.perPage,
                r: Math.random()
            };
            const url = routing.generate('teachers_bid_widget_sidebar_bids', routeParams);

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

    return AssignedBidsContentView;
});
