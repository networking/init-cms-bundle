CKEDITOR.plugins.add("internal_link", {
    lang: ["de", "en"],
    requires: "ajax"
}), CKEDITOR.on('dialogDefinition', function (ev) {
    if (ev.data.name === 'link') {
        var setup = JSON.parse(CKEDITOR.ajax.load("/cms/pages/internal-url.json?"));
        var infoTab = ev.data.definition.getContents('info');
        ev.data.definition.onLoad = function()
        {
            var linkType = this.getContentElement( 'info', 'linkType' );
            var internalinkOptions = this.getContentElement( 'info', 'internalinkOptions' );
            $('#'+linkType.domId).on('change', function () {
                if(linkType.getValue() === 'url'){
                    internalinkOptions.getElement().show()
                }else{
                    internalinkOptions.getElement().hide()
                }
            })
        };

        infoTab.add({
            type: 'vbox',
            id: 'internalinkOptions',
            children: [
                {
                    type: "hbox", widths: ["25%", "75%"], children: [
                        {
                            type: 'select',
                            id: 'locale',
                            label: ev.editor.lang.internal_link.locale,
                            style: 'width:100%',
                            items: setup.locales,
                            onChange: function () {
                                var locale = this.getValue();
                                var setup = JSON.parse(CKEDITOR.ajax.load("/cms/pages/internal-url.json?_locale=" + locale));
                                var intern = this.getDialog().getContentElement("info", "intern");
                                intern.clear();
                                intern.add(ev.editor.lang.internal_link.select, '');

                                setup.pages.forEach(function (page) {
                                    intern.add(page.name, page.url);
                                });
                            },
                            commit: function (element) {
                                var d = CKEDITOR.dialog.getCurrent();
                                d.setValueOf('advanced', 'advLangCode', this.getValue());
                            },
                            setup: function (data) {
                                this.allowOnChange = false;
                                this.setValue(data.advanced ? data.advanced.advLangCode : setup.locales[0][1]);
                                this.allowOnChange = true;
                            }
                        }, {
                            type: 'select',
                            id: 'intern',
                            label: ev.editor.lang.internal_link.internal_link,
                            style: 'width:100%',
                            items: [[ev.editor.lang.internal_link.select, null]],
                            onChange: function () {
                                var d = CKEDITOR.dialog.getCurrent();
                                d.setValueOf('info', 'url', this.getValue());
                                d.setValueOf('info', 'protocol', !this.getValue() ? 'http://' : '');
                            },
                            setup: function (data) {
                                this.allowOnChange = false;
                                this.setValue(data.url ? data.url.url : '');
                                this.allowOnChange = true;
                            }
                        }
                    ]
                }
            ]

        }, 'urlOptions');
    }
});

