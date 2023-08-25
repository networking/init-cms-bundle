
const App = {
    init(subject) {
        this.setup_xeditable(subject);
    },
    create_xeditable(element, options, saveCallback, hiddenCallback) {

        let subject = $(element)
        subject.unbind()
        subject.editable(options)
            .on('contextmenu', function (e) {
                $(this).editable('show');
                e.preventDefault();
            })
            .on('save', saveCallback)
            .on('hidden', hiddenCallback)


        return subject
    },
    setup_xeditable(subject, cb) {



        $('.x-editable', subject).editable({
            emptyclass: 'editable-empty btn btn-sm btn-default',
            emptytext: '<i class="fas fa-pencil-alt"></i>',
            container: 'body',
            placement: 'auto',
            params: function(params) {
                // make sure pk is always loaded from element
                params.pk = jQuery(this).attr('data-pk');

                if(this.dataset.xEditableParams){
                    let extraParams = JSON.parse(this.dataset.xEditableParams);
                    extraParams.forEach(function (param) {
                        Object.entries(param).forEach(([key, value]) => {
                            params[key] = value;
                        })
                    });
                }
                return params;
            },
            success(response) {

                if(cb){
                    return cb(response);
                }

                if(response instanceof Object && response.pk){
                    jQuery(this).attr('data-pk', response.pk);
                    return response;
                }

                const html = jQuery(response);
                this.setup_xeditable(html);
                jQuery(this).closest('td').replaceWith(html);
            },
            error: (xhr) => {
                // On some error responses, we return JSON.
                if (xhr.getResponseHeader('Content-Type') === 'application/json') {
                    return JSON.parse(xhr.responseText);
                }

                return xhr.responseText;
            },
        });
    },


}

KTUtil.onDOMContentLoaded(() => {
    App.init(document.body);
});


window.CMSApp = App;

