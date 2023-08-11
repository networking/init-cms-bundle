import {CMSAdmin} from './cms-admin';
import CMSRouting from './cms-routing';
import 'jstree'
import 'bootstrap'

var CMSMenuAdmin = function () {

    var lastTab = localStorage.getItem('lastTab');
    var lastEdited;
    var menuDialog;
    var tabs;
    var listUrl;
    var updateUrl;
    var ready = false;
    var trees = [];

    var reloadList = () => {
        axios.get(tabs.dataset.listUrl, {'locale': document.querySelector('#filter_locale_value').value, ...axiosConfig}
        ).then(function (response) {
            tabs.innerHTML = response.data.html;

            if(response.data.hasOwnProperty('last_edited') > -1) {
                setLastEdited(response.data.last_edited)
            }
            setUpSortTree();
            selectLastTab(lastTab)
        })
    }

    var selectLastTab = (lastTab) => {
        if(!lastTab) {
            return;
        }
        let tabTriggerEl = document.querySelector('a[href="' + lastTab + '"]')
        var tab = new bootstrap.Tab(tabTriggerEl)

        tab.show()
    }

    var setLastTab = (tab) => {
        lastTab = tab;
        localStorage.setItem('lastTab', lastTab);
    }

    var initializeDialog = () => {
        if (!menuDialog) {
            menuDialog = new bootstrap.Modal(document.querySelector('#menu_dialog'), {height:'auto', width:650, show: false})
        }
    }

    var createAjaxDialog = (event)=> {
        event.preventDefault();



        let link = event.target;

        if(!link.getAttribute('href')) {
            link = link.closest('a');
        }


        let locale = document.querySelector('#filter_locale_value').value
        axios.get(
            link.getAttribute('href'),
            {'locale': locale, ...axiosConfig}
        ).then(function (response) {
            document.querySelector('#menu_dialog').querySelector('.modal-content').innerHTML = response.data;
            menuDialog.show();
            CMSAdmin.createSelect2();
        })
    }


    var submitAjaxForm =(event) => {
        event.preventDefault();
        event.stopPropagation();

        let form = event.target;

        form.elements.forEach((item) => {
            item.classList.remove('is-invalid')
        })
        // form.classList.add('was-validated')
        axios.post(form.action, new FormData(form), axiosConfig)
            .then(function (response) {
                var data = response.data;
                if (data.result == 'ok') {

                    if (data.is_new_menu_item && data.html) {
                        document.querySelector('#menu_dialog').querySelector('.modal-content').innerHTML = data.html;
                    } else {
                        menuDialog.hide();
                        CMSAdmin.createInitCmsMessageBox(data.status, data.message);
                    }
                    reloadList();
                }
            })
            .catch(function (error) {
                var data = error.response.data;

                if (typeof data !== 'object') {
                    menuDialog.html(data);
                }

                data.violations.forEach((item) => {
                    let path = item.propertyPath
                    let message = item.title
                    let field = form.querySelector('[name="' + path + '"]')
                    field.classList.add('is-invalid')
                    field.setAttribute('required', 'required')

                    if(field.nextElementSibling && field.nextElementSibling.classList.contains('invalid-feedback')){
                        field.nextElementSibling.innerHtml = message;
                        return
                    }
                    field.insertAdjacentHTML('afterend', '<div class="invalid-feedback">' + message + '</div>')
                })
            })
    }

    var setUpSortTree = () => {
        $('div.menu_tree').each(function (e, ui) {
            let tree = $(ui);

            trees.push(tree)

            let treeData = tree.data('tree');
            let plugins = ['dnd', 'types','changed'];

            if (tree.attr('id') !== 'placement_menu') {
                plugins.push('externalUrl');
                plugins.push('delete');
            }
            tree.jstree({
                "types": {
                    "default": {
                        "icon": "la la-file text-primary fs-2x"
                    },
                    "file": {
                        "icon": "la la-file text-primary fs-2x"
                    }
                },
                "plugins": plugins,
                'core': {
                    "themes": {
                        "variant": "large",
                    },

                    "check_callback": true,
                    data: function (node, cb) {
                        cb(treeData)
                    }
                }
            });

            let treeSwitch = document.querySelector('[data-tree-id="' + tree.attr('id') + '"]')

            tree.on("move_node.jstree", function (e, data) {
                updateMenuSort(data.new_instance.get_json(), ($(data.new_instance.element[0]).attr('id') === 'placement_menu'))

                if (treeSwitch && treeSwitch.checked) {
                    showHideTree(treeSwitch)
                }

            });

            tree.on('after_close.jstree', function (e, data) {
                treeSwitch.checked = false
            })

            if (ready) {
                return;
            }

            tree.on('ready.jstree', function () {
                ready = true;
                postMenuLoad();
            });

        });
    }


    var postMenuLoad = () => {

        document.querySelectorAll('[data-tree-id]').forEach(function (item) {
            showHideTree(item)
        })
    }

    var setLastEdited = (id) => {
        lastEdited = id;
    }

    var flattenArray = (item, depth, left, ret, pid) => {
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

    var updateMenuSort = (nodes, doReload) => {
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
                CMSAdmin.createInitCmsMessageBox('success', response.data.message);
                menuDialog.hide();
                if (doReload) {
                    reloadList()
                }
            })
    }

    var handleShowHideTree = (event) => {
        let treeSwitch = event.target;
        showHideTree(treeSwitch)
    }

    var showHideTree = (treeSwitch) => {
        let treeId = treeSwitch.dataset.treeId;
        let open = treeSwitch.checked

        localStorage.setItem('tree_' + treeId, open)
        trees.find((tree) => {
            if(tree.attr('id') === treeId) {
                if(open) {
                    return tree.jstree(true).open_all()
                }
                return tree.jstree(true).close_all()
            }
        })

    }

    var initMenu = () => {
        tabs = document.querySelector('#menuTabs')

        lastTab = localStorage.getItem('lastTab');
        lastEdited = tabs.dataset.lastEdited;
        listUrl = tabs.dataset.listUrl
        updateUrl = tabs.dataset.updateUrl

        document.querySelectorAll('[data-tree-id]').forEach(function (item) {
            item.checked = localStorage.getItem('tree_' + item.dataset.treeId) !== 'false'
        })
        setUpSortTree();
        KTUtil.on(document.querySelector('#menuTabs'), '.menu-dialog-link', 'click', createAjaxDialog);
        KTUtil.on(document.querySelector('#menu_dialog'), 'form', 'submit', submitAjaxForm);
        KTUtil.on(document.querySelector('#menuTabs'), '.tree-show-all', 'click', handleShowHideTree);

        document.body.addEventListener('shown.bs.tab',  (e) => {
            setLastTab($(e.target).attr('href'));
        });

        setTimeout( () => {
            if (lastEdited) {
                let lastEditedNode = document.querySelector('#menu-item-' + lastEdited);
                lastTab = '#menu_' + lastEditedNode.dataset.rootId;
                setLastTab(lastTab);
            }

            selectLastTab(lastTab)
        }, 500);
    }

    return {
        init: function () {
            initMenu();
            initializeDialog();
        },
        reload: function () {
            reloadList();
        }
    }
}();

(function ($, undefined) {
    "use strict";

    var externalUrl = document.createElement('a');
    externalUrl.className = "external-url float-end";
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

    var link = document.createElement('a');
    link.className = "delete-tag menu-dialog-link float-end ";
    link.innerHTML = '<i class="la la-trash fs-2 mx-3"></i>';

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


})($);



// On document ready
KTUtil.onDOMContentLoaded(function () {
    CMSMenuAdmin.init();
});