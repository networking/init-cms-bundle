<style>
.imageContainer {
    position: relative;
}

.image {
    opacity: 1;
    display: block;
    transition: .5s ease;
    backface-visibility: hidden;
}

.middle {
    transition: .5s ease;
    opacity: 0;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    text-align: center;
}

.imageContainer:hover .image {
    opacity: 0.3;
}

.imageContainer:hover .middle {
    opacity: 1;
}
</style>
<template>
    <div class="imageEditorApp py-10">
        <div class="imageContainer d-flex justify-content-center align-items-center">
<!--            <img :src="imageURL" class="img-responsive image" @contextmenu.prevent="$refs.menu.open">-->
            <img :src="imageURL" class="img-fluid center-block image">
            <div class="middle">
                <div class="text"><a href="" class="btn btn-default" @click.prevent="editImage"><i class="fa fa-magic fa-small"></i> {{ $t('edit_image')}}</a></div>
            </div>
        </div>
        <div class="alert mt-10" :class="[alertType === 'error'?'alert-danger':'alert-success']" v-if="alert" role="alert" v-html="alertMessage"></div>
        <div id="imageModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog  modal-fullscreen " role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <h4 class="modal-title">{{ $t('created_image') }}</h4>

                        <!--begin::Close-->
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                    </div>
                    <div class="modal-body align-center">
                        <div class="row">
                            <div class="col-md-6 align-center">
                                <h3>{{ $t('original_image') }}</h3>
                                <p><img :src="imageURL" class="img-fluid center-block"/></p></div>
                            <div class="col-md-6 align-center">
                                <h3>{{ $t('new_image') }}</h3>
                                <p><img :src="newImage" class="img-fluid center-block"/></p></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">{{ $t('cancel') }}</button>
                        <button type="button" class="btn btn-sm btn-warning" @click.prevent="updateImage">{{ $t('replace_image') }}</button>
                        <button type="button" class="btn btn-sm btn-primary" @click.prevent="cloneImage">{{ $t('create_new_image') }}</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <div id="confirmModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ $t('are_you_sure') }}</h4>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <p>
                        {{ $t('message.cannot_be_undone') }}
                        </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-warning" @click.prevent="confirmUpdate">{{ $t('continue') }}</button>
                    <button type="button" class="btn btn-sm btn-light" @click.prevent="rejectUpdate">{{ $t('cancel') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <div id="editorModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-fullscreen" role="document">
                <div class="modal-content">
                    <div id="imageEditor" class="h-100"></div>
                </div>
            </div>
        </div>
    </div>

</template>
<script>
    import axios from 'axios';
    import FilerobotImageEditor, {
        TABS,
        TOOLS,
    }from 'filerobot-image-editor';

    const ALLOWED_FILE_EXTENTIONS = ['gif', 'jpg', 'jpeg', 'png'];
    let imageContainer = document.getElementById('image-container');
    let axiosConfig = {headers: {'X-Requested-With': 'XMLHttpRequest'}};
    let langauge = 'de'//imageContainer.getAttribute('data-lang');

    export default {
        name: 'Editor',
        mounted() {
            this.imageURL = imageContainer.getAttribute('data-image-src');
            this.id = imageContainer.getAttribute('data-image-id');
            this.context = imageContainer.getAttribute('data-image-context');
            this.provider = imageContainer.getAttribute('data-image-provider');
            this.fileExtension = this.imageURL.slice((this.imageURL.lastIndexOf(".") - 1 >>> 0) + 2);

            this.imageModal = new bootstrap.Modal(document.getElementById('imageModal'), {
                keyboard: false
            })

            this.confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'), {
                keyboard: false
            })

            this.editorModal = new bootstrap.Modal(document.getElementById('editorModal'), {
                keyboard: false
            })
        },
        data() {
            this.$i18n.locale = langauge;
            return {
                locale: langauge,
                imageURL: '',
                id: '',
                context: '',
                provider: '',
                imageEditor: '',
                newImage: '',
                alert: false,
                alertMessage: false,
                alertType: false,
                fileExtension: '',
                imageModal: {},
                confirmModal: {},
                editorModal: {},
                config: {},
            }
        },
        watch: {
            locale (val) {
                this.$i18n.locale = val
            }
        },
        methods: {
            editImage(){
                this.alertMessage = false;
                if(!ALLOWED_FILE_EXTENTIONS.includes(this.fileExtension)){
                    this.alert = true;
                    this.alertType = 'error';
                    this.alertMessage = this.$i18n.t('not_allowed_extension');
                    return;
                }

                this.alert = false;
                this.alertType = false;
                this.alertMessage = false;
                this.open(this.imageURL);
            },
            open(url) {
                this.config.source = url
                this.imageEditor = new FilerobotImageEditor( document.querySelector('#imageEditor'),
                    {source: url, language: this.locale});
                this.editorModal.show()

                this.imageEditor.render({
                    onClose: (closingReason) => {
                        this.imageEditor.terminate();
                        this.editorModal.hide()
                    },
                    onBeforeSave: (imageFileInfo) => {
                        return false
                    },
                    onSave: (imageData, imageDesignState) => {
                       this.download(imageData)
                    }
                });
            },
            download({ fullName, mimeType, imageCanvas }){
                this.newImage = imageCanvas.toDataURL();
                this.alert = false;
                this.alertType = false;
                this.alertMessage = false;
                this.imageModal.show()
                this.editorModal.hide()
                return false;
            },
            cloneImage(){
                this.imageModal.hide()
                axios
                    .post('/admin/cms/media/clone', {
                        'id': this.id,
                        'clone': true,
                        'provider': this.provider,
                        'context': this.context,
                        'file': this.newImage
                    }, axiosConfig).
                then(response => {
                    this.alert = true;

                    if(response.data.success){
                        this.alertMessage = this.$t('message.cloned', {'url': response.data.url});
                        this.alertType = 'success';
                    }else{
                        this.alertMessage = response.data.error;
                        this.alertType = 'error';
                    }
                }).catch(function (error) {
                    console.log(error);
                });
            },
            updateImage(){

                this.imageModal.hide()
                this.confirmModal.show()
            },
            rejectUpdate(){

                this.imageModal.show()
                this.confirmModal.hide()
            },
            confirmUpdate(){
                this.confirmModal.hide()

                axios
                    .post('/admin/cms/media/clone', {
                        'id': this.id,
                        'clone': false,
                        'provider': this.provider,
                        'context': this.context,
                        'file': this.newImage
                    }, axiosConfig).
                then(response => {
                    if(response.data.success){
                        window.location.href = response.data.url;
                    }else{
                        this.alert = true;
                        this.alertMessage = response.data.error;
                        this.alertType = 'error';
                    }
                }).catch(function (error) {
                    console.log(error);
                });
            }
        }
    };
</script>
