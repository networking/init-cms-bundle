import Vue from 'vue';
import VueI18n from 'vue-i18n'
import Editor from './components/Editor.vue'
import './scss/styles.scss'

Vue.use(VueI18n)
const i18n = new VueI18n({
    locale: 'en',
})

new Vue({
    i18n,
    render: h => h(Editor),
}).$mount('#image-editor');
