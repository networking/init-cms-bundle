var menuDialog, lastTab, tabs, lastEdited, listUrl, updateUrl, ready = false;

function reloadList() {
    axios.get(tabs.data('list-url'), {'locale': $('#filter_locale_value').val(), ...axiosConfig}
    ).then(function (response) {
        tabs.html(response.data);
        setUpSortTree();
        $('a[href="' + lastTab + '"]').tab('show');
    })
}

function setLastTab(tab) {
    lastTab = tab;
    localStorage.setItem('lastTab', lastTab);
}

function createAjaxDialog(event) {
    event.preventDefault();
    axios.get(
        $(this).attr('href'),
        {'locale': $('#filter_locale_value').val(), ...axiosConfig}
    ).then(function (response) {
        menuDialog.html(response.data);
        menuDialog.modal('show');
    })
}

function submitAjaxForm(event) {
    event.preventDefault();
    event.stopPropagation();
    axios.post(this.action, new FormData(this), axiosConfig)
        .then(function (response) {
            var data = response.data;
            if (data.result == 'ok') {
                reloadList();
                if (data.is_new_menu_item && data.html) {
                    menuDialog.html(data.html);
                } else {
                    menuDialog.modal('hide');
                    createInitCmsMessageBox(data.status, data.message);
                }
            }
        })
        .catch(function (error) {
            console.log(error);
            var data = error.response.data;
            if (typeof data === 'object') {
                $('.modal-body').prepend('<div class="alert alert-danger">' + data.message + ' <br>' + data.errors.join('<br>') + '</div>');
            } else {
                menuDialog.html(data);
            }
        })
}

function setUpSortTree() {
    $('div.menu_tree').each(function (e, ui) {
        var tree = $(ui);
        var treeData = tree.data('tree');
        tree.jstree({
            "types": {
                "default": {
                    "icon": "la la-file text-primary icon-2x"
                },
                "file": {
                    "icon": "la la-file text-primary icon-2x"
                }
            },
            "plugins": [
                "dnd",
                "externalUrl",
                "delete",
                "types",
            ],
            'core': {
                "themes": {
                    "variant": "large"
                },
                "check_callback": function (operation, node, node_parent, node_position, more) {
                    return true;
                },
                data: function (node, cb) {
                    cb(treeData)
                }
            }
        });

        if (ready) {
            return;
        }

        tree.on('ready.jstree', function () {
            postMenuLoad();
            ready = true;
        });

    });
}


function postMenuLoad() {
    if (lastEdited) {
        var lastEditedNode = $('#menu-item-' + lastEdited);
        lastEditedNode.trigger('click');
        lastTab = '#menu_' + lastEditedNode.data('root-id');
        setLastTab(lastTab);
    }

    if (lastTab) {
        $('a[href="' + lastTab + '"]').tab('show');
    }
}

function flattenArray(item, depth, left, ret, pid) {
    var right = left + 1,
        id = item.data.id,
        pid;

    if (item.children.length > 0) {
        depth++;
        item.children.forEach(function (node) {
            right = flattenArray(node, depth, right, ret, id);
        });
        depth--;
    }

    if (id) {
        ret.push({
            "name": item.text,
            "item_id": id,
            "parent_id": pid,
            "depth": depth,
            "left": left,
            "right": right
        });
    }

    left = right + 1;
    return left;
}

function updateMenuSort(nodes, doReload) {
    var ret = [];
    var left = 2;

    nodes.forEach(function (node) {
        left = flattenArray(node, 1, left, ret, node.data.rootId);
    })

    ret = ret.sort(function (a, b) {
        return (a.left - b.left);
    });

    axios.post(updateUrl, {"nodes": ret}, axiosConfig)
        .then(function (response) {
            createInitCmsMessageBox('success', response.data.message);
            menuDialog.modal('hide');
            console.log(doReload)
            if (doReload) {
                reloadList()
            }
        })
}

$(function () {
    tabs = $('#menuTabs');
    menuDialog = $('#menu_dialog');
    lastTab = localStorage.getItem('lastTab');
    lastEdited = tabs.data('last-edited');
    listUrl = tabs.data('list-url');
    updateUrl = tabs.data('update-url');
    tabs.on('click', '.menu-dialog-link', createAjaxDialog);

    setUpSortTree();
    menuDialog = $('#menu_dialog');
    menuDialog.modal({show: false});
    $(menuDialog).on('submit', 'form', submitAjaxForm);

    $('body').on('shown.bs.tab', function (e) {
        setLastTab($(e.target).attr('href'));
    });

    $(document).on("dnd_stop.vakata", function (e, data) {
        updateMenuSort(data.data.origin.get_json(), ($(data.data.origin.element[0]).attr('id') === 'placement_menu'))
    });
});

(function ($, undefined) {
    "use strict";
    var link = document.createElement('a');
    link.className = "delete-tag menu-dialog-link float-right ";
    link.innerHTML = '<i class="la la-trash icon-lg"></i>';

    $.jstree.defaults.delete = $.noop;
    $.jstree.plugins.delete = function (options, parent) {

        this.teardown = function () {
            if (this.settings.delete) {
                this.element.find(".delete-tag").remove();
            }
            parent.teardown.call(this);
        };
        this.redraw_node = function (obj, deep, callback, force_draw) {

            obj = parent.redraw_node.call(this, obj, deep, callback, force_draw);

            var tmp = link.cloneNode(true);
            var node = this.get_node(obj);
            tmp.href = node.data.deleteUrl;
            obj.insertBefore(tmp, obj.childNodes[2]);
            return obj;
        };
    };

    var externalUrl = document.createElement('a');
    externalUrl.className = "external-url float-right mr-8";
    $.jstree.defaults.externalUrl = $.noop;
    $.jstree.plugins.externalUrl = function (options, parent) {
        this.teardown = function () {
            if (this.settings.externalUrl) {
                this.element.find(".external-url").remove();
            }
            parent.teardown.call(this);
        };
        this.redraw_node = function (obj, deep, callback, force_draw) {

            obj = parent.redraw_node.call(this, obj, deep, callback, force_draw);
            var node = this.get_node(obj);
            var tmp = externalUrl.cloneNode(true);
            tmp.href = node.data.externalUrl;
            tmp.innerHTML = node.data.path
            obj.insertBefore(tmp, obj.childNodes[2]);
            return obj;
        };
    };
})($);