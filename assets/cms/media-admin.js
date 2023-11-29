import {CMSAdmin, Routing} from './cms-admin';
import 'jstree'
import GLightbox from 'glightbox';
import 'glightbox/dist/css/glightbox.min.css';

var CMSMediaAdmin = function () {
    var tagDialog;
    var tagDialogContainer;
    var tagsContainer = null;
    var treeData = [];
    var canSort = true;
    var canDelete = true;
    var inlineEditUrl = '';

    var createTagDialog = (event) => {
        event.preventDefault();
        event.stopPropagation();

        initializeTagDialog();

        let link = event.target;

        if(!link.getAttribute('href')) {
            link = link.closest('a');
        }




        fetch(link.getAttribute('href'), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        }).then(response => {
            return response.text()
        }).then(html => {
            document.querySelector('#tag_dialog').querySelector('.modal-content').innerHTML = html;

            tagDialog.show();

            tagDialogContainer.querySelector('form').addEventListener('submit', submitAjaxTagForm)
        })
    }
    const initializeTagDialog = () => {
        if (!tagDialog) {
            tagDialogContainer = document.querySelector('#tag_dialog'),
            tagDialog = new bootstrap.Modal(tagDialogContainer, { height:'auto', width:650, show:false})
        }
    }
    const submitAjaxTagForm = (event) => {
        event.preventDefault();

        let form = event.target;

        axios.post(form.action, new FormData(form), axiosConfig).then((response) => {
            if(response.headers['content-type'] === 'application/json') {
                if(response.data.result === 'ok') {
                    tagDialog.hide();
                    CMSAdmin.createInitCmsMessageBox(response.data.status, response.data.message);
                    reloadSortTree(response.data.json, response.data.objectId);
                }
            }
            tagDialogContainer.querySelector('.modal-content').innerHTML = response.data;
            tagDialogContainer.querySelector('form').addEventListener('submit', submitAjaxTagForm)
            CMSAdmin.initSpecialFields()
        }).catch((error) => {
            console.log(error);
        })
    }

    const setUpEditable = () => {

        let editableLinks = CMSApp.create_xeditable('.tag_link', {
            'toggle': 'manual',
            'url': inlineEditUrl,
            'name': 'name',
            'display': function (value, response){
                if(typeof response !== "undefined"){
                    var icon = $('.jstree-icon', this);
                    $(this).html(icon).append(value);
                }
            }
        }, function (event, params) {
            updateTreeData(params.newValue, params.response.objectId);
        },function (e, r) {
            if (r === 'save') {
                var list = $(this).parent().closest('ul');
                const currentUrl = new URL(window.location.href);
                let searchParams = currentUrl.searchParams;
                searchParams.set('time', jQuery.now());
                currentUrl.search = searchParams.toString();
                history.replaceState({time: jQuery.now()}, 'updated', currentUrl.toString());
            }
        } )

    }

    const updateTreeData = (value, id) => {
        var data = $(tagsContainer).jstree(true).get_json();
        replaceDataName(data, value, id, data.length -1);
        setUpSortTree()

    }

    const replaceDataName = (nodes, name, id, n) => {
        if(n < 0){
            return nodes;
        }
        var node = nodes[n];

        if(typeof node.data !== "undefined" && node.data['id'] === id){
            node.text = name;
            node.li_attr['data-tag-name'] = name;
        }
        if(node.children.length > 0){
            replaceDataName(node.children, name, id, node.children.length-1);
        }

        return replaceDataName(nodes, name, id, n-1);
    }

    const setUpSortTree = () => {

        let plugins = [
            "sort",
            "types",
            "unique"
        ];
        if (canSort) {
            plugins.push("dnd");
        }

        if (canDelete) {
            plugins.push("delete");
        }

        let tree = $(tagsContainer)

        treeData = tree.data('tagsJson')

        inlineEditUrl = tree.data('inlineEditUrl')

        tree.jstree({
            "types": {
                "default": {
                    "icon": "fa fa-folder text-primary fs-2x"
                },
                "file": {
                    "icon": "fa fa-file text-primary fs-2x"
                }
            },
            "plugins": plugins,
            "dnd":
                {
                    "check_while_dragging": true,
                    "is_draggable": (node) => {
                        return node[0].a_attr.class !== "show_all_media"
                    },
                },
            "sort": function (a, b) {
                let a1 = this.get_node(a);
                let b1 = this.get_node(b);
                if (a1.data['show_first']) {
                    return -1;
                }

                if (b1.data['show_first']) {
                    return 1;
                }
                return (a1.text.toLowerCase() > b1.text.toLowerCase()) ? 1 : -1;
            },
            'core':
                {
                    "themes": {
                            "variant":   "large"
                    },
                    "check_callback": function (operation, node, node_parent, node_position, more) {
                        // operation can be 'create_node', 'rename_node', 'delete_node', 'move_node' or 'copy_node'
                        // in case of 'rename_node' node_position is filled with the new node name

                        if (operation === "move_node") {
                            if (node_parent.a_attr) {
                                return node_parent.a_attr.class !== "show_all_media"; //only allow dropping inside nodes of type 'Parent'
                            }
                        }
                        return true;
                    }

                    ,
                    data: function (node, cb) {
                        cb(treeData)
                    }
                }
        })

        tree.on('refresh.jstree', function () {
            setUpEditable();
        })
            .on('move_node.jstree', function (e, data) {

                let nodes = data.new_instance.get_json()
                var flatTree = getFlatTree(nodes, 0, 0, nodes.length-1, [])

                updateTree(flatTree)
                setUpEditable();
            })
            .on('after_open.jstree', function () {
                setUpEditable();
            })
            .on('redraw.jstree', function () {
                setUpEditable();
            })
        ;
        tree.on('dragover', function (e) {

            // if(!data.instance.is_leaf(data.node)){
            //     data.instance.open_node(data.node)
            // }
        })
    }

    const deleteTag = (event) => {
        event.preventDefault();
        let node = event.target;
        axios.get(node.data.delete_link, {...axiosConfig}).then(function (response) {
            listDialog.html(response.data);
            listDialog.modal('show');
        });
    }
    const updateTree = (tagTree) => {

        axios.post(tagsContainer.dataset.updateTreeUrl, {"nodes": tagTree}, axiosConfig)
            .then(function (response) {
                var data = response.data;
                CMSAdmin.createInitCmsMessageBox(data.status, data.message);
                const currentUrl = new URL(window.location.href);

                let searchParams = currentUrl.searchParams;
                searchParams.set('time', jQuery.now());
                currentUrl.search = searchParams.toString();

                // sortList(list);
                history.replaceState({time: jQuery.now()}, 'updated', currentUrl.toString());
            });

    }

    const getFlatTree = (nodes, parentId, depth, n, flatArray) => {
        if(n < 0){
            return flatArray;
        }
        var node = nodes[n];

        if(node.children.length > 0){
            flatArray.push.apply(getFlatTree(node.children, node.data['id'], depth+1, node.children.length-1, flatArray))
        }
        flatArray.push({id: node.data['id'], parent_id: parentId, name: node.text, depth: depth});

        return getFlatTree(nodes, parentId, depth, n-1, flatArray);
    }

    const reloadSortTree = (data, id) => {
        treeData = data;
        $(tagsContainer).jstree(true).refresh();
    }

    const refreshList = (filters) => {
        if (!filters) {
            filters = []
        }

        let data = new FormData( document.querySelector('#search-form'))

        for(const key in filters){
            data.append(key, filters[key])
        }

        filters = Object.fromEntries(data.entries());


        axios.get(tagsContainer.dataset.refreshListUrl, {...axiosConfig, params: filters})
            .then(function (response) {
                $('#item_list').html(response.data);
                $("html, body").animate({scrollTop: $('#item_list').scrollTop()}, "slow");

                setUpMediaLightbox();
                KTComponents.init();
        })
    }

    const setUpMediaLightbox = () => {

        const lightbox = GLightbox({
            touchNavigation: true,
            loop: true,
            autoplayVideos: true,
            selector: '.light-box-items'
        });
    }

    return {
        init: function () {
            tagsContainer = document.querySelector('#tagsContainer');
            canSort = tagsContainer.dataset.canSort
            canDelete = tagsContainer.dataset.canDelete;
            setUpSortTree();
            initializeTagDialog();
            setUpMediaLightbox();

            KTUtil.on(document.body, 'a.delete_check_box', 'click', function (e) {
                e.preventDefault();
                let active = false
                let batchActions = document.querySelector('.batch-actions');

                document.querySelectorAll('.delete_check_box').forEach(function (item) {
                    if (item.checked) {
                        item.classList.add('ui-selected');
                        active = true;
                    } else {
                        item.classList.remove('ui-selected');
                    }
                })

                if (active) {
                    return batchActions.classList.remove('d-none');
                }
                batchActions.classList.add('d-none');batchActions
            })

            KTUtil.on(document.body, 'a.batch', 'click', function (e) {
                e.preventDefault();
                document.querySelector("input[name='action']").value = e.target.dataset.value;
                createBatchDialog(e);
            })

            KTUtil.on(document.body, 'a.tag-dialog-link', 'click', function (e) {
                e.preventDefault();
                createTagDialog(e);
            })

            KTUtil.on(document.body, '.tag_link', 'click', function (e, noLoad) {
                e.preventDefault();
                var tagId = e.target.dataset.pk;
                if (noLoad === undefined) {
                    refreshList({'filter[tags][value]': tagId});
                }
            })

            KTUtil.on(document.body, '.show_all_media', 'click', function (e, noLoad) {
                e.preventDefault();
                refreshList({'filter[_page]':  1,'filter[tags][value]': ''});
            })

            KTUtil.on(document.body, 'a.media-pager', 'click', function (e) {
                e.preventDefault();
                let page = e.target.dataset.page;
                let sort_order = e.target.dataset.sortOrder;
                let sort_by = e.target.dataset.sortBy;
                let tags = e.target.dataset.tags;
                let name = e.target.dataset.name;
                refreshList({
                    'filter[_page]': page,
                    'filter[_sort_order]': sort_order,
                    'filter[_sort_by]':sort_by,
                    'filter[tags][value]': tags,
                    'filter[name][value]': name,
                });
            })

            KTUtil.on(document.body, '.per-page', 'change', function (e) {

                e.preventDefault();
                let per_page = e.target.value;
                let page = e.target.dataset.page;
                let sort_order = e.target.dataset.sortOrder;
                let sort_by = e.target.dataset.sortBy;
                let tags = e.target.dataset.tags;
                let name = e.target.dataset.name;
                refreshList({
                    'filter[_per_page]': per_page,
                    'filter[_page]': page,
                    'filter[_sort_order]': sort_order,
                    'filter[_sort_by]':sort_by,
                    'filter[tags][value]': tags,
                    'filter[name][value]': name,
                });
            })

        },
        refreshList: function (filters) {
            refreshList(filters);
        }
    }
}();

