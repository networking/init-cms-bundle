var axiosConfig = {headers: {'X-Requested-With': 'XMLHttpRequest'}};

function OneManyAssociation(id, title, isList, updateUrl, formElementUrl) {
    var modalList = {
        dialog: false,
        dialogTitle: title,
        id: id,
        field: false,
        fieldWidget: false,
        isList: isList,
        updateUrl: updateUrl,
        formElementUrl: formElementUrl,
        init: function () {
            this.field = $('#' + this.id);
            this.fieldWidget = $('#field_widget_' + this.id);
            var that = this;
            if (this.isList) {
                this.field.on('change', function () {

                    that.logMessage('change', 'update label');

                    that.fieldWidget.html("<span><img src=\"/bundles/sonataadmin/ajax-loader.gif\" style=\"vertical-align: middle; margin-right: 10px\"/></span>");

                    var url = that.updateUrl.replace('OBJECT_ID', jQuery(this).val());

                    axios.get(url, axiosConfig).then(function (response) {
                        that.fieldWidget.html(response.data);
                    });

                });
            }
        },
        initializePopUp: function () {
            if (!this.dialog) {
                this.dialog = $('#field_dialog_' + id);
                $(document.body).append(this.dialog);
                this.logMessage('field_dialog', 'move dialog container as a document child');
            }
        },
        field_dialog_form_list: function (element) {
            this.initializePopUp();
            this.logMessage('field_dialog_form_list', 'open the list modal');
            this.dialog.html('');
            var that = this;
            axios.get($(element).attr('href'), axiosConfig)
                .then(function (response) {
                    that.logMessage('field_dialog_form_list', 'retrieving the list content');

                    // populate the popup container
                    that.createModalHtml(response.data)

                    that.field_dialog_form_list_handle_action();
                    that.dialog.modal('show');
                    that.dialog.on('hide.bs.modal', function (e) {
                        if (!jQuery(e.target).hasClass('collapse')) {
                            that.logMessage('field_dialog_form_list', 'dialog closed - removing `live` events');
                            // make sure we have a clean state
                            that.dialog.off('click', 'a');
                            that.dialog.off('submit', 'form');
                            that.dialog.html('');
                        }
                    });
                });

            return false;
        },
        field_dialog_form_list_handle_action: function () {

            this.logMessage('field_dialog_form_list_handle_action', 'attaching valid js event');

            this.dialog.off('click', 'a');
            this.dialog.off('submit', 'form');
            var that = this;
            this.dialog.on('click', 'a', function (event) {
                event.preventDefault();
                that.field_dialog_form_list_link(this);
            });
            this.dialog.on('submit', 'form', function (event) {
                event.preventDefault();
                that.field_dialog_form_list_submit(this);
            });
            Admin.add_filters();
        },
        field_dialog_form_list_link: function (element) {

            var link = $(element);

            //trigger javascript actions
            if (link.hasClass('filter-close') ||
                link.hasClass('dropdown-toggle') ||
                link.hasClass('has-action') ||
                link.hasClass('tag_link') ||
                link.hasClass('show_all_media')
            ) {
                this.logMessage('field_dialog_form_list_link', 'handle default javascript action')
                return;
            }

            //select media
            if (link.hasClass('select-media') || element.length > 0) {
                jQuery('#' + id).val(link.data('object-id')).trigger('change');
                this.dialog.modal('hide');
                return true;
            }

            event.preventDefault();
            event.stopPropagation();

            this.logMessage('field_dialog_form_list_link', 'handle link click in a list')
            var that = this;
            axios.get(link.attr('href'), axiosConfig)
                .then(function (response) {
                    that.logMessage('field_dialog_form_list_link', 'callback success, attach valid js event')
                    that.createModalHtml(response.data);
                    that.field_dialog_form_list_handle_action();
                });

        },
        field_dialog_form_list_submit: function (form) {
            this.logMessage('field_dialog_form_list_submit', 'catching submit event, sending ajax request')

            try {
                var axiosParams = getAxiosRequestConfig(form);
                var that = this;
                axios(axiosParams)
                    .then(function (response) {
                        that.logMessage('field_dialog_form_list_submit', 'form submit success, restoring event')
                        that.createModalHtml(response.data);
                        that.field_dialog_form_list_handle_action();
                    });
            } catch (e) {
                alert(e);
            }

        },
        field_dialog_form_add: function (element) {

            this.logMessage('field_dialog_form_add', 'add link action')

            event.preventDefault();
            event.stopPropagation();
            this.initializePopUp();

            var link = $(element);

            this.dialog.html('');

            var that = this;

            axios.get(link.attr('href'), axiosConfig)
                .then(function (response) {
                    that.logMessage('field_dialog_form_add', 'ajax success loading form');

                    // populate the popup container
                    that.dialog.html(response.data);

                    // capture the submit event to make an ajax call, ie : POST data to the
                    // related create admin
                    that.dialog.on('click', 'a', function (event) {
                        that.field_dialog_form_action(event, this);
                    });
                    that.dialog.on('submit', 'form', function (event) {
                        that.field_dialog_form_action(event, this);
                    });

                    // open the dialog in modal mode
                    that.dialog.modal('show');

                    that.dialog.on('hide.bs.modal', function (e) {
                        if (!$(e.target).hasClass('collapse')) {
                            that.logMessage('field_dialog_form_add', 'dialog closed - removing `live` events')
                            // make sure we have a clean state
                            that.dialog.off('click', 'a');
                            that.dialog.off('submit', 'form');
                            that.dialog.html('');
                        }
                    });
                })
        },
        field_dialog_form_action: function (event, clickElement) {

            var that = this;
            that.logMessage('field_dialog_form_action', 'action catch')


            var element = $(clickElement);
            // return if the link is an anchor inside the same page
            if (clickElement.nodeName == 'A'
                && (
                    element.attr('href').length == 0 ||
                    element.attr('href')[0] == '#' ||
                    element.attr('href').substring(0, 11) == 'javascript:')) {
                return;
            }
            if (element.hasClass('has-action')) {
                that.logMessage('field_dialog_form_action', 'reserved action stop catch all events');
                return;
            }
            event.preventDefault();
            event.stopPropagation();
            that.initializePopUp();


            try {
                that.logMessage('field_dialog_form_action', 'execute ajax call');

                axios(getAxiosRequestConfig(clickElement)).then(function (response) {
                    var data = response.data;
                    if (typeof data !== 'object') {
                        var IS_JSON = true;
                        try {
                            data = jQuery.parseJSON(data);
                        } catch (err) {
                            IS_JSON = false;
                        }

                        if (IS_JSON === false && typeof data == 'string') {
                            that.dialog.html(data);
                            return;
                        }
                    }
                    // if the crud action return ok, then the element has been added
                    // so the widget container must be refresh with the last option available

                    if (data.result === 'ok') {
                        that.dialog.modal('hide');
                        if (data.html) {
                            $('body').trigger("html-received", [data.html]);
                        }
                        if (that.isList) {
                            that.field.val(data.objectId).trigger('change');
                        } else {
                            var parentForm = jQuery('#field_widget_' + that.id).closest('form');
                            var formData = new FormData(parentForm[0]);
                            axios.post(that.formElementUrl, formData, axiosConfig)
                                .then(function (response) {
                                    var container = jQuery('#field_container_' + that.id);
                                    container.html(jQuery(response.data).filter('#field_container_' + that.id).html());
                                    var newElement = jQuery('#' + id + ' [value="' + data.objectId + '"]');
                                    if (newElement.is("input")) {
                                        newElement.attr('checked', 'checked');
                                    } else {
                                        newElement.attr('selected', 'selected');
                                    }
                                    if (newElement.hasClass('select2')) {
                                        container.on('sonata-admin-append-form-element', function () {
                                            newElement.select2();
                                            newElement.trigger('change');
                                        });
                                        container.trigger('sonata-admin-append-form-element');
                                    }
                                });
                        }
                        return;
                    }

                    // otherwise, display form error
                    that.dialog.html(data);

                    // reattach the event
                    jQuery('form', that.dialog).submit(function (event) {
                        that.field_dialog_form_action(event, this)
                    });
                })
            } catch (e) {
                alert(e);
                return;
            }

            return false;
        },
        //remove the image
        field_remove_element: function () {
            if (jQuery('#' + this.id + ' option').get(0)) {
                this.field.attr('selectedIndex', '-1').children("option:selected").attr("selected", false);
            }
            this.field.val('');
            this.field.trigger('change');
            return false;
        },
        createModalHtml: function (responseHtml) {
            var html = '<div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"> ' +
                '<h3>' + this.dialogTitle + '</h3><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button></div>' +
                '<div class="modal-body">' + responseHtml + '</div> <div class="modal-footer"></div> </div></div>';

            this.dialog.html(html);
        },
        logMessage: function (method, message) {
            console.log('[' + this.id + '|' + method + '] ' + message);
        }
    };
    var list = Object.create(modalList);
    list.init();
    return list;
}