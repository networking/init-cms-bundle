<template>
    <div class="imageEditorApp">
        <div class="imageContainer">
<!--            <img :src="imageURL" class="img-responsive image" @contextmenu.prevent="$refs.menu.open">-->
            <img :src="imageURL" class="img-responsive center-block image">
            <div class="middle">
                <div class="text"><a href="" class="btn btn-default" @click.prevent="editImage"><i class="fa fa-magic fa-small"></i> {{ $t('edit_image')}}</a></div>
            </div>
        </div>
        <br>
        <div class="alert " :class="[alertType === 'error'?'alert-danger':'alert-success']" v-if="alert" role="alert" v-html="alertMessage"></div>
        <br>
        <div id="imageModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog  modal-full" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">{{ $t('created_image') }}</h4>
                    </div>
                    <div class="modal-body align-center">
                        <div class="row">
                            <div class="col-md-6 align-center">
                                <h3>{{ $t('original_image') }}</h3>
                                <p><img :src="imageURL" class="img-responsive center-block"/></p></div>
                            <div class="col-md-6 align-center">
                                <h3>{{ $t('new_image') }}</h3>
                                <p><img :src="newImage" class="img-responsive center-block"/></p></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ $t('cancel') }}</button>
                        <button type="button" class="btn btn-warning" @click.prevent="updateImage">{{ $t('replace_image') }}</button>
                        <button type="button" class="btn btn-primary" @click.prevent="cloneImage">{{ $t('create_new_image') }}</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <div id="confirmModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ $t('are_you_sure') }}</h4>
                </div>
                <div class="modal-body">
                    <p>
                        {{ $t('message.cannot_be_undone') }}
                        </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" @click.prevent="confirmUpdate">{{ $t('continue') }}</button>
                    <button type="button" class="btn btn-default" @click.prevent="rejectUpdate">{{ $t('cancel') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    </div>
</template>
<i18n>
{
    "en": {
        "not_allowed_extension": "Unfortunately the image cannot be edited",
        "created_image":"Created Image",
        "create_new_image":"Create a new image",
        "replace_image":"Replace current image",
        "are_you_sure": "Are You Sure?",
        "edit_image": "Edit image",
        "continue":"Yes, continue",
        "cancel":"Cancel",
        "original_image":"Original Image",
        "new_image":"New Image",
        "message": {
            "cloned": "Image has been created, follow this <a href=\"{url}\">link to view the new image</a>",
            "cannot_be_undone": "This action will replace the current image, you can not undo this action"
        }
    },
    "de": {
        "not_allowed_extension": "Bild kann leider nicht bearbeitet werden",
        "created_image":"Erstelltes Bild",
        "create_new_image":"Ein neues Bild erstellen",
        "replace_image":"Aktuelles Bild ersetzen",
        "are_you_sure": "Sind Sie Sicher",
        "edit_image": "Bild bearbeiten",
        "continue":"Ja, weiter",
        "cancel":"Abrechen",
        "original_image":"Originalbild",
        "new_image":"Neues Bild",
        "message": {
            "cloned": "Das Bild wurde erstellt, folgen Sie diesem <a href=\"{url}\">Link, um das neue Bild anzusehen</a>.",
            "cannot_be_undone": "Diese Aktion ersetzt das aktuelle Bild, Sie können diese Aktion nicht rückgängig machen."
        }
    }
}
</i18n>
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
<script>
    import Vue from 'vue';
    import axios from "axios";
    // import { VueContext } from 'vue-context';
    import $ from 'jquery';
    import FilerobotImageEditor from './imageEditor'

    const ALLOWED_FILE_EXTENTIONS = ['gif', 'jpg', 'jpeg', 'png'];
    const LANGUAGES = ['de', 'en'];
    let imageContainer = document.getElementById('image-container');
    let axiosConfig = {headers: {'X-Requested-With': 'XMLHttpRequest'}};
    let langauge = imageContainer.getAttribute('data-lang');
    if(!LANGUAGES.includes(langauge)){
        langauge = 'en';
    }

    export default {
        name: 'Editor',
        // components: {
        //     VueContext
        // },
        mounted() {
            let config = {
                colorScheme: 'light',
                tools: ['adjust', 'effects', 'filters', 'rotate','crop','resize'],
                language: langauge,
                translations: {
                    en: {
                        'toolbar.download': 'Save',
                    },
                    de: {
                        "header.image_editor_title": "Bild bearbiten",
                        "toolbar.download": "Speichern",
                        "toolbar.save": "Speichern",
                        "toolbar.apply": "Anwenden",
                        "toolbar.cancel": "Abbrechen",
                        "toolbar.go_back": "Zurück",
                        "toolbar.adjust": "Anpassen",
                        "toolbar.effects": "Effekte",
                        "toolbar.filters": "Filter",
                        "toolbar.orientation": "Orientierung",
                        "toolbar.crop": "Zuschneiden",
                        "toolbar.resize": "Größe ändern",
                        "toolbar.watermark": "Wasserzeichen",
                        "adjust.brightness": "Helligkeit",
                        "adjust.contrast": "Kontrast",
                        "adjust.exposure": "Belichtung",
                        "adjust.saturation": "Farbsättigung",
                        "orientation.rotate_l": "Nach links drehen",
                        "orientation.rotate_r": "Nach rechts drehen",
                        "orientation.flip_h": "Horizontal spiegeln",
                        "orientation.flip_v": "Vertikal spiegeln",
                        "pre_resize.title": "Möchten Sie die Auflösung reduzieren, bevor Sie das Bild bearbeiten?",
                        "pre_resize.keep_original_resolution": "Originalauflösung beibehalten",
                        "pre_resize.resize_n_continue": "Größe ändern & fortsetzen",
                        "footer.reset": "Zurücksetzen",
                        "footer.undo": "Rückgängig machen",
                        "footer.redo": "Wiederholen",
                        "spinner.label": "Verarbeitung...",
                        "warning.too_big_resolution": "Die Auflösung des Bildes ist zu groß für das Web. Es kann zu Problemen mit der Leistung des Bildbearbeitungsprogramms führen.",
                        "common.width": "breite",
                        "common.height": "höhe",
                        "common.custom": "benutzerdefiniert",
                        "common.original": "Original",
                        "common.square": "quadratisch",
                        "common.opacity": "Opazität",
                        "common.apply_watermark": "Wasserzeichen anwenden"

                    }
                }
            }
            this.imageEditor = new FilerobotImageEditor(config, this.download);
            this.imageURL = imageContainer.getAttribute('data-image-src');
            this.id = imageContainer.getAttribute('data-image-id');
            this.context = imageContainer.getAttribute('data-image-context');
            this.provider = imageContainer.getAttribute('data-image-provider');
            this.fileExtension = this.imageURL.slice((this.imageURL.lastIndexOf(".") - 1 >>> 0) + 2);
        },
        data() {
            this.$i18n.locale = langauge;
            return {
                locale: 'en',
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
                this.imageEditor.open(this.imageURL);
            },
            download(newImage){
                this.newImage = newImage.canvas.toDataURL();
                this.alert = false;
                this.alertType = false;
                this.alertMessage = false;
                $('#imageModal').modal('show');
            },
            cloneImage(){
                $('#imageModal').modal('hide');
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
                $('#imageModal').modal('hide');
                $('#confirmModal').modal('show');
            },
            rejectUpdate(){
                $('#imageModal').modal('show');
                $('#confirmModal').modal('hide');
            },
            confirmUpdate(){
                $('#confirmModal').modal('hide');
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