class MediaDropZone{
    constructor(element) {
        this.element = element;
        this.context = this.element.dataset.context;
        this.provider = this.element.dataset.provider;
        this.uploadForm = this.element.querySelector('#upload-form');
        const uniqueId = this.element.dataset.uniqueId;

        let previewNode = this.element.querySelector(".dropzone-item");
        previewNode.id = "";
        let previewTemplate = previewNode.parentNode.innerHTML;
        previewNode.parentNode.removeChild(previewNode);

        const that = this



        let fileDropzone = new Dropzone(this.element, {
            init: function () {
                this.on("success", function (file, response) {
                    that.changeButtonRow(file, response);
                    CMSMediaAdmin.refreshList();
                });
                this.on('error', function (file, response) {
                    if (response.duplicate) {
                        that.changeButtonRow(file, response);
                    }
                });
            },
            url: this.uploadForm.action, // Set the url
            params: {context: this.context, provider: this.provider, oneuploader: true},
            thumbnailWidth: 80,
            thumbnailHeight: 80,
            parallelUploads: 20,
            previewTemplate: previewTemplate,
            previewsContainer: "#previews", // Define the container to display the previews
            clickable: ".fileinput-zone" // Define the element that should be used as click trigger to select files.
        });


        // Update the total progress bar
        fileDropzone.on("totaluploadprogress", function (progress) {
            document.querySelector("#total-progress .progress-bar").style.width = progress + "%";
        });

        fileDropzone.on("sending", function (file, xhr, formData) {
            let tags = document.querySelector('#'+uniqueId+'_tags').value;

            formData.append("tags", tags);
            // Show the total progress bar when upload starts
            document.querySelector("#total-progress").style.opacity = "1";
        });

        // Hide the total progress bar when nothing's uploading anymore
        fileDropzone.on("queuecomplete", function (progress) {
            document.querySelector("#total-progress").style.opacity = "0";
            // CMSMediaAdmin.refreshList({});
        });

        // Setup the buttons for all transfers
        // The "add files" button doesn't need to be setup because the config
        // `clickable` has already been specified.
        document.querySelector("#actions .cancel").onclick = function () {
            fileDropzone.removeAllFiles(true);
        };
    }
    changeButtonRow(file, response) {

        let editButton = file.previewElement.querySelector("[data-dz-url]")
        editButton.href = response.url;
        editButton.classList.remove('d-none');
        file.previewElement.querySelector(".progress").classList.add('d-none');
    }
}

(function ($, undefined) {
    "use strict";
    var link = document.createElement('a');
    link.className = "delete-tag dialog-link float-end ";
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

            if (this.get_node(obj).children && this.get_node(obj).children.length) {
                return obj;
            }

            if (obj && $(obj).hasClass('sortable-tag')) {

                var tmp = link.cloneNode(true);
                var node = this.get_node(obj);
                tmp.href = node.data.delete_link;
                obj.insertBefore(tmp, obj.childNodes[2]);
            }
            return obj;
        };
    };
})($);

KTUtil.onDOMContentLoaded(function () {
    CMSMediaAdmin.init();
    document.body.addEventListener('shown.bs.modal', function (event) {
        let dropzone = event.target.querySelector('#dropzone_area');
        if (dropzone) {
            new MediaDropZone(dropzone);
        }
    })

});