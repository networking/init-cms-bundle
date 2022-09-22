import Vue from 'vue';
import VueI18n from 'vue-i18n'
import Editor from './components/Editor.vue'


Vue.use(VueI18n)
const i18n = new VueI18n({
    locale: 'en',
    messages: {
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
})

new Vue({
    i18n,
    render: h => h(Editor),
}).$mount('#image-editor');
