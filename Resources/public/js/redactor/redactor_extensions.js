if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};
var cssPluginOptions = {
    modal_table:String() +
        '<div id="redactor_modal_content">' +
        '<label>CSS Class</label>' +
        '<input type="text" size="5" value="" id="redactor_table_class" />' +
        '<label>' + RLANG.rows + '</label>' +
        '<input type="text" size="5" value="2" id="redactor_table_rows" />' +
        '<label>' + RLANG.columns + '</label>' +
        '<input type="text" size="5" value="3" id="redactor_table_columns" />' +
        '</div>' +
        '<div id="redactor_modal_footer">' +
        '<a href="javascript:void(null);" class="redactor_modal_btn redactor_btn_modal_close">' + RLANG.cancel + '</a>' +
        '<input type="button" name="upload" class="redactor_modal_btn" id="redactor_insert_table_btn" value="' + RLANG.insert + '" />' +
        '</div>',
    modal_table_class:String() +
        '<div id="redactor_modal_content">' +
        '<label>CSS Class</label>' +
        '<input type="text" size="5" value="" id="redactor_table_class" />' +
        '</div>' +
        '<div id="redactor_modal_footer">' +
        '<a href="javascript:void(null);" class="redactor_modal_btn redactor_btn_modal_close">' + RLANG.cancel + '</a>' +
        '<input type="button" name="upload" class="redactor_modal_btn" id="redactor_insert_table_btn" value="' + RLANG.save + '" />' +
        '</div>',
    modal_image_edit:String() +
        '<div id="redactor_modal_content">' +
        '<label>' + RLANG.title + '</label>' +
        '<input id="redactor_file_alt" class="redactor_input" />' +
        '<label>' + RLANG.link + '</label>' +
        '<input id="redactor_file_link" class="redactor_input" />' +
        '<label>' + RLANG.image_position + '</label>' +
        '<select id="redactor_form_image_align">' +
        '<option value="none">' + RLANG.none + '</option>' +
        '<option value="left">' + RLANG.left + '</option>' +
        '<option value="right">' + RLANG.right + '</option>' +
        '</select>' +
        '<label>CSS Class</label>' +
        '<input id="redactor_form_image_edit_css" class="redactor_input" />' +
        '</div>' +
        '<div id="redactor_modal_footer">' +
        '<a href="javascript:void(null);" id="redactor_image_delete_btn" class="redactor_modal_btn">' + RLANG._delete + '</a>&nbsp;&nbsp;&nbsp;' +
        '<a href="javascript:void(null);" class="redactor_modal_btn redactor_btn_modal_close">' + RLANG.cancel + '</a>' +
        '<input type="button" name="save" class="redactor_modal_btn" id="redactorSaveBtn" value="' + RLANG.save + '" />' +
        '</div>',
    toolbar:{
        html:{
            title:RLANG.html,
            func:'toggle'
        },
        formatting:{
            title:RLANG.formatting,
            func:'show',
            dropdown:{
                p:{
                    title:RLANG.paragraph,
                    exec:'formatblock'
                },
                blockquote:{
                    title:RLANG.quote,
                    exec:'formatblock',
                    className:'redactor_format_blockquote'
                },
                pre:{
                    title:RLANG.code,
                    exec:'formatblock',
                    className:'redactor_format_pre'
                },
                h1:{
                    title:RLANG.header1,
                    exec:'formatblock',
                    className:'redactor_format_h1'
                },
                h2:{
                    title:RLANG.header2,
                    exec:'formatblock',
                    className:'redactor_format_h2'
                },
                h3:{
                    title:RLANG.header3,
                    exec:'formatblock',
                    className:'redactor_format_h3'
                },
                h4:{
                    title:RLANG.header4,
                    exec:'formatblock',
                    className:'redactor_format_h4'
                }
            }
        },
        bold:{
            title:RLANG.bold,
            exec:'bold'
        },
        italic:{
            title:RLANG.italic,
            exec:'italic'
        },
        deleted:{
            title:RLANG.deleted,
            exec:'strikethrough'
        },
        underline:{
            title:RLANG.underline,
            exec:'underline'
        },
        unorderedlist:{
            title:'&bull; ' + RLANG.unorderedlist,
            func:'insertunorderedlist'
        },
        orderedlist:{
            title:'1. ' + RLANG.orderedlist,
            exec:'insertorderedlist'
        },
        outdent:{
            title:'< ' + RLANG.outdent,
            exec:'outdent'
        },
        indent:{
            title:'> ' + RLANG.indent,
            exec:'indent'
        },
        image:{
            title:RLANG.image,
            func:'showImage'
        },
        video:{
            title:RLANG.video,
            func:'showVideo'
        },
        file:{
            title:RLANG.file,
            func:'showFile'
        },
        table:{
            title:RLANG.table,
            func:'show',
            dropdown:{
                insert_table:{
                    title:RLANG.insert_table,
                    func:'showTable'
                },
                separator_drop1:{
                    name:'separator'
                },
                insert_class:{
                    title:'Add table class',
                    func:'showTableClass'
                },
                insert_row_above:{
                    title:RLANG.insert_row_above,
                    func:'insertRowAbove'
                },
                insert_row_below:{
                    title:RLANG.insert_row_below,
                    func:'insertRowBelow'
                },
                insert_column_left:{
                    title:RLANG.insert_column_left,
                    func:'insertColumnLeft'
                },
                insert_column_right:{
                    title:RLANG.insert_column_right,
                    func:'insertColumnRight'
                },
                separator_drop2:{
                    name:'separator'
                },
                add_head:{
                    title:RLANG.add_head,
                    func:'addHead'
                },
                delete_head:{
                    title:RLANG.delete_head,
                    func:'deleteHead'
                },
                separator_drop3:{
                    name:'separator'
                },
                delete_class:{
                    title:'Remove css',
                    func:'removeCss'
                },
                delete_column:{
                    title:RLANG.delete_column,
                    func:'deleteColumn'
                },
                delete_row:{
                    title:RLANG.delete_row,
                    func:'deleteRow'
                },
                delete_table:{
                    title:RLANG.delete_table,
                    func:'deleteTable'
                }
            }
        },
        link:{
            title:RLANG.link,
            func:'show',
            dropdown:{
                link:{
                    title:RLANG.link_insert,
                    func:'showLink'
                },
                unlink:{
                    title:RLANG.unlink,
                    exec:'unlink'
                }
            }
        },
        fontcolor:{
            title:RLANG.fontcolor,
            func:'show'
        },
        backcolor:{
            title:RLANG.backcolor,
            func:'show'
        },
        alignment:{
            title:RLANG.alignment,
            func:'show',
            dropdown:{
                alignleft:{
                    title:RLANG.align_left,
                    exec:'JustifyLeft'
                },
                aligncenter:{
                    title:RLANG.align_center,
                    exec:'JustifyCenter'
                },
                alignright:{
                    title:RLANG.align_right,
                    exec:'JustifyRight'
                },
                justify:{
                    title:RLANG.align_justify,
                    exec:'JustifyFull'
                }
            }
        },
        alignleft:{
            exec:'JustifyLeft',
            title:RLANG.align_left
        },
        aligncenter:{
            exec:'JustifyCenter',
            title:RLANG.align_center
        },
        alignright:{
            exec:'JustifyRight',
            title:RLANG.align_right
        },
        justify:{
            exec:'JustifyFull',
            title:RLANG.align_justify
        },
        horizontalrule:{
            exec:'inserthorizontalrule',
            title:RLANG.horizontalrule
        }
    }
};
RedactorPlugins.cssPlugin = {

    init:function (options) {
        // Modal's callback

        console.log(this.opts);

        var callback = $.proxy(function (obj, e, key) {
            this.saveSelection();
            this.insertFromMyModal(obj, e, key);

        }, this);


        var dropdown = {
            removeFormatting:{
                title:'Remove formatting',
                callback:callback
            },
            remove:{
                title:'Remove class',
                callback:callback
            }
        }

        var styles = this.opts.cssStyles;
        this.opts = $.extend(this.opts, cssPluginOptions);

        jQuery.each(styles, function (k, e) {


            dropdown[k] = {title:e, callback:callback};
        });

        $('.redactor_toolbar').remove();
        this.buildToolbar();
        this.addBtn('css', 'Css Classes', function () {
        }, dropdown);
    },
    insertFromMyModal:function (obj, e, key) {
        this.setBuffer();

        var text = this.getSelectedHtml();
        var node = this.getCurrentNode();
        var selectedNode = this.getSelectedNode();


        if (key == 'remove') {
            jQuery(node).removeClass();
        }
        else if (key == 'removeFormatting') {
            console.log(jQuery(node).attr('class'));
            if (!jQuery(node).hasClass('redactor_editor') && !jQuery(node).hasClass('redactor_box')) {
                jQuery(node).stripTags();
            }
        }
        else {

            if (text != '' && jQuery(selectedNode).html() != text) {
                surroundSelection(key);
            } else {
                jQuery(node).addClass(key);
            }

        }
        this.saveSelection();
        this.restoreSelection();
        this.syncCode();

    },
    showTable:function () {
        this.saveSelection();

        this.modalInit(RLANG.table, this.opts.modal_table, 300, $.proxy(function () {
            $('#redactor_insert_table_btn').click($.proxy(this.insertTable, this));

            setTimeout(function () {
                $('#redactor_table_class').focus();
            }, 200);

        }, this)
        );
    },
    insertTable:function () {
        var rows = $('#redactor_table_rows').val();
        var columns = $('#redactor_table_columns').val();
        var css_class = $('#redactor_table_class').val();

        var table_box = $('<div></div>');

        var tableid = Math.floor(Math.random() * 99999);
        var table = $('<table id="table' + tableid + '" class="' + css_class + '"><tbody></tbody></table>');

        for (var i = 0; i < rows; i++) {
            var row = $('<tr></tr>');
            for (var z = 0; z < columns; z++) {
                var column = $('<td><br></td>');
                $(row).append(column);
            }
            $(table).append(row);
        }

        $(table_box).append(table);
        var html = $(table_box).html() + '<p></p>';

        this.restoreSelection();
        this.execCommand('inserthtml', html);
        this.modalClose();
        this.observeTables();

    },
    showTableClass:function () {
        this.saveSelection();

        this.modalInit(RLANG.table, this.opts.modal_table_class, 300, $.proxy(function () {
            var table_class = $(this.$table).attr('class');
            $('#redactor_insert_table_btn').click($.proxy(this.updateTableClass, this));

            setTimeout(function () {
                $('#redactor_table_class').focus();
                $('#redactor_table_class').val(table_class);
            }, 200);

        }, this)
        );
    },
    updateTableClass:function () {
        var cssClass = $('#redactor_table_class').val();

        if (cssClass != '') {
            if ($(this.$table).attr('class')) {
                $(this.$table).attr('class', cssClass);
            } else {
                $(this.$table).addClass(cssClass);
            }
        } else {
            $(this.$table).removeAttr('class');
        }

        this.restoreSelection();
        this.modalClose();
        this.observeTables();

    },
    removeCss:function () {
        $(this.$table).removeAttr('class');

        this.restoreSelection();
        this.observeTables();
    },
    imageEdit:function (e) {
        var $el = $(e.target);
        var parent = $el.parent();

        var callback = $.proxy(function () {
            $('#redactor_file_alt').val($el.attr('alt'));
            $('#redactor_image_edit_src').attr('href', $el.attr('src'));
            $('#redactor_form_image_align').val($el.css('float'));
            $('#redactor_form_image_edit_css').val($el.attr('class'));

            if ($(parent).get(0).tagName === 'A') {
                $('#redactor_file_link').val($(parent).attr('href'));
            }

            $('#redactor_image_delete_btn').click($.proxy(function () {
                this.imageDelete($el);
            }, this));
            $('#redactorSaveBtn').click($.proxy(function () {
                this.imageSave($el);
            }, this));

        }, this);

        this.modalInit(RLANG.image, this.opts.modal_image_edit, 380, callback);

    },
    imageSave:function (el) {
        var parent = $(el).parent();

        $(el).attr('alt', $('#redactor_file_alt').val());

        var floating = $('#redactor_form_image_align').val();

        if (floating === 'left') {
            $(el).css({ 'float':'left', margin:'0 10px 10px 0' });
        }
        else if (floating === 'right') {
            $(el).css({ 'float':'right', margin:'0 0 10px 10px' });
        }
        else {
            $(el).css({ 'float':'none', margin:'0' });
        }

        // as link
        var link = $.trim($('#redactor_file_link').val());
        if (link !== '') {
            if ($(parent).get(0).tagName !== 'A') {
                $(el).replaceWith('<a href="' + link + '">' + this.outerHTML(el) + '</a>');
            }
            else {
                $(parent).attr('href', link);
            }
        }
        else {
            if ($(parent).get(0).tagName === 'A') {
                $(parent).replaceWith(this.outerHTML(el));
            }
        }

        var cssClass = $('#redactor_form_image_edit_css').val();

        if (cssClass != '') {
            if ($(el).attr('class')) {
                $(el).attr('class', cssClass);
            } else {
                $(el).addClass(cssClass);
            }
        } else {
            $(el).removeAttr('class');
        }

        this.modalClose();
        this.observeImages();
        this.syncCode();

    },
    insertunorderedlist:function () {
        //                var list = this.getSelectedNode(), 'ol,ul');
        //
        this.document.execCommand('insertunorderedlist', false, null);
        var list = jQuery(this.getCurrentNode()).find('li');

        console.log(list.length);
        if (list.length < 1) {

            var span = jQuery(this.getParentNode()).children('span');
            span.replaceWith(span.text());
        } else {
            list.each(function () {
                var span = jQuery(this).children('span');
                span.replaceWith(span.text());
            })
        }


    }
}

jQuery.fn.stripTags = function () {
    return this.replaceWith(this.html().replace(/<\/?[^>]+>/gi, ''));
};

surroundSelection = function (className) {
    var selection = window.getSelection().getRangeAt(0);
    var selectedText = selection.extractContents();
    var span = document.createElement("span");
    jQuery(span).addClass(className)
    span.appendChild(selectedText);
    selection.insertNode(span);
}