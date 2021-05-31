define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const mediator = require('oroui/js/mediator');
    const options = {
        successMessage: 'teachers.invoice.button.send_email.success.message',
        errorMessage: 'teachers.invoice.button.send_email.error.message'
    };

    function onClick(e) {
        e.preventDefault();

        const url = $(e.target).data('url');
        mediator.execute('showLoading');
        $.post({
            url: url,
            errorHandlerMessage: __(options.errorMessage)
        }).done(function() {
            mediator.execute('showFlashMessage', 'success', __(options.successMessage));
        }).always(function() {
            mediator.execute('hideLoading');
        });
    }

    return function(additionalOptions) {
        _.extend(options, additionalOptions || {});
        const button = options._sourceElement;
        button.click(onClick);
    };
});
