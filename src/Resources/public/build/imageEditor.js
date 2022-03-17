/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/Resources/public/js/components/Editor.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/Resources/public/js/components/Editor.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var core_js_modules_es_array_includes_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.array.includes.js */ "../../../node_modules/core-js/modules/es.array.includes.js");
/* harmony import */ var core_js_modules_es_array_includes_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_includes_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_array_slice_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.array.slice.js */ "../../../node_modules/core-js/modules/es.array.slice.js");
/* harmony import */ var core_js_modules_es_array_slice_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_slice_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var core_js_modules_es_array_last_index_of_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! core-js/modules/es.array.last-index-of.js */ "../../../node_modules/core-js/modules/es.array.last-index-of.js");
/* harmony import */ var core_js_modules_es_array_last_index_of_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_last_index_of_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _node_modules_axios__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../node_modules/axios */ "./node_modules/axios/index.js");
/* harmony import */ var _node_modules_axios__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_node_modules_axios__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _imageEditor__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./imageEditor */ "./src/Resources/public/js/components/imageEditor.js");



//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



var ALLOWED_FILE_EXTENTIONS = ['gif', 'jpg', 'jpeg', 'png'];
var LANGUAGES = ['de', 'en'];
var imageContainer = document.getElementById('image-container');
var axiosConfig = {
  headers: {
    'X-Requested-With': 'XMLHttpRequest'
  }
};
var langauge = imageContainer.getAttribute('data-lang');

if (!LANGUAGES.includes(langauge)) {
  langauge = 'en';
}

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  name: 'Editor',
  // components: {
  //     VueContext
  // },
  mounted: function mounted() {
    var config = {
      colorScheme: 'light',
      tools: ['adjust', 'effects', 'filters', 'rotate', 'crop', 'resize'],
      language: langauge,
      translations: {
        en: {
          'toolbar.download': 'Save'
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
    };
    this.imageEditor = new _imageEditor__WEBPACK_IMPORTED_MODULE_4__["default"](config, this.download);
    this.imageURL = imageContainer.getAttribute('data-image-src');
    this.id = imageContainer.getAttribute('data-image-id');
    this.context = imageContainer.getAttribute('data-image-context');
    this.provider = imageContainer.getAttribute('data-image-provider');
    this.fileExtension = this.imageURL.slice((this.imageURL.lastIndexOf(".") - 1 >>> 0) + 2);
  },
  data: function data() {
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
      fileExtension: ''
    };
  },
  watch: {
    locale: function locale(val) {
      this.$i18n.locale = val;
    }
  },
  methods: {
    editImage: function editImage() {
      this.alertMessage = false;

      if (!ALLOWED_FILE_EXTENTIONS.includes(this.fileExtension)) {
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
    download: function download(_ref) {
      var status = _ref.status,
          imageName = _ref.imageName,
          imageMime = _ref.imageMime,
          canvas = _ref.canvas;
      this.newImage = canvas.toDataURL();
      this.alert = false;
      this.alertType = false;
      this.alertMessage = false;
      $('#imageModal').modal('show');
      return false;
    },
    cloneImage: function cloneImage() {
      var _this = this;

      $('#imageModal').modal('hide');
      _node_modules_axios__WEBPACK_IMPORTED_MODULE_3___default().post('/admin/cms/media/clone', {
        'id': this.id,
        'clone': true,
        'provider': this.provider,
        'context': this.context,
        'file': this.newImage
      }, axiosConfig).then(function (response) {
        _this.alert = true;

        if (response.data.success) {
          _this.alertMessage = _this.$t('message.cloned', {
            'url': response.data.url
          });
          _this.alertType = 'success';
        } else {
          _this.alertMessage = response.data.error;
          _this.alertType = 'error';
        }
      })["catch"](function (error) {
        console.log(error);
      });
    },
    updateImage: function updateImage() {
      $('#imageModal').modal('hide');
      $('#confirmModal').modal('show');
    },
    rejectUpdate: function rejectUpdate() {
      $('#imageModal').modal('show');
      $('#confirmModal').modal('hide');
    },
    confirmUpdate: function confirmUpdate() {
      var _this2 = this;

      $('#confirmModal').modal('hide');
      _node_modules_axios__WEBPACK_IMPORTED_MODULE_3___default().post('/admin/cms/media/clone', {
        'id': this.id,
        'clone': false,
        'provider': this.provider,
        'context': this.context,
        'file': this.newImage
      }, axiosConfig).then(function (response) {
        if (response.data.success) {
          window.location.href = response.data.url;
        } else {
          _this2.alert = true;
          _this2.alertMessage = response.data.error;
          _this2.alertType = 'error';
        }
      })["catch"](function (error) {
        console.log(error);
      });
    }
  }
});

/***/ }),

/***/ "./src/Resources/public/js/components/imageEditor.js":
/*!***********************************************************!*\
  !*** ./src/Resources/public/js/components/imageEditor.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FilerobotImageEditor)
/* harmony export */ });
/* harmony import */ var core_js_modules_es_object_set_prototype_of_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.object.set-prototype-of.js */ "../../../node_modules/core-js/modules/es.object.set-prototype-of.js");
/* harmony import */ var core_js_modules_es_object_set_prototype_of_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_set_prototype_of_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_object_get_prototype_of_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.object.get-prototype-of.js */ "../../../node_modules/core-js/modules/es.object.get-prototype-of.js");
/* harmony import */ var core_js_modules_es_object_get_prototype_of_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_get_prototype_of_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! core-js/modules/es.object.to-string.js */ "../../../node_modules/core-js/modules/es.object.to-string.js");
/* harmony import */ var core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_to_string_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var core_js_modules_es_reflect_construct_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! core-js/modules/es.reflect.construct.js */ "../../../node_modules/core-js/modules/es.reflect.construct.js");
/* harmony import */ var core_js_modules_es_reflect_construct_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_reflect_construct_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var core_js_modules_es_object_create_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! core-js/modules/es.object.create.js */ "../../../node_modules/core-js/modules/es.object.create.js");
/* harmony import */ var core_js_modules_es_object_create_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_create_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var core_js_modules_es_object_define_property_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! core-js/modules/es.object.define-property.js */ "../../../node_modules/core-js/modules/es.object.define-property.js");
/* harmony import */ var core_js_modules_es_object_define_property_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_object_define_property_js__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var core_js_modules_es_symbol_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! core-js/modules/es.symbol.js */ "../../../node_modules/core-js/modules/es.symbol.js");
/* harmony import */ var core_js_modules_es_symbol_js__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_symbol_js__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var core_js_modules_es_symbol_description_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! core-js/modules/es.symbol.description.js */ "../../../node_modules/core-js/modules/es.symbol.description.js");
/* harmony import */ var core_js_modules_es_symbol_description_js__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_symbol_description_js__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var core_js_modules_es_symbol_iterator_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! core-js/modules/es.symbol.iterator.js */ "../../../node_modules/core-js/modules/es.symbol.iterator.js");
/* harmony import */ var core_js_modules_es_symbol_iterator_js__WEBPACK_IMPORTED_MODULE_8___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_symbol_iterator_js__WEBPACK_IMPORTED_MODULE_8__);
/* harmony import */ var core_js_modules_es_array_iterator_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! core-js/modules/es.array.iterator.js */ "../../../node_modules/core-js/modules/es.array.iterator.js");
/* harmony import */ var core_js_modules_es_array_iterator_js__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_array_iterator_js__WEBPACK_IMPORTED_MODULE_9__);
/* harmony import */ var core_js_modules_es_string_iterator_js__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! core-js/modules/es.string.iterator.js */ "../../../node_modules/core-js/modules/es.string.iterator.js");
/* harmony import */ var core_js_modules_es_string_iterator_js__WEBPACK_IMPORTED_MODULE_10___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_string_iterator_js__WEBPACK_IMPORTED_MODULE_10__);
/* harmony import */ var core_js_modules_web_dom_collections_iterator_js__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! core-js/modules/web.dom-collections.iterator.js */ "../../../node_modules/core-js/modules/web.dom-collections.iterator.js");
/* harmony import */ var core_js_modules_web_dom_collections_iterator_js__WEBPACK_IMPORTED_MODULE_11___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_web_dom_collections_iterator_js__WEBPACK_IMPORTED_MODULE_11__);
/* harmony import */ var filerobotImageEditor__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! filerobotImageEditor */ "filerobotImageEditor");
/* harmony import */ var filerobotImageEditor__WEBPACK_IMPORTED_MODULE_12___default = /*#__PURE__*/__webpack_require__.n(filerobotImageEditor__WEBPACK_IMPORTED_MODULE_12__);
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }














function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); Object.defineProperty(subClass, "prototype", { writable: false }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } else if (call !== void 0) { throw new TypeError("Derived constructors may only return object or undefined"); } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }



var FilerobotImageEditor = /*#__PURE__*/function (_FilerobotImageEditor) {
  _inherits(FilerobotImageEditor, _FilerobotImageEditor);

  var _super = _createSuper(FilerobotImageEditor);

  function FilerobotImageEditor() {
    _classCallCheck(this, FilerobotImageEditor);

    return _super.apply(this, arguments);
  }

  return _createClass(FilerobotImageEditor);
}((filerobotImageEditor__WEBPACK_IMPORTED_MODULE_12___default()));



/***/ }),

/***/ "./src/Resources/public/js/filebot.js":
/*!********************************************!*\
  !*** ./src/Resources/public/js/filebot.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.runtime.esm.js");
/* harmony import */ var vue_i18n__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! vue-i18n */ "./node_modules/vue-i18n/dist/vue-i18n.esm.js");
/* harmony import */ var _components_Editor_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/Editor.vue */ "./src/Resources/public/js/components/Editor.vue");



vue__WEBPACK_IMPORTED_MODULE_1__["default"].use(vue_i18n__WEBPACK_IMPORTED_MODULE_2__["default"]);
var i18n = new vue_i18n__WEBPACK_IMPORTED_MODULE_2__["default"]({
  locale: 'en',
  messages: {
    "en": {
      "not_allowed_extension": "Unfortunately the image cannot be edited",
      "created_image": "Created Image",
      "create_new_image": "Create a new image",
      "replace_image": "Replace current image",
      "are_you_sure": "Are You Sure?",
      "edit_image": "Edit image",
      "continue": "Yes, continue",
      "cancel": "Cancel",
      "original_image": "Original Image",
      "new_image": "New Image",
      "message": {
        "cloned": "Image has been created, follow this <a href=\"{url}\">link to view the new image</a>",
        "cannot_be_undone": "This action will replace the current image, you can not undo this action"
      }
    },
    "de": {
      "not_allowed_extension": "Bild kann leider nicht bearbeitet werden",
      "created_image": "Erstelltes Bild",
      "create_new_image": "Ein neues Bild erstellen",
      "replace_image": "Aktuelles Bild ersetzen",
      "are_you_sure": "Sind Sie Sicher",
      "edit_image": "Bild bearbeiten",
      "continue": "Ja, weiter",
      "cancel": "Abrechen",
      "original_image": "Originalbild",
      "new_image": "Neues Bild",
      "message": {
        "cloned": "Das Bild wurde erstellt, folgen Sie diesem <a href=\"{url}\">Link, um das neue Bild anzusehen</a>.",
        "cannot_be_undone": "Diese Aktion ersetzt das aktuelle Bild, Sie können diese Aktion nicht rückgängig machen."
      }
    }
  }
});
new vue__WEBPACK_IMPORTED_MODULE_1__["default"]({
  i18n: i18n,
  render: function render(h) {
    return h(_components_Editor_vue__WEBPACK_IMPORTED_MODULE_0__["default"]);
  }
}).$mount('#image-editor');

/***/ }),

/***/ "./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-4[0].rules[0].use[0]!./node_modules/@symfony/webpack-encore/node_modules/css-loader/dist/cjs.js??clonedRuleSet-4[0].rules[0].use[1]!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/Resources/public/js/components/Editor.vue?vue&type=style&index=0&lang=css&":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-4[0].rules[0].use[0]!./node_modules/@symfony/webpack-encore/node_modules/css-loader/dist/cjs.js??clonedRuleSet-4[0].rules[0].use[1]!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/Resources/public/js/components/Editor.vue?vue&type=style&index=0&lang=css& ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./src/Resources/public/js/components/Editor.vue":
/*!*******************************************************!*\
  !*** ./src/Resources/public/js/components/Editor.vue ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Editor_vue_vue_type_template_id_72ce7433___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Editor.vue?vue&type=template&id=72ce7433& */ "./src/Resources/public/js/components/Editor.vue?vue&type=template&id=72ce7433&");
/* harmony import */ var _Editor_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Editor.vue?vue&type=script&lang=js& */ "./src/Resources/public/js/components/Editor.vue?vue&type=script&lang=js&");
/* harmony import */ var _Editor_vue_vue_type_style_index_0_lang_css___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Editor.vue?vue&type=style&index=0&lang=css& */ "./src/Resources/public/js/components/Editor.vue?vue&type=style&index=0&lang=css&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! !../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");



;


/* normalize component */

var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__["default"])(
  _Editor_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _Editor_vue_vue_type_template_id_72ce7433___WEBPACK_IMPORTED_MODULE_0__.render,
  _Editor_vue_vue_type_template_id_72ce7433___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "src/Resources/public/js/components/Editor.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./src/Resources/public/js/components/Editor.vue?vue&type=script&lang=js&":
/*!********************************************************************************!*\
  !*** ./src/Resources/public/js/components/Editor.vue?vue&type=script&lang=js& ***!
  \********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Editor_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1[0].rules[0].use[0]!../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Editor.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1[0].rules[0].use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/Resources/public/js/components/Editor.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_1_0_rules_0_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Editor_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./src/Resources/public/js/components/Editor.vue?vue&type=style&index=0&lang=css&":
/*!****************************************************************************************!*\
  !*** ./src/Resources/public/js/components/Editor.vue?vue&type=style&index=0&lang=css& ***!
  \****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_4_0_rules_0_use_0_node_modules_symfony_webpack_encore_node_modules_css_loader_dist_cjs_js_clonedRuleSet_4_0_rules_0_use_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_vue_loader_lib_index_js_vue_loader_options_Editor_vue_vue_type_style_index_0_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-4[0].rules[0].use[0]!../../../../../node_modules/@symfony/webpack-encore/node_modules/css-loader/dist/cjs.js??clonedRuleSet-4[0].rules[0].use[1]!../../../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Editor.vue?vue&type=style&index=0&lang=css& */ "./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-4[0].rules[0].use[0]!./node_modules/@symfony/webpack-encore/node_modules/css-loader/dist/cjs.js??clonedRuleSet-4[0].rules[0].use[1]!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/Resources/public/js/components/Editor.vue?vue&type=style&index=0&lang=css&");


/***/ }),

/***/ "./src/Resources/public/js/components/Editor.vue?vue&type=template&id=72ce7433&":
/*!**************************************************************************************!*\
  !*** ./src/Resources/public/js/components/Editor.vue?vue&type=template&id=72ce7433& ***!
  \**************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Editor_vue_vue_type_template_id_72ce7433___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   "staticRenderFns": () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Editor_vue_vue_type_template_id_72ce7433___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Editor_vue_vue_type_template_id_72ce7433___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Editor.vue?vue&type=template&id=72ce7433& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/Resources/public/js/components/Editor.vue?vue&type=template&id=72ce7433&");


/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/Resources/public/js/components/Editor.vue?vue&type=template&id=72ce7433&":
/*!*****************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/Resources/public/js/components/Editor.vue?vue&type=template&id=72ce7433& ***!
  \*****************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "render": () => (/* binding */ render),
/* harmony export */   "staticRenderFns": () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function () {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "imageEditorApp" }, [
    _c("div", { staticClass: "imageContainer" }, [
      _c("img", {
        staticClass: "img-responsive center-block image",
        attrs: { src: _vm.imageURL },
      }),
      _vm._v(" "),
      _c("div", { staticClass: "middle" }, [
        _c("div", { staticClass: "text" }, [
          _c(
            "a",
            {
              staticClass: "btn btn-default",
              attrs: { href: "" },
              on: {
                click: function ($event) {
                  $event.preventDefault()
                  return _vm.editImage.apply(null, arguments)
                },
              },
            },
            [
              _c("i", { staticClass: "fa fa-magic fa-small" }),
              _vm._v(" " + _vm._s(_vm.$t("edit_image"))),
            ]
          ),
        ]),
      ]),
    ]),
    _vm._v(" "),
    _c("br"),
    _vm._v(" "),
    _vm.alert
      ? _c("div", {
          staticClass: "alert ",
          class: [_vm.alertType === "error" ? "alert-danger" : "alert-success"],
          attrs: { role: "alert" },
          domProps: { innerHTML: _vm._s(_vm.alertMessage) },
        })
      : _vm._e(),
    _vm._v(" "),
    _c("br"),
    _vm._v(" "),
    _c(
      "div",
      {
        staticClass: "modal fade",
        attrs: { id: "imageModal", tabindex: "-1", role: "dialog" },
      },
      [
        _c(
          "div",
          {
            staticClass: "modal-dialog  modal-full",
            attrs: { role: "document" },
          },
          [
            _c("div", { staticClass: "modal-content" }, [
              _c("div", { staticClass: "modal-header" }, [
                _vm._m(0),
                _vm._v(" "),
                _c("h4", { staticClass: "modal-title" }, [
                  _vm._v(_vm._s(_vm.$t("created_image"))),
                ]),
              ]),
              _vm._v(" "),
              _c("div", { staticClass: "modal-body align-center" }, [
                _c("div", { staticClass: "row" }, [
                  _c("div", { staticClass: "col-md-6 align-center" }, [
                    _c("h3", [_vm._v(_vm._s(_vm.$t("original_image")))]),
                    _vm._v(" "),
                    _c("p", [
                      _c("img", {
                        staticClass: "img-responsive center-block",
                        attrs: { src: _vm.imageURL },
                      }),
                    ]),
                  ]),
                  _vm._v(" "),
                  _c("div", { staticClass: "col-md-6 align-center" }, [
                    _c("h3", [_vm._v(_vm._s(_vm.$t("new_image")))]),
                    _vm._v(" "),
                    _c("p", [
                      _c("img", {
                        staticClass: "img-responsive center-block",
                        attrs: { src: _vm.newImage },
                      }),
                    ]),
                  ]),
                ]),
              ]),
              _vm._v(" "),
              _c("div", { staticClass: "modal-footer" }, [
                _c(
                  "button",
                  {
                    staticClass: "btn btn-default",
                    attrs: { type: "button", "data-dismiss": "modal" },
                  },
                  [_vm._v(_vm._s(_vm.$t("cancel")))]
                ),
                _vm._v(" "),
                _c(
                  "button",
                  {
                    staticClass: "btn btn-warning",
                    attrs: { type: "button" },
                    on: {
                      click: function ($event) {
                        $event.preventDefault()
                        return _vm.updateImage.apply(null, arguments)
                      },
                    },
                  },
                  [_vm._v(_vm._s(_vm.$t("replace_image")))]
                ),
                _vm._v(" "),
                _c(
                  "button",
                  {
                    staticClass: "btn btn-primary",
                    attrs: { type: "button" },
                    on: {
                      click: function ($event) {
                        $event.preventDefault()
                        return _vm.cloneImage.apply(null, arguments)
                      },
                    },
                  },
                  [_vm._v(_vm._s(_vm.$t("create_new_image")))]
                ),
              ]),
            ]),
          ]
        ),
      ]
    ),
    _vm._v(" "),
    _c(
      "div",
      {
        staticClass: "modal fade",
        attrs: { id: "confirmModal", tabindex: "-1", role: "dialog" },
      },
      [
        _c(
          "div",
          { staticClass: "modal-dialog", attrs: { role: "document" } },
          [
            _c("div", { staticClass: "modal-content" }, [
              _c("div", { staticClass: "modal-header" }, [
                _vm._m(1),
                _vm._v(" "),
                _c("h4", { staticClass: "modal-title" }, [
                  _vm._v(_vm._s(_vm.$t("are_you_sure"))),
                ]),
              ]),
              _vm._v(" "),
              _c("div", { staticClass: "modal-body" }, [
                _c("p", [
                  _vm._v(
                    "\n                        " +
                      _vm._s(_vm.$t("message.cannot_be_undone")) +
                      "\n                        "
                  ),
                ]),
              ]),
              _vm._v(" "),
              _c("div", { staticClass: "modal-footer" }, [
                _c(
                  "button",
                  {
                    staticClass: "btn btn-warning",
                    attrs: { type: "button" },
                    on: {
                      click: function ($event) {
                        $event.preventDefault()
                        return _vm.confirmUpdate.apply(null, arguments)
                      },
                    },
                  },
                  [_vm._v(_vm._s(_vm.$t("continue")))]
                ),
                _vm._v(" "),
                _c(
                  "button",
                  {
                    staticClass: "btn btn-default",
                    attrs: { type: "button" },
                    on: {
                      click: function ($event) {
                        $event.preventDefault()
                        return _vm.rejectUpdate.apply(null, arguments)
                      },
                    },
                  },
                  [_vm._v(_vm._s(_vm.$t("cancel")))]
                ),
              ]),
            ]),
          ]
        ),
      ]
    ),
  ])
}
var staticRenderFns = [
  function () {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c(
      "button",
      {
        staticClass: "close",
        attrs: {
          type: "button",
          "data-dismiss": "modal",
          "aria-label": "Close",
        },
      },
      [_c("span", { attrs: { "aria-hidden": "true" } }, [_vm._v("×")])]
    )
  },
  function () {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c(
      "button",
      {
        staticClass: "close",
        attrs: {
          type: "button",
          "data-dismiss": "modal",
          "aria-label": "Close",
        },
      },
      [_c("span", { attrs: { "aria-hidden": "true" } }, [_vm._v("×")])]
    )
  },
]
render._withStripped = true



/***/ }),

/***/ "filerobotImageEditor":
/*!***************************************!*\
  !*** external "FilerobotImageEditor" ***!
  \***************************************/
/***/ ((module) => {

module.exports = FilerobotImageEditor;

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/global */
/******/ 	(() => {
/******/ 		__webpack_require__.g = (function() {
/******/ 			if (typeof globalThis === 'object') return globalThis;
/******/ 			try {
/******/ 				return this || new Function('return this')();
/******/ 			} catch (e) {
/******/ 				if (typeof window === 'object') return window;
/******/ 			}
/******/ 		})();
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"imageEditor": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkinit_cms_bundle"] = self["webpackChunkinit_cms_bundle"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["vendors-node_modules_core-js_modules_es_array_includes_js-node_modules_core-js_modules_es_arr-c2de70"], () => (__webpack_require__("./src/Resources/public/js/filebot.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW1hZ2VFZGl0b3IuanMiLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQTBGQTtBQUNBO0FBQ0E7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUFBQTtBQUFBO0FBQUE7QUFBQTtBQUNBOztBQUNBO0FBQ0FDO0FBQ0E7O0FBRUE7QUFDQUMsZ0JBREE7QUFFQTtBQUNBO0FBQ0E7QUFDQUMsU0FMQSxxQkFLQTtBQUNBO0FBQ0FDLDBCQURBO0FBRUFDLHlFQUZBO0FBR0FDLHdCQUhBO0FBSUFDO0FBQ0FDO0FBQ0E7QUFEQSxTQURBO0FBSUFDO0FBQ0EsdURBREE7QUFFQSx5Q0FGQTtBQUdBLHFDQUhBO0FBSUEscUNBSkE7QUFLQSx1Q0FMQTtBQU1BLHFDQU5BO0FBT0Esc0NBUEE7QUFRQSxzQ0FSQTtBQVNBLHFDQVRBO0FBVUEsK0NBVkE7QUFXQSx1Q0FYQTtBQVlBLDBDQVpBO0FBYUEsOENBYkE7QUFjQSwyQ0FkQTtBQWVBLHVDQWZBO0FBZ0JBLHlDQWhCQTtBQWlCQSw4Q0FqQkE7QUFrQkEscURBbEJBO0FBbUJBLHNEQW5CQTtBQW9CQSxxREFwQkE7QUFxQkEsbURBckJBO0FBc0JBLG9HQXRCQTtBQXVCQSxnRkF2QkE7QUF3QkEscUVBeEJBO0FBeUJBLHdDQXpCQTtBQTBCQSw0Q0ExQkE7QUEyQkEsc0NBM0JBO0FBNEJBLDRDQTVCQTtBQTZCQSx1S0E3QkE7QUE4QkEsa0NBOUJBO0FBK0JBLGlDQS9CQTtBQWdDQSw4Q0FoQ0E7QUFpQ0EsdUNBakNBO0FBa0NBLHdDQWxDQTtBQW1DQSxzQ0FuQ0E7QUFvQ0E7QUFwQ0E7QUFKQTtBQUpBO0FBaURBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLEdBN0RBO0FBOERBQyxNQTlEQSxrQkE4REE7QUFDQTtBQUNBO0FBQ0FDLGtCQURBO0FBRUFDLGtCQUZBO0FBR0FDLFlBSEE7QUFJQUMsaUJBSkE7QUFLQUMsa0JBTEE7QUFNQUMscUJBTkE7QUFPQUMsa0JBUEE7QUFRQUMsa0JBUkE7QUFTQUMseUJBVEE7QUFVQUMsc0JBVkE7QUFXQUM7QUFYQTtBQWFBLEdBN0VBO0FBOEVBQztBQUNBWCxVQURBLGtCQUNBWSxHQURBLEVBQ0E7QUFDQTtBQUNBO0FBSEEsR0E5RUE7QUFtRkFDO0FBQ0FDLGFBREEsdUJBQ0E7QUFDQTs7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQSxLQWRBO0FBZUFDLFlBZkEsMEJBZUE7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0FDO0FBQ0E7QUFDQSxLQXRCQTtBQXVCQUMsY0F2QkEsd0JBdUJBO0FBQUE7O0FBQ0FEO0FBQ0FFLE1BQU1BLCtEQUFOLENBQ0Esd0JBREEsRUFDQTtBQUNBLHFCQURBO0FBRUEscUJBRkE7QUFHQSxpQ0FIQTtBQUlBLCtCQUpBO0FBS0E7QUFMQSxPQURBLEVBT0FFLFdBUEEsRUFRQUMsSUFSQSxDQVFBO0FBQ0E7O0FBRUE7QUFDQTtBQUFBO0FBQUE7QUFDQTtBQUNBLFNBSEEsTUFHQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLE9BbEJBLFdBa0JBO0FBQ0FDO0FBQ0EsT0FwQkE7QUFxQkEsS0E5Q0E7QUErQ0FDLGVBL0NBLHlCQStDQTtBQUNBUDtBQUNBQTtBQUNBLEtBbERBO0FBbURBUSxnQkFuREEsMEJBbURBO0FBQ0FSO0FBQ0FBO0FBQ0EsS0F0REE7QUF1REFTLGlCQXZEQSwyQkF1REE7QUFBQTs7QUFDQVQ7QUFDQUUsTUFBTUEsK0RBQU4sQ0FDQSx3QkFEQSxFQUNBO0FBQ0EscUJBREE7QUFFQSxzQkFGQTtBQUdBLGlDQUhBO0FBSUEsK0JBSkE7QUFLQTtBQUxBLE9BREEsRUFPQUUsV0FQQSxFQVFBQyxJQVJBLENBUUE7QUFDQTtBQUNBSztBQUNBLFNBRkEsTUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsT0FoQkEsV0FnQkE7QUFDQUo7QUFDQSxPQWxCQTtBQW1CQTtBQTVFQTtBQW5GQTs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FDdkdBOztJQUVxQk07Ozs7Ozs7Ozs7OztFQUE2QkQ7Ozs7Ozs7Ozs7Ozs7Ozs7QUNGbEQ7QUFDQTtBQUNBO0FBRUFFLCtDQUFBLENBQVFDLGdEQUFSO0FBQ0EsSUFBTUcsSUFBSSxHQUFHLElBQUlILGdEQUFKLENBQVk7QUFDckI5QixFQUFBQSxNQUFNLEVBQUUsSUFEYTtBQUVyQmtDLEVBQUFBLFFBQVEsRUFBRTtBQUNOLFVBQU07QUFDRiwrQkFBeUIsMENBRHZCO0FBRUYsdUJBQWdCLGVBRmQ7QUFHRiwwQkFBbUIsb0JBSGpCO0FBSUYsdUJBQWdCLHVCQUpkO0FBS0Ysc0JBQWdCLGVBTGQ7QUFNRixvQkFBYyxZQU5aO0FBT0Ysa0JBQVcsZUFQVDtBQVFGLGdCQUFTLFFBUlA7QUFTRix3QkFBaUIsZ0JBVGY7QUFVRixtQkFBWSxXQVZWO0FBV0YsaUJBQVc7QUFDUCxrQkFBVSxzRkFESDtBQUVQLDRCQUFvQjtBQUZiO0FBWFQsS0FEQTtBQWlCTixVQUFNO0FBQ0YsK0JBQXlCLDBDQUR2QjtBQUVGLHVCQUFnQixpQkFGZDtBQUdGLDBCQUFtQiwwQkFIakI7QUFJRix1QkFBZ0IseUJBSmQ7QUFLRixzQkFBZ0IsaUJBTGQ7QUFNRixvQkFBYyxpQkFOWjtBQU9GLGtCQUFXLFlBUFQ7QUFRRixnQkFBUyxVQVJQO0FBU0Ysd0JBQWlCLGNBVGY7QUFVRixtQkFBWSxZQVZWO0FBV0YsaUJBQVc7QUFDUCxrQkFBVSxvR0FESDtBQUVQLDRCQUFvQjtBQUZiO0FBWFQ7QUFqQkE7QUFGVyxDQUFaLENBQWI7QUFzQ0EsSUFBSUwsMkNBQUosQ0FBUTtBQUNKSSxFQUFBQSxJQUFJLEVBQUpBLElBREk7QUFFSkUsRUFBQUEsTUFBTSxFQUFFLGdCQUFBQyxDQUFDO0FBQUEsV0FBSUEsQ0FBQyxDQUFDTCw4REFBRCxDQUFMO0FBQUE7QUFGTCxDQUFSLEVBR0dNLE1BSEgsQ0FHVSxlQUhWOzs7Ozs7Ozs7OztBQzNDQTs7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQ0FxRjtBQUMzQjtBQUNMO0FBQ3JELENBQWtFOzs7QUFHbEU7QUFDbUc7QUFDbkcsZ0JBQWdCLHVHQUFVO0FBQzFCLEVBQUUsNEVBQU07QUFDUixFQUFFLDhFQUFNO0FBQ1IsRUFBRSx1RkFBZTtBQUNqQjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQSxJQUFJLEtBQVUsRUFBRSxZQWlCZjtBQUNEO0FBQ0EsaUVBQWU7Ozs7Ozs7Ozs7Ozs7OztBQ3ZDNk0sQ0FBQyxpRUFBZSwyTUFBRyxFQUFDOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FDQWhQO0FBQ0E7QUFDQTtBQUNBO0FBQ0EscUJBQXFCLCtCQUErQjtBQUNwRCxnQkFBZ0IsK0JBQStCO0FBQy9DO0FBQ0E7QUFDQSxpQkFBaUIsbUJBQW1CO0FBQ3BDLE9BQU87QUFDUDtBQUNBLGtCQUFrQix1QkFBdUI7QUFDekMsb0JBQW9CLHFCQUFxQjtBQUN6QztBQUNBO0FBQ0E7QUFDQTtBQUNBLHVCQUF1QixVQUFVO0FBQ2pDO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsaUJBQWlCO0FBQ2pCLGVBQWU7QUFDZixhQUFhO0FBQ2I7QUFDQSx3QkFBd0IscUNBQXFDO0FBQzdEO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsbUJBQW1CLGVBQWU7QUFDbEMsc0JBQXNCLHFDQUFxQztBQUMzRCxTQUFTO0FBQ1Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLGlCQUFpQixrREFBa0Q7QUFDbkUsT0FBTztBQUNQO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxxQkFBcUIsa0JBQWtCO0FBQ3ZDLFdBQVc7QUFDWDtBQUNBLHdCQUF3Qiw4QkFBOEI7QUFDdEQsMEJBQTBCLDZCQUE2QjtBQUN2RDtBQUNBO0FBQ0EsMkJBQTJCLDRCQUE0QjtBQUN2RDtBQUNBO0FBQ0E7QUFDQTtBQUNBLDBCQUEwQix3Q0FBd0M7QUFDbEUsNEJBQTRCLG9CQUFvQjtBQUNoRCw4QkFBOEIsc0NBQXNDO0FBQ3BFO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxpQ0FBaUMsbUJBQW1CO0FBQ3BELHVCQUF1QjtBQUN2QjtBQUNBO0FBQ0E7QUFDQSw4QkFBOEIsc0NBQXNDO0FBQ3BFO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxpQ0FBaUMsbUJBQW1CO0FBQ3BELHVCQUF1QjtBQUN2QjtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsMEJBQTBCLDZCQUE2QjtBQUN2RDtBQUNBO0FBQ0E7QUFDQTtBQUNBLDZCQUE2Qix5Q0FBeUM7QUFDdEUsbUJBQW1CO0FBQ25CO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsNkJBQTZCLGdCQUFnQjtBQUM3QztBQUNBO0FBQ0E7QUFDQTtBQUNBLHVCQUF1QjtBQUN2QixxQkFBcUI7QUFDckIsbUJBQW1CO0FBQ25CO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsNkJBQTZCLGdCQUFnQjtBQUM3QztBQUNBO0FBQ0E7QUFDQTtBQUNBLHVCQUF1QjtBQUN2QixxQkFBcUI7QUFDckIsbUJBQW1CO0FBQ25CO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsaUJBQWlCLG9EQUFvRDtBQUNyRSxPQUFPO0FBQ1A7QUFDQTtBQUNBO0FBQ0EsWUFBWSxzQ0FBc0Msb0JBQW9CO0FBQ3RFO0FBQ0Esd0JBQXdCLDhCQUE4QjtBQUN0RCwwQkFBMEIsNkJBQTZCO0FBQ3ZEO0FBQ0E7QUFDQSwyQkFBMkIsNEJBQTRCO0FBQ3ZEO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsMEJBQTBCLDJCQUEyQjtBQUNyRDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSwwQkFBMEIsNkJBQTZCO0FBQ3ZEO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsNkJBQTZCLGdCQUFnQjtBQUM3QztBQUNBO0FBQ0E7QUFDQTtBQUNBLHVCQUF1QjtBQUN2QixxQkFBcUI7QUFDckIsbUJBQW1CO0FBQ25CO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsNkJBQTZCLGdCQUFnQjtBQUM3QztBQUNBO0FBQ0E7QUFDQTtBQUNBLHVCQUF1QjtBQUN2QixxQkFBcUI7QUFDckIsbUJBQW1CO0FBQ25CO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1QsT0FBTztBQUNQLG9CQUFvQixTQUFTLHlCQUF5QjtBQUN0RDtBQUNBLEdBQUc7QUFDSDtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxTQUFTO0FBQ1QsT0FBTztBQUNQLG9CQUFvQixTQUFTLHlCQUF5QjtBQUN0RDtBQUNBLEdBQUc7QUFDSDtBQUNBOzs7Ozs7Ozs7Ozs7QUN0UEE7Ozs7OztVQ0FBO1VBQ0E7O1VBRUE7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7O1VBRUE7VUFDQTs7VUFFQTtVQUNBO1VBQ0E7O1VBRUE7VUFDQTs7Ozs7V0N6QkE7V0FDQTtXQUNBO1dBQ0E7V0FDQSwrQkFBK0Isd0NBQXdDO1dBQ3ZFO1dBQ0E7V0FDQTtXQUNBO1dBQ0EsaUJBQWlCLHFCQUFxQjtXQUN0QztXQUNBO1dBQ0Esa0JBQWtCLHFCQUFxQjtXQUN2QztXQUNBO1dBQ0EsS0FBSztXQUNMO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7V0FDQTs7Ozs7V0MzQkE7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBLGlDQUFpQyxXQUFXO1dBQzVDO1dBQ0E7Ozs7O1dDUEE7V0FDQTtXQUNBO1dBQ0E7V0FDQSx5Q0FBeUMsd0NBQXdDO1dBQ2pGO1dBQ0E7V0FDQTs7Ozs7V0NQQTtXQUNBO1dBQ0E7V0FDQTtXQUNBLEdBQUc7V0FDSDtXQUNBO1dBQ0EsQ0FBQzs7Ozs7V0NQRDs7Ozs7V0NBQTtXQUNBO1dBQ0E7V0FDQSx1REFBdUQsaUJBQWlCO1dBQ3hFO1dBQ0EsZ0RBQWdELGFBQWE7V0FDN0Q7Ozs7O1dDTkE7O1dBRUE7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBOztXQUVBOztXQUVBOztXQUVBOztXQUVBOztXQUVBOztXQUVBOztXQUVBO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBLE1BQU0scUJBQXFCO1dBQzNCO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBO1dBQ0E7O1dBRUE7V0FDQTtXQUNBOzs7OztVRWhEQTtVQUNBO1VBQ0E7VUFDQTtVQUNBIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlL3NyYy9SZXNvdXJjZXMvcHVibGljL2pzL2NvbXBvbmVudHMvRWRpdG9yLnZ1ZSIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvLi9zcmMvUmVzb3VyY2VzL3B1YmxpYy9qcy9jb21wb25lbnRzL2ltYWdlRWRpdG9yLmpzIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS8uL3NyYy9SZXNvdXJjZXMvcHVibGljL2pzL2ZpbGVib3QuanMiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vc3JjL1Jlc291cmNlcy9wdWJsaWMvanMvY29tcG9uZW50cy9FZGl0b3IudnVlPzQ1MTkiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vc3JjL1Jlc291cmNlcy9wdWJsaWMvanMvY29tcG9uZW50cy9FZGl0b3IudnVlP2Q3M2UiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vc3JjL1Jlc291cmNlcy9wdWJsaWMvanMvY29tcG9uZW50cy9FZGl0b3IudnVlPzljZTEiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlLy4vc3JjL1Jlc291cmNlcy9wdWJsaWMvanMvY29tcG9uZW50cy9FZGl0b3IudnVlP2UyM2YiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlL2V4dGVybmFsIHZhciBcIkZpbGVyb2JvdEltYWdlRWRpdG9yXCIiLCJ3ZWJwYWNrOi8vaW5pdC1jbXMtYnVuZGxlL3dlYnBhY2svYm9vdHN0cmFwIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS93ZWJwYWNrL3J1bnRpbWUvY2h1bmsgbG9hZGVkIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS93ZWJwYWNrL3J1bnRpbWUvY29tcGF0IGdldCBkZWZhdWx0IGV4cG9ydCIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvd2VicGFjay9ydW50aW1lL2RlZmluZSBwcm9wZXJ0eSBnZXR0ZXJzIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS93ZWJwYWNrL3J1bnRpbWUvZ2xvYmFsIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS93ZWJwYWNrL3J1bnRpbWUvaGFzT3duUHJvcGVydHkgc2hvcnRoYW5kIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS93ZWJwYWNrL3J1bnRpbWUvbWFrZSBuYW1lc3BhY2Ugb2JqZWN0Iiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS93ZWJwYWNrL3J1bnRpbWUvanNvbnAgY2h1bmsgbG9hZGluZyIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvd2VicGFjay9iZWZvcmUtc3RhcnR1cCIsIndlYnBhY2s6Ly9pbml0LWNtcy1idW5kbGUvd2VicGFjay9zdGFydHVwIiwid2VicGFjazovL2luaXQtY21zLWJ1bmRsZS93ZWJwYWNrL2FmdGVyLXN0YXJ0dXAiXSwic291cmNlc0NvbnRlbnQiOlsiPHN0eWxlPlxuLmltYWdlQ29udGFpbmVyIHtcbiAgICBwb3NpdGlvbjogcmVsYXRpdmU7XG59XG5cbi5pbWFnZSB7XG4gICAgb3BhY2l0eTogMTtcbiAgICBkaXNwbGF5OiBibG9jaztcbiAgICB0cmFuc2l0aW9uOiAuNXMgZWFzZTtcbiAgICBiYWNrZmFjZS12aXNpYmlsaXR5OiBoaWRkZW47XG59XG5cbi5taWRkbGUge1xuICAgIHRyYW5zaXRpb246IC41cyBlYXNlO1xuICAgIG9wYWNpdHk6IDA7XG4gICAgcG9zaXRpb246IGFic29sdXRlO1xuICAgIHRvcDogNTAlO1xuICAgIGxlZnQ6IDUwJTtcbiAgICB0cmFuc2Zvcm06IHRyYW5zbGF0ZSgtNTAlLCAtNTAlKTtcbiAgICAtbXMtdHJhbnNmb3JtOiB0cmFuc2xhdGUoLTUwJSwgLTUwJSk7XG4gICAgdGV4dC1hbGlnbjogY2VudGVyO1xufVxuXG4uaW1hZ2VDb250YWluZXI6aG92ZXIgLmltYWdlIHtcbiAgICBvcGFjaXR5OiAwLjM7XG59XG5cbi5pbWFnZUNvbnRhaW5lcjpob3ZlciAubWlkZGxlIHtcbiAgICBvcGFjaXR5OiAxO1xufVxuPC9zdHlsZT5cbjx0ZW1wbGF0ZT5cbiAgICA8ZGl2IGNsYXNzPVwiaW1hZ2VFZGl0b3JBcHBcIj5cbiAgICAgICAgPGRpdiBjbGFzcz1cImltYWdlQ29udGFpbmVyXCI+XG48IS0tICAgICAgICAgICAgPGltZyA6c3JjPVwiaW1hZ2VVUkxcIiBjbGFzcz1cImltZy1yZXNwb25zaXZlIGltYWdlXCIgQGNvbnRleHRtZW51LnByZXZlbnQ9XCIkcmVmcy5tZW51Lm9wZW5cIj4tLT5cbiAgICAgICAgICAgIDxpbWcgOnNyYz1cImltYWdlVVJMXCIgY2xhc3M9XCJpbWctcmVzcG9uc2l2ZSBjZW50ZXItYmxvY2sgaW1hZ2VcIj5cbiAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJtaWRkbGVcIj5cbiAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPVwidGV4dFwiPjxhIGhyZWY9XCJcIiBjbGFzcz1cImJ0biBidG4tZGVmYXVsdFwiIEBjbGljay5wcmV2ZW50PVwiZWRpdEltYWdlXCI+PGkgY2xhc3M9XCJmYSBmYS1tYWdpYyBmYS1zbWFsbFwiPjwvaT4ge3sgJHQoJ2VkaXRfaW1hZ2UnKX19PC9hPjwvZGl2PlxuICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgIDwvZGl2PlxuICAgICAgICA8YnI+XG4gICAgICAgIDxkaXYgY2xhc3M9XCJhbGVydCBcIiA6Y2xhc3M9XCJbYWxlcnRUeXBlID09PSAnZXJyb3InPydhbGVydC1kYW5nZXInOidhbGVydC1zdWNjZXNzJ11cIiB2LWlmPVwiYWxlcnRcIiByb2xlPVwiYWxlcnRcIiB2LWh0bWw9XCJhbGVydE1lc3NhZ2VcIj48L2Rpdj5cbiAgICAgICAgPGJyPlxuICAgICAgICA8ZGl2IGlkPVwiaW1hZ2VNb2RhbFwiIGNsYXNzPVwibW9kYWwgZmFkZVwiIHRhYmluZGV4PVwiLTFcIiByb2xlPVwiZGlhbG9nXCI+XG4gICAgICAgICAgICA8ZGl2IGNsYXNzPVwibW9kYWwtZGlhbG9nICBtb2RhbC1mdWxsXCIgcm9sZT1cImRvY3VtZW50XCI+XG4gICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cIm1vZGFsLWNvbnRlbnRcIj5cbiAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cIm1vZGFsLWhlYWRlclwiPlxuICAgICAgICAgICAgICAgICAgICAgICAgPGJ1dHRvbiB0eXBlPVwiYnV0dG9uXCIgY2xhc3M9XCJjbG9zZVwiIGRhdGEtZGlzbWlzcz1cIm1vZGFsXCIgYXJpYS1sYWJlbD1cIkNsb3NlXCI+PHNwYW4gYXJpYS1oaWRkZW49XCJ0cnVlXCI+JnRpbWVzOzwvc3Bhbj48L2J1dHRvbj5cbiAgICAgICAgICAgICAgICAgICAgICAgIDxoNCBjbGFzcz1cIm1vZGFsLXRpdGxlXCI+e3sgJHQoJ2NyZWF0ZWRfaW1hZ2UnKSB9fTwvaDQ+XG4gICAgICAgICAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPVwibW9kYWwtYm9keSBhbGlnbi1jZW50ZXJcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJyb3dcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPVwiY29sLW1kLTYgYWxpZ24tY2VudGVyXCI+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxoMz57eyAkdCgnb3JpZ2luYWxfaW1hZ2UnKSB9fTwvaDM+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxwPjxpbWcgOnNyYz1cImltYWdlVVJMXCIgY2xhc3M9XCJpbWctcmVzcG9uc2l2ZSBjZW50ZXItYmxvY2tcIi8+PC9wPjwvZGl2PlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJjb2wtbWQtNiBhbGlnbi1jZW50ZXJcIj5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGgzPnt7ICR0KCduZXdfaW1hZ2UnKSB9fTwvaDM+XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxwPjxpbWcgOnNyYz1cIm5ld0ltYWdlXCIgY2xhc3M9XCJpbWctcmVzcG9uc2l2ZSBjZW50ZXItYmxvY2tcIi8+PC9wPjwvZGl2PlxuICAgICAgICAgICAgICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPVwibW9kYWwtZm9vdGVyXCI+XG4gICAgICAgICAgICAgICAgICAgICAgICA8YnV0dG9uIHR5cGU9XCJidXR0b25cIiBjbGFzcz1cImJ0biBidG4tZGVmYXVsdFwiIGRhdGEtZGlzbWlzcz1cIm1vZGFsXCI+e3sgJHQoJ2NhbmNlbCcpIH19PC9idXR0b24+XG4gICAgICAgICAgICAgICAgICAgICAgICA8YnV0dG9uIHR5cGU9XCJidXR0b25cIiBjbGFzcz1cImJ0biBidG4td2FybmluZ1wiIEBjbGljay5wcmV2ZW50PVwidXBkYXRlSW1hZ2VcIj57eyAkdCgncmVwbGFjZV9pbWFnZScpIH19PC9idXR0b24+XG4gICAgICAgICAgICAgICAgICAgICAgICA8YnV0dG9uIHR5cGU9XCJidXR0b25cIiBjbGFzcz1cImJ0biBidG4tcHJpbWFyeVwiIEBjbGljay5wcmV2ZW50PVwiY2xvbmVJbWFnZVwiPnt7ICR0KCdjcmVhdGVfbmV3X2ltYWdlJykgfX08L2J1dHRvbj5cbiAgICAgICAgICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgICAgICAgPC9kaXY+PCEtLSAvLm1vZGFsLWNvbnRlbnQgLS0+XG4gICAgICAgICAgICA8L2Rpdj48IS0tIC8ubW9kYWwtZGlhbG9nIC0tPlxuICAgICAgICA8L2Rpdj48IS0tIC8ubW9kYWwgLS0+XG4gICAgICAgIDxkaXYgaWQ9XCJjb25maXJtTW9kYWxcIiBjbGFzcz1cIm1vZGFsIGZhZGVcIiB0YWJpbmRleD1cIi0xXCIgcm9sZT1cImRpYWxvZ1wiPlxuICAgICAgICA8ZGl2IGNsYXNzPVwibW9kYWwtZGlhbG9nXCIgcm9sZT1cImRvY3VtZW50XCI+XG4gICAgICAgICAgICA8ZGl2IGNsYXNzPVwibW9kYWwtY29udGVudFwiPlxuICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJtb2RhbC1oZWFkZXJcIj5cbiAgICAgICAgICAgICAgICAgICAgPGJ1dHRvbiB0eXBlPVwiYnV0dG9uXCIgY2xhc3M9XCJjbG9zZVwiIGRhdGEtZGlzbWlzcz1cIm1vZGFsXCIgYXJpYS1sYWJlbD1cIkNsb3NlXCI+PHNwYW4gYXJpYS1oaWRkZW49XCJ0cnVlXCI+JnRpbWVzOzwvc3Bhbj48L2J1dHRvbj5cbiAgICAgICAgICAgICAgICAgICAgPGg0IGNsYXNzPVwibW9kYWwtdGl0bGVcIj57eyAkdCgnYXJlX3lvdV9zdXJlJykgfX08L2g0PlxuICAgICAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJtb2RhbC1ib2R5XCI+XG4gICAgICAgICAgICAgICAgICAgIDxwPlxuICAgICAgICAgICAgICAgICAgICAgICAge3sgJHQoJ21lc3NhZ2UuY2Fubm90X2JlX3VuZG9uZScpIH19XG4gICAgICAgICAgICAgICAgICAgICAgICA8L3A+XG4gICAgICAgICAgICAgICAgPC9kaXY+XG4gICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cIm1vZGFsLWZvb3RlclwiPlxuICAgICAgICAgICAgICAgICAgICA8YnV0dG9uIHR5cGU9XCJidXR0b25cIiBjbGFzcz1cImJ0biBidG4td2FybmluZ1wiIEBjbGljay5wcmV2ZW50PVwiY29uZmlybVVwZGF0ZVwiPnt7ICR0KCdjb250aW51ZScpIH19PC9idXR0b24+XG4gICAgICAgICAgICAgICAgICAgIDxidXR0b24gdHlwZT1cImJ1dHRvblwiIGNsYXNzPVwiYnRuIGJ0bi1kZWZhdWx0XCIgQGNsaWNrLnByZXZlbnQ9XCJyZWplY3RVcGRhdGVcIj57eyAkdCgnY2FuY2VsJykgfX08L2J1dHRvbj5cbiAgICAgICAgICAgICAgICA8L2Rpdj5cbiAgICAgICAgICAgIDwvZGl2PjwhLS0gLy5tb2RhbC1jb250ZW50IC0tPlxuICAgICAgICA8L2Rpdj48IS0tIC8ubW9kYWwtZGlhbG9nIC0tPlxuICAgIDwvZGl2PjwhLS0gLy5tb2RhbCAtLT5cbiAgICA8L2Rpdj5cbjwvdGVtcGxhdGU+XG48c2NyaXB0PlxuICAgIGltcG9ydCBWdWUgZnJvbSAnLi4vLi4vLi4vLi4vLi4vbm9kZV9tb2R1bGVzL3Z1ZSc7XG4gICAgaW1wb3J0IGF4aW9zIGZyb20gJy4uLy4uLy4uLy4uLy4uL25vZGVfbW9kdWxlcy9heGlvcyc7XG4gICAgaW1wb3J0IEZpbGVyb2JvdEltYWdlRWRpdG9yIGZyb20gJy4vaW1hZ2VFZGl0b3InO1xuXG4gICAgY29uc3QgQUxMT1dFRF9GSUxFX0VYVEVOVElPTlMgPSBbJ2dpZicsICdqcGcnLCAnanBlZycsICdwbmcnXTtcbiAgICBjb25zdCBMQU5HVUFHRVMgPSBbJ2RlJywgJ2VuJ107XG4gICAgbGV0IGltYWdlQ29udGFpbmVyID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2ltYWdlLWNvbnRhaW5lcicpO1xuICAgIGxldCBheGlvc0NvbmZpZyA9IHtoZWFkZXJzOiB7J1gtUmVxdWVzdGVkLVdpdGgnOiAnWE1MSHR0cFJlcXVlc3QnfX07XG4gICAgbGV0IGxhbmdhdWdlID0gaW1hZ2VDb250YWluZXIuZ2V0QXR0cmlidXRlKCdkYXRhLWxhbmcnKTtcbiAgICBpZighTEFOR1VBR0VTLmluY2x1ZGVzKGxhbmdhdWdlKSl7XG4gICAgICAgIGxhbmdhdWdlID0gJ2VuJztcbiAgICB9XG5cbiAgICBleHBvcnQgZGVmYXVsdCB7XG4gICAgICAgIG5hbWU6ICdFZGl0b3InLFxuICAgICAgICAvLyBjb21wb25lbnRzOiB7XG4gICAgICAgIC8vICAgICBWdWVDb250ZXh0XG4gICAgICAgIC8vIH0sXG4gICAgICAgIG1vdW50ZWQoKSB7XG4gICAgICAgICAgICBsZXQgY29uZmlnID0ge1xuICAgICAgICAgICAgICAgIGNvbG9yU2NoZW1lOiAnbGlnaHQnLFxuICAgICAgICAgICAgICAgIHRvb2xzOiBbJ2FkanVzdCcsICdlZmZlY3RzJywgJ2ZpbHRlcnMnLCAncm90YXRlJywnY3JvcCcsJ3Jlc2l6ZSddLFxuICAgICAgICAgICAgICAgIGxhbmd1YWdlOiBsYW5nYXVnZSxcbiAgICAgICAgICAgICAgICB0cmFuc2xhdGlvbnM6IHtcbiAgICAgICAgICAgICAgICAgICAgZW46IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICd0b29sYmFyLmRvd25sb2FkJzogJ1NhdmUnLFxuICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgICBkZToge1xuICAgICAgICAgICAgICAgICAgICAgICAgXCJoZWFkZXIuaW1hZ2VfZWRpdG9yX3RpdGxlXCI6IFwiQmlsZCBiZWFyYml0ZW5cIixcbiAgICAgICAgICAgICAgICAgICAgICAgIFwidG9vbGJhci5kb3dubG9hZFwiOiBcIlNwZWljaGVyblwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgXCJ0b29sYmFyLnNhdmVcIjogXCJTcGVpY2hlcm5cIixcbiAgICAgICAgICAgICAgICAgICAgICAgIFwidG9vbGJhci5hcHBseVwiOiBcIkFud2VuZGVuXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICBcInRvb2xiYXIuY2FuY2VsXCI6IFwiQWJicmVjaGVuXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICBcInRvb2xiYXIuZ29fYmFja1wiOiBcIlp1csO8Y2tcIixcbiAgICAgICAgICAgICAgICAgICAgICAgIFwidG9vbGJhci5hZGp1c3RcIjogXCJBbnBhc3NlblwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgXCJ0b29sYmFyLmVmZmVjdHNcIjogXCJFZmZla3RlXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICBcInRvb2xiYXIuZmlsdGVyc1wiOiBcIkZpbHRlclwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgXCJ0b29sYmFyLm9yaWVudGF0aW9uXCI6IFwiT3JpZW50aWVydW5nXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICBcInRvb2xiYXIuY3JvcFwiOiBcIlp1c2NobmVpZGVuXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICBcInRvb2xiYXIucmVzaXplXCI6IFwiR3LDtsOfZSDDpG5kZXJuXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICBcInRvb2xiYXIud2F0ZXJtYXJrXCI6IFwiV2Fzc2VyemVpY2hlblwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgXCJhZGp1c3QuYnJpZ2h0bmVzc1wiOiBcIkhlbGxpZ2tlaXRcIixcbiAgICAgICAgICAgICAgICAgICAgICAgIFwiYWRqdXN0LmNvbnRyYXN0XCI6IFwiS29udHJhc3RcIixcbiAgICAgICAgICAgICAgICAgICAgICAgIFwiYWRqdXN0LmV4cG9zdXJlXCI6IFwiQmVsaWNodHVuZ1wiLFxuICAgICAgICAgICAgICAgICAgICAgICAgXCJhZGp1c3Quc2F0dXJhdGlvblwiOiBcIkZhcmJzw6R0dGlndW5nXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICBcIm9yaWVudGF0aW9uLnJvdGF0ZV9sXCI6IFwiTmFjaCBsaW5rcyBkcmVoZW5cIixcbiAgICAgICAgICAgICAgICAgICAgICAgIFwib3JpZW50YXRpb24ucm90YXRlX3JcIjogXCJOYWNoIHJlY2h0cyBkcmVoZW5cIixcbiAgICAgICAgICAgICAgICAgICAgICAgIFwib3JpZW50YXRpb24uZmxpcF9oXCI6IFwiSG9yaXpvbnRhbCBzcGllZ2VsblwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgXCJvcmllbnRhdGlvbi5mbGlwX3ZcIjogXCJWZXJ0aWthbCBzcGllZ2VsblwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgXCJwcmVfcmVzaXplLnRpdGxlXCI6IFwiTcO2Y2h0ZW4gU2llIGRpZSBBdWZsw7ZzdW5nIHJlZHV6aWVyZW4sIGJldm9yIFNpZSBkYXMgQmlsZCBiZWFyYmVpdGVuP1wiLFxuICAgICAgICAgICAgICAgICAgICAgICAgXCJwcmVfcmVzaXplLmtlZXBfb3JpZ2luYWxfcmVzb2x1dGlvblwiOiBcIk9yaWdpbmFsYXVmbMO2c3VuZyBiZWliZWhhbHRlblwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgXCJwcmVfcmVzaXplLnJlc2l6ZV9uX2NvbnRpbnVlXCI6IFwiR3LDtsOfZSDDpG5kZXJuICYgZm9ydHNldHplblwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgXCJmb290ZXIucmVzZXRcIjogXCJadXLDvGNrc2V0emVuXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICBcImZvb3Rlci51bmRvXCI6IFwiUsO8Y2tnw6RuZ2lnIG1hY2hlblwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgXCJmb290ZXIucmVkb1wiOiBcIldpZWRlcmhvbGVuXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICBcInNwaW5uZXIubGFiZWxcIjogXCJWZXJhcmJlaXR1bmcuLi5cIixcbiAgICAgICAgICAgICAgICAgICAgICAgIFwid2FybmluZy50b29fYmlnX3Jlc29sdXRpb25cIjogXCJEaWUgQXVmbMO2c3VuZyBkZXMgQmlsZGVzIGlzdCB6dSBncm/DnyBmw7xyIGRhcyBXZWIuIEVzIGthbm4genUgUHJvYmxlbWVuIG1pdCBkZXIgTGVpc3R1bmcgZGVzIEJpbGRiZWFyYmVpdHVuZ3Nwcm9ncmFtbXMgZsO8aHJlbi5cIixcbiAgICAgICAgICAgICAgICAgICAgICAgIFwiY29tbW9uLndpZHRoXCI6IFwiYnJlaXRlXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICBcImNvbW1vbi5oZWlnaHRcIjogXCJow7ZoZVwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgXCJjb21tb24uY3VzdG9tXCI6IFwiYmVudXR6ZXJkZWZpbmllcnRcIixcbiAgICAgICAgICAgICAgICAgICAgICAgIFwiY29tbW9uLm9yaWdpbmFsXCI6IFwiT3JpZ2luYWxcIixcbiAgICAgICAgICAgICAgICAgICAgICAgIFwiY29tbW9uLnNxdWFyZVwiOiBcInF1YWRyYXRpc2NoXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICBcImNvbW1vbi5vcGFjaXR5XCI6IFwiT3Bheml0w6R0XCIsXG4gICAgICAgICAgICAgICAgICAgICAgICBcImNvbW1vbi5hcHBseV93YXRlcm1hcmtcIjogXCJXYXNzZXJ6ZWljaGVuIGFud2VuZGVuXCJcblxuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICAgICAgdGhpcy5pbWFnZUVkaXRvciA9IG5ldyBGaWxlcm9ib3RJbWFnZUVkaXRvcihjb25maWcsIHRoaXMuZG93bmxvYWQpO1xuICAgICAgICAgICAgdGhpcy5pbWFnZVVSTCA9IGltYWdlQ29udGFpbmVyLmdldEF0dHJpYnV0ZSgnZGF0YS1pbWFnZS1zcmMnKTtcbiAgICAgICAgICAgIHRoaXMuaWQgPSBpbWFnZUNvbnRhaW5lci5nZXRBdHRyaWJ1dGUoJ2RhdGEtaW1hZ2UtaWQnKTtcbiAgICAgICAgICAgIHRoaXMuY29udGV4dCA9IGltYWdlQ29udGFpbmVyLmdldEF0dHJpYnV0ZSgnZGF0YS1pbWFnZS1jb250ZXh0Jyk7XG4gICAgICAgICAgICB0aGlzLnByb3ZpZGVyID0gaW1hZ2VDb250YWluZXIuZ2V0QXR0cmlidXRlKCdkYXRhLWltYWdlLXByb3ZpZGVyJyk7XG4gICAgICAgICAgICB0aGlzLmZpbGVFeHRlbnNpb24gPSB0aGlzLmltYWdlVVJMLnNsaWNlKCh0aGlzLmltYWdlVVJMLmxhc3RJbmRleE9mKFwiLlwiKSAtIDEgPj4+IDApICsgMik7XG4gICAgICAgIH0sXG4gICAgICAgIGRhdGEoKSB7XG4gICAgICAgICAgICB0aGlzLiRpMThuLmxvY2FsZSA9IGxhbmdhdWdlO1xuICAgICAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgICAgICBsb2NhbGU6ICdlbicsXG4gICAgICAgICAgICAgICAgaW1hZ2VVUkw6ICcnLFxuICAgICAgICAgICAgICAgIGlkOiAnJyxcbiAgICAgICAgICAgICAgICBjb250ZXh0OiAnJyxcbiAgICAgICAgICAgICAgICBwcm92aWRlcjogJycsXG4gICAgICAgICAgICAgICAgaW1hZ2VFZGl0b3I6ICcnLFxuICAgICAgICAgICAgICAgIG5ld0ltYWdlOiAnJyxcbiAgICAgICAgICAgICAgICBhbGVydDogZmFsc2UsXG4gICAgICAgICAgICAgICAgYWxlcnRNZXNzYWdlOiBmYWxzZSxcbiAgICAgICAgICAgICAgICBhbGVydFR5cGU6IGZhbHNlLFxuICAgICAgICAgICAgICAgIGZpbGVFeHRlbnNpb246ICcnLFxuICAgICAgICAgICAgfVxuICAgICAgICB9LFxuICAgICAgICB3YXRjaDoge1xuICAgICAgICAgICAgbG9jYWxlICh2YWwpIHtcbiAgICAgICAgICAgICAgICB0aGlzLiRpMThuLmxvY2FsZSA9IHZhbFxuICAgICAgICAgICAgfVxuICAgICAgICB9LFxuICAgICAgICBtZXRob2RzOiB7XG4gICAgICAgICAgICBlZGl0SW1hZ2UoKXtcbiAgICAgICAgICAgICAgICB0aGlzLmFsZXJ0TWVzc2FnZSA9IGZhbHNlO1xuICAgICAgICAgICAgICAgIGlmKCFBTExPV0VEX0ZJTEVfRVhURU5USU9OUy5pbmNsdWRlcyh0aGlzLmZpbGVFeHRlbnNpb24pKXtcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5hbGVydCA9IHRydWU7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuYWxlcnRUeXBlID0gJ2Vycm9yJztcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5hbGVydE1lc3NhZ2UgPSB0aGlzLiRpMThuLnQoJ25vdF9hbGxvd2VkX2V4dGVuc2lvbicpO1xuICAgICAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgdGhpcy5hbGVydCA9IGZhbHNlO1xuICAgICAgICAgICAgICAgIHRoaXMuYWxlcnRUeXBlID0gZmFsc2U7XG4gICAgICAgICAgICAgICAgdGhpcy5hbGVydE1lc3NhZ2UgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICB0aGlzLmltYWdlRWRpdG9yLm9wZW4odGhpcy5pbWFnZVVSTCk7XG4gICAgICAgICAgICB9LFxuICAgICAgICAgICAgZG93bmxvYWQoeyBzdGF0dXMsICBpbWFnZU5hbWUsIGltYWdlTWltZSwgY2FudmFzIH0pe1xuICAgICAgICAgICAgICAgIHRoaXMubmV3SW1hZ2UgPSBjYW52YXMudG9EYXRhVVJMKCk7XG4gICAgICAgICAgICAgICAgdGhpcy5hbGVydCA9IGZhbHNlO1xuICAgICAgICAgICAgICAgIHRoaXMuYWxlcnRUeXBlID0gZmFsc2U7XG4gICAgICAgICAgICAgICAgdGhpcy5hbGVydE1lc3NhZ2UgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICAkKCcjaW1hZ2VNb2RhbCcpLm1vZGFsKCdzaG93Jyk7XG4gICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIGNsb25lSW1hZ2UoKXtcbiAgICAgICAgICAgICAgICAkKCcjaW1hZ2VNb2RhbCcpLm1vZGFsKCdoaWRlJyk7XG4gICAgICAgICAgICAgICAgYXhpb3NcbiAgICAgICAgICAgICAgICAgICAgLnBvc3QoJy9hZG1pbi9jbXMvbWVkaWEvY2xvbmUnLCB7XG4gICAgICAgICAgICAgICAgICAgICAgICAnaWQnOiB0aGlzLmlkLFxuICAgICAgICAgICAgICAgICAgICAgICAgJ2Nsb25lJzogdHJ1ZSxcbiAgICAgICAgICAgICAgICAgICAgICAgICdwcm92aWRlcic6IHRoaXMucHJvdmlkZXIsXG4gICAgICAgICAgICAgICAgICAgICAgICAnY29udGV4dCc6IHRoaXMuY29udGV4dCxcbiAgICAgICAgICAgICAgICAgICAgICAgICdmaWxlJzogdGhpcy5uZXdJbWFnZVxuICAgICAgICAgICAgICAgICAgICB9LCBheGlvc0NvbmZpZykuXG4gICAgICAgICAgICAgICAgdGhlbihyZXNwb25zZSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuYWxlcnQgPSB0cnVlO1xuXG4gICAgICAgICAgICAgICAgICAgIGlmKHJlc3BvbnNlLmRhdGEuc3VjY2Vzcyl7XG4gICAgICAgICAgICAgICAgICAgICAgICB0aGlzLmFsZXJ0TWVzc2FnZSA9IHRoaXMuJHQoJ21lc3NhZ2UuY2xvbmVkJywgeyd1cmwnOiByZXNwb25zZS5kYXRhLnVybH0pO1xuICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy5hbGVydFR5cGUgPSAnc3VjY2Vzcyc7XG4gICAgICAgICAgICAgICAgICAgIH1lbHNle1xuICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy5hbGVydE1lc3NhZ2UgPSByZXNwb25zZS5kYXRhLmVycm9yO1xuICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy5hbGVydFR5cGUgPSAnZXJyb3InO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfSkuY2F0Y2goZnVuY3Rpb24gKGVycm9yKSB7XG4gICAgICAgICAgICAgICAgICAgIGNvbnNvbGUubG9nKGVycm9yKTtcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIH0sXG4gICAgICAgICAgICB1cGRhdGVJbWFnZSgpe1xuICAgICAgICAgICAgICAgICQoJyNpbWFnZU1vZGFsJykubW9kYWwoJ2hpZGUnKTtcbiAgICAgICAgICAgICAgICAkKCcjY29uZmlybU1vZGFsJykubW9kYWwoJ3Nob3cnKTtcbiAgICAgICAgICAgIH0sXG4gICAgICAgICAgICByZWplY3RVcGRhdGUoKXtcbiAgICAgICAgICAgICAgICAkKCcjaW1hZ2VNb2RhbCcpLm1vZGFsKCdzaG93Jyk7XG4gICAgICAgICAgICAgICAgJCgnI2NvbmZpcm1Nb2RhbCcpLm1vZGFsKCdoaWRlJyk7XG4gICAgICAgICAgICB9LFxuICAgICAgICAgICAgY29uZmlybVVwZGF0ZSgpe1xuICAgICAgICAgICAgICAgICQoJyNjb25maXJtTW9kYWwnKS5tb2RhbCgnaGlkZScpO1xuICAgICAgICAgICAgICAgIGF4aW9zXG4gICAgICAgICAgICAgICAgICAgIC5wb3N0KCcvYWRtaW4vY21zL21lZGlhL2Nsb25lJywge1xuICAgICAgICAgICAgICAgICAgICAgICAgJ2lkJzogdGhpcy5pZCxcbiAgICAgICAgICAgICAgICAgICAgICAgICdjbG9uZSc6IGZhbHNlLFxuICAgICAgICAgICAgICAgICAgICAgICAgJ3Byb3ZpZGVyJzogdGhpcy5wcm92aWRlcixcbiAgICAgICAgICAgICAgICAgICAgICAgICdjb250ZXh0JzogdGhpcy5jb250ZXh0LFxuICAgICAgICAgICAgICAgICAgICAgICAgJ2ZpbGUnOiB0aGlzLm5ld0ltYWdlXG4gICAgICAgICAgICAgICAgICAgIH0sIGF4aW9zQ29uZmlnKS5cbiAgICAgICAgICAgICAgICB0aGVuKHJlc3BvbnNlID0+IHtcbiAgICAgICAgICAgICAgICAgICAgaWYocmVzcG9uc2UuZGF0YS5zdWNjZXNzKXtcbiAgICAgICAgICAgICAgICAgICAgICAgIHdpbmRvdy5sb2NhdGlvbi5ocmVmID0gcmVzcG9uc2UuZGF0YS51cmw7XG4gICAgICAgICAgICAgICAgICAgIH1lbHNle1xuICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy5hbGVydCA9IHRydWU7XG4gICAgICAgICAgICAgICAgICAgICAgICB0aGlzLmFsZXJ0TWVzc2FnZSA9IHJlc3BvbnNlLmRhdGEuZXJyb3I7XG4gICAgICAgICAgICAgICAgICAgICAgICB0aGlzLmFsZXJ0VHlwZSA9ICdlcnJvcic7XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9KS5jYXRjaChmdW5jdGlvbiAoZXJyb3IpIHtcbiAgICAgICAgICAgICAgICAgICAgY29uc29sZS5sb2coZXJyb3IpO1xuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfTtcbjwvc2NyaXB0PlxuIiwiaW1wb3J0IEZpbGVyb2JvdEltYWdlRWRpdG9yQmFzZSBmcm9tICdmaWxlcm9ib3RJbWFnZUVkaXRvcic7XG5cbmV4cG9ydCBkZWZhdWx0IGNsYXNzIEZpbGVyb2JvdEltYWdlRWRpdG9yIGV4dGVuZHMgRmlsZXJvYm90SW1hZ2VFZGl0b3JCYXNlIHt9IiwiaW1wb3J0IFZ1ZSBmcm9tICd2dWUnO1xuaW1wb3J0IFZ1ZUkxOG4gZnJvbSAndnVlLWkxOG4nXG5pbXBvcnQgRWRpdG9yIGZyb20gJy4vY29tcG9uZW50cy9FZGl0b3IudnVlJ1xuXG5WdWUudXNlKFZ1ZUkxOG4pXG5jb25zdCBpMThuID0gbmV3IFZ1ZUkxOG4oe1xuICAgIGxvY2FsZTogJ2VuJyxcbiAgICBtZXNzYWdlczoge1xuICAgICAgICBcImVuXCI6IHtcbiAgICAgICAgICAgIFwibm90X2FsbG93ZWRfZXh0ZW5zaW9uXCI6IFwiVW5mb3J0dW5hdGVseSB0aGUgaW1hZ2UgY2Fubm90IGJlIGVkaXRlZFwiLFxuICAgICAgICAgICAgXCJjcmVhdGVkX2ltYWdlXCI6XCJDcmVhdGVkIEltYWdlXCIsXG4gICAgICAgICAgICBcImNyZWF0ZV9uZXdfaW1hZ2VcIjpcIkNyZWF0ZSBhIG5ldyBpbWFnZVwiLFxuICAgICAgICAgICAgXCJyZXBsYWNlX2ltYWdlXCI6XCJSZXBsYWNlIGN1cnJlbnQgaW1hZ2VcIixcbiAgICAgICAgICAgIFwiYXJlX3lvdV9zdXJlXCI6IFwiQXJlIFlvdSBTdXJlP1wiLFxuICAgICAgICAgICAgXCJlZGl0X2ltYWdlXCI6IFwiRWRpdCBpbWFnZVwiLFxuICAgICAgICAgICAgXCJjb250aW51ZVwiOlwiWWVzLCBjb250aW51ZVwiLFxuICAgICAgICAgICAgXCJjYW5jZWxcIjpcIkNhbmNlbFwiLFxuICAgICAgICAgICAgXCJvcmlnaW5hbF9pbWFnZVwiOlwiT3JpZ2luYWwgSW1hZ2VcIixcbiAgICAgICAgICAgIFwibmV3X2ltYWdlXCI6XCJOZXcgSW1hZ2VcIixcbiAgICAgICAgICAgIFwibWVzc2FnZVwiOiB7XG4gICAgICAgICAgICAgICAgXCJjbG9uZWRcIjogXCJJbWFnZSBoYXMgYmVlbiBjcmVhdGVkLCBmb2xsb3cgdGhpcyA8YSBocmVmPVxcXCJ7dXJsfVxcXCI+bGluayB0byB2aWV3IHRoZSBuZXcgaW1hZ2U8L2E+XCIsXG4gICAgICAgICAgICAgICAgXCJjYW5ub3RfYmVfdW5kb25lXCI6IFwiVGhpcyBhY3Rpb24gd2lsbCByZXBsYWNlIHRoZSBjdXJyZW50IGltYWdlLCB5b3UgY2FuIG5vdCB1bmRvIHRoaXMgYWN0aW9uXCJcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSxcbiAgICAgICAgXCJkZVwiOiB7XG4gICAgICAgICAgICBcIm5vdF9hbGxvd2VkX2V4dGVuc2lvblwiOiBcIkJpbGQga2FubiBsZWlkZXIgbmljaHQgYmVhcmJlaXRldCB3ZXJkZW5cIixcbiAgICAgICAgICAgIFwiY3JlYXRlZF9pbWFnZVwiOlwiRXJzdGVsbHRlcyBCaWxkXCIsXG4gICAgICAgICAgICBcImNyZWF0ZV9uZXdfaW1hZ2VcIjpcIkVpbiBuZXVlcyBCaWxkIGVyc3RlbGxlblwiLFxuICAgICAgICAgICAgXCJyZXBsYWNlX2ltYWdlXCI6XCJBa3R1ZWxsZXMgQmlsZCBlcnNldHplblwiLFxuICAgICAgICAgICAgXCJhcmVfeW91X3N1cmVcIjogXCJTaW5kIFNpZSBTaWNoZXJcIixcbiAgICAgICAgICAgIFwiZWRpdF9pbWFnZVwiOiBcIkJpbGQgYmVhcmJlaXRlblwiLFxuICAgICAgICAgICAgXCJjb250aW51ZVwiOlwiSmEsIHdlaXRlclwiLFxuICAgICAgICAgICAgXCJjYW5jZWxcIjpcIkFicmVjaGVuXCIsXG4gICAgICAgICAgICBcIm9yaWdpbmFsX2ltYWdlXCI6XCJPcmlnaW5hbGJpbGRcIixcbiAgICAgICAgICAgIFwibmV3X2ltYWdlXCI6XCJOZXVlcyBCaWxkXCIsXG4gICAgICAgICAgICBcIm1lc3NhZ2VcIjoge1xuICAgICAgICAgICAgICAgIFwiY2xvbmVkXCI6IFwiRGFzIEJpbGQgd3VyZGUgZXJzdGVsbHQsIGZvbGdlbiBTaWUgZGllc2VtIDxhIGhyZWY9XFxcInt1cmx9XFxcIj5MaW5rLCB1bSBkYXMgbmV1ZSBCaWxkIGFuenVzZWhlbjwvYT4uXCIsXG4gICAgICAgICAgICAgICAgXCJjYW5ub3RfYmVfdW5kb25lXCI6IFwiRGllc2UgQWt0aW9uIGVyc2V0enQgZGFzIGFrdHVlbGxlIEJpbGQsIFNpZSBrw7ZubmVuIGRpZXNlIEFrdGlvbiBuaWNodCByw7xja2fDpG5naWcgbWFjaGVuLlwiXG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICB9XG59KVxuXG5uZXcgVnVlKHtcbiAgICBpMThuLFxuICAgIHJlbmRlcjogaCA9PiBoKEVkaXRvciksXG59KS4kbW91bnQoJyNpbWFnZS1lZGl0b3InKTtcbiIsIi8vIGV4dHJhY3RlZCBieSBtaW5pLWNzcy1leHRyYWN0LXBsdWdpblxuZXhwb3J0IHt9OyIsImltcG9ydCB7IHJlbmRlciwgc3RhdGljUmVuZGVyRm5zIH0gZnJvbSBcIi4vRWRpdG9yLnZ1ZT92dWUmdHlwZT10ZW1wbGF0ZSZpZD03MmNlNzQzMyZcIlxuaW1wb3J0IHNjcmlwdCBmcm9tIFwiLi9FZGl0b3IudnVlP3Z1ZSZ0eXBlPXNjcmlwdCZsYW5nPWpzJlwiXG5leHBvcnQgKiBmcm9tIFwiLi9FZGl0b3IudnVlP3Z1ZSZ0eXBlPXNjcmlwdCZsYW5nPWpzJlwiXG5pbXBvcnQgc3R5bGUwIGZyb20gXCIuL0VkaXRvci52dWU/dnVlJnR5cGU9c3R5bGUmaW5kZXg9MCZsYW5nPWNzcyZcIlxuXG5cbi8qIG5vcm1hbGl6ZSBjb21wb25lbnQgKi9cbmltcG9ydCBub3JtYWxpemVyIGZyb20gXCIhLi4vLi4vLi4vLi4vLi4vbm9kZV9tb2R1bGVzL3Z1ZS1sb2FkZXIvbGliL3J1bnRpbWUvY29tcG9uZW50Tm9ybWFsaXplci5qc1wiXG52YXIgY29tcG9uZW50ID0gbm9ybWFsaXplcihcbiAgc2NyaXB0LFxuICByZW5kZXIsXG4gIHN0YXRpY1JlbmRlckZucyxcbiAgZmFsc2UsXG4gIG51bGwsXG4gIG51bGwsXG4gIG51bGxcbiAgXG4pXG5cbi8qIGhvdCByZWxvYWQgKi9cbmlmIChtb2R1bGUuaG90KSB7XG4gIHZhciBhcGkgPSByZXF1aXJlKFwiL1VzZXJzL3lvcmtpZWNoYWR3aWNrL3NpdGVzL2NhcmluZ2NvbW11bml0aWVzL3ZlbmRvci9uZXR3b3JraW5nL2luaXQtY21zLWJ1bmRsZS9ub2RlX21vZHVsZXMvdnVlLWhvdC1yZWxvYWQtYXBpL2Rpc3QvaW5kZXguanNcIilcbiAgYXBpLmluc3RhbGwocmVxdWlyZSgndnVlJykpXG4gIGlmIChhcGkuY29tcGF0aWJsZSkge1xuICAgIG1vZHVsZS5ob3QuYWNjZXB0KClcbiAgICBpZiAoIWFwaS5pc1JlY29yZGVkKCc3MmNlNzQzMycpKSB7XG4gICAgICBhcGkuY3JlYXRlUmVjb3JkKCc3MmNlNzQzMycsIGNvbXBvbmVudC5vcHRpb25zKVxuICAgIH0gZWxzZSB7XG4gICAgICBhcGkucmVsb2FkKCc3MmNlNzQzMycsIGNvbXBvbmVudC5vcHRpb25zKVxuICAgIH1cbiAgICBtb2R1bGUuaG90LmFjY2VwdChcIi4vRWRpdG9yLnZ1ZT92dWUmdHlwZT10ZW1wbGF0ZSZpZD03MmNlNzQzMyZcIiwgZnVuY3Rpb24gKCkge1xuICAgICAgYXBpLnJlcmVuZGVyKCc3MmNlNzQzMycsIHtcbiAgICAgICAgcmVuZGVyOiByZW5kZXIsXG4gICAgICAgIHN0YXRpY1JlbmRlckZuczogc3RhdGljUmVuZGVyRm5zXG4gICAgICB9KVxuICAgIH0pXG4gIH1cbn1cbmNvbXBvbmVudC5vcHRpb25zLl9fZmlsZSA9IFwic3JjL1Jlc291cmNlcy9wdWJsaWMvanMvY29tcG9uZW50cy9FZGl0b3IudnVlXCJcbmV4cG9ydCBkZWZhdWx0IGNvbXBvbmVudC5leHBvcnRzIiwiaW1wb3J0IG1vZCBmcm9tIFwiLSEuLi8uLi8uLi8uLi8uLi9ub2RlX21vZHVsZXMvYmFiZWwtbG9hZGVyL2xpYi9pbmRleC5qcz8/Y2xvbmVkUnVsZVNldC0xWzBdLnJ1bGVzWzBdLnVzZVswXSEuLi8uLi8uLi8uLi8uLi9ub2RlX21vZHVsZXMvdnVlLWxvYWRlci9saWIvaW5kZXguanM/P3Z1ZS1sb2FkZXItb3B0aW9ucyEuL0VkaXRvci52dWU/dnVlJnR5cGU9c2NyaXB0Jmxhbmc9anMmXCI7IGV4cG9ydCBkZWZhdWx0IG1vZDsgZXhwb3J0ICogZnJvbSBcIi0hLi4vLi4vLi4vLi4vLi4vbm9kZV9tb2R1bGVzL2JhYmVsLWxvYWRlci9saWIvaW5kZXguanM/P2Nsb25lZFJ1bGVTZXQtMVswXS5ydWxlc1swXS51c2VbMF0hLi4vLi4vLi4vLi4vLi4vbm9kZV9tb2R1bGVzL3Z1ZS1sb2FkZXIvbGliL2luZGV4LmpzPz92dWUtbG9hZGVyLW9wdGlvbnMhLi9FZGl0b3IudnVlP3Z1ZSZ0eXBlPXNjcmlwdCZsYW5nPWpzJlwiIiwidmFyIHJlbmRlciA9IGZ1bmN0aW9uICgpIHtcbiAgdmFyIF92bSA9IHRoaXNcbiAgdmFyIF9oID0gX3ZtLiRjcmVhdGVFbGVtZW50XG4gIHZhciBfYyA9IF92bS5fc2VsZi5fYyB8fCBfaFxuICByZXR1cm4gX2MoXCJkaXZcIiwgeyBzdGF0aWNDbGFzczogXCJpbWFnZUVkaXRvckFwcFwiIH0sIFtcbiAgICBfYyhcImRpdlwiLCB7IHN0YXRpY0NsYXNzOiBcImltYWdlQ29udGFpbmVyXCIgfSwgW1xuICAgICAgX2MoXCJpbWdcIiwge1xuICAgICAgICBzdGF0aWNDbGFzczogXCJpbWctcmVzcG9uc2l2ZSBjZW50ZXItYmxvY2sgaW1hZ2VcIixcbiAgICAgICAgYXR0cnM6IHsgc3JjOiBfdm0uaW1hZ2VVUkwgfSxcbiAgICAgIH0pLFxuICAgICAgX3ZtLl92KFwiIFwiKSxcbiAgICAgIF9jKFwiZGl2XCIsIHsgc3RhdGljQ2xhc3M6IFwibWlkZGxlXCIgfSwgW1xuICAgICAgICBfYyhcImRpdlwiLCB7IHN0YXRpY0NsYXNzOiBcInRleHRcIiB9LCBbXG4gICAgICAgICAgX2MoXG4gICAgICAgICAgICBcImFcIixcbiAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgc3RhdGljQ2xhc3M6IFwiYnRuIGJ0bi1kZWZhdWx0XCIsXG4gICAgICAgICAgICAgIGF0dHJzOiB7IGhyZWY6IFwiXCIgfSxcbiAgICAgICAgICAgICAgb246IHtcbiAgICAgICAgICAgICAgICBjbGljazogZnVuY3Rpb24gKCRldmVudCkge1xuICAgICAgICAgICAgICAgICAgJGV2ZW50LnByZXZlbnREZWZhdWx0KClcbiAgICAgICAgICAgICAgICAgIHJldHVybiBfdm0uZWRpdEltYWdlLmFwcGx5KG51bGwsIGFyZ3VtZW50cylcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIFtcbiAgICAgICAgICAgICAgX2MoXCJpXCIsIHsgc3RhdGljQ2xhc3M6IFwiZmEgZmEtbWFnaWMgZmEtc21hbGxcIiB9KSxcbiAgICAgICAgICAgICAgX3ZtLl92KFwiIFwiICsgX3ZtLl9zKF92bS4kdChcImVkaXRfaW1hZ2VcIikpKSxcbiAgICAgICAgICAgIF1cbiAgICAgICAgICApLFxuICAgICAgICBdKSxcbiAgICAgIF0pLFxuICAgIF0pLFxuICAgIF92bS5fdihcIiBcIiksXG4gICAgX2MoXCJiclwiKSxcbiAgICBfdm0uX3YoXCIgXCIpLFxuICAgIF92bS5hbGVydFxuICAgICAgPyBfYyhcImRpdlwiLCB7XG4gICAgICAgICAgc3RhdGljQ2xhc3M6IFwiYWxlcnQgXCIsXG4gICAgICAgICAgY2xhc3M6IFtfdm0uYWxlcnRUeXBlID09PSBcImVycm9yXCIgPyBcImFsZXJ0LWRhbmdlclwiIDogXCJhbGVydC1zdWNjZXNzXCJdLFxuICAgICAgICAgIGF0dHJzOiB7IHJvbGU6IFwiYWxlcnRcIiB9LFxuICAgICAgICAgIGRvbVByb3BzOiB7IGlubmVySFRNTDogX3ZtLl9zKF92bS5hbGVydE1lc3NhZ2UpIH0sXG4gICAgICAgIH0pXG4gICAgICA6IF92bS5fZSgpLFxuICAgIF92bS5fdihcIiBcIiksXG4gICAgX2MoXCJiclwiKSxcbiAgICBfdm0uX3YoXCIgXCIpLFxuICAgIF9jKFxuICAgICAgXCJkaXZcIixcbiAgICAgIHtcbiAgICAgICAgc3RhdGljQ2xhc3M6IFwibW9kYWwgZmFkZVwiLFxuICAgICAgICBhdHRyczogeyBpZDogXCJpbWFnZU1vZGFsXCIsIHRhYmluZGV4OiBcIi0xXCIsIHJvbGU6IFwiZGlhbG9nXCIgfSxcbiAgICAgIH0sXG4gICAgICBbXG4gICAgICAgIF9jKFxuICAgICAgICAgIFwiZGl2XCIsXG4gICAgICAgICAge1xuICAgICAgICAgICAgc3RhdGljQ2xhc3M6IFwibW9kYWwtZGlhbG9nICBtb2RhbC1mdWxsXCIsXG4gICAgICAgICAgICBhdHRyczogeyByb2xlOiBcImRvY3VtZW50XCIgfSxcbiAgICAgICAgICB9LFxuICAgICAgICAgIFtcbiAgICAgICAgICAgIF9jKFwiZGl2XCIsIHsgc3RhdGljQ2xhc3M6IFwibW9kYWwtY29udGVudFwiIH0sIFtcbiAgICAgICAgICAgICAgX2MoXCJkaXZcIiwgeyBzdGF0aWNDbGFzczogXCJtb2RhbC1oZWFkZXJcIiB9LCBbXG4gICAgICAgICAgICAgICAgX3ZtLl9tKDApLFxuICAgICAgICAgICAgICAgIF92bS5fdihcIiBcIiksXG4gICAgICAgICAgICAgICAgX2MoXCJoNFwiLCB7IHN0YXRpY0NsYXNzOiBcIm1vZGFsLXRpdGxlXCIgfSwgW1xuICAgICAgICAgICAgICAgICAgX3ZtLl92KF92bS5fcyhfdm0uJHQoXCJjcmVhdGVkX2ltYWdlXCIpKSksXG4gICAgICAgICAgICAgICAgXSksXG4gICAgICAgICAgICAgIF0pLFxuICAgICAgICAgICAgICBfdm0uX3YoXCIgXCIpLFxuICAgICAgICAgICAgICBfYyhcImRpdlwiLCB7IHN0YXRpY0NsYXNzOiBcIm1vZGFsLWJvZHkgYWxpZ24tY2VudGVyXCIgfSwgW1xuICAgICAgICAgICAgICAgIF9jKFwiZGl2XCIsIHsgc3RhdGljQ2xhc3M6IFwicm93XCIgfSwgW1xuICAgICAgICAgICAgICAgICAgX2MoXCJkaXZcIiwgeyBzdGF0aWNDbGFzczogXCJjb2wtbWQtNiBhbGlnbi1jZW50ZXJcIiB9LCBbXG4gICAgICAgICAgICAgICAgICAgIF9jKFwiaDNcIiwgW192bS5fdihfdm0uX3MoX3ZtLiR0KFwib3JpZ2luYWxfaW1hZ2VcIikpKV0pLFxuICAgICAgICAgICAgICAgICAgICBfdm0uX3YoXCIgXCIpLFxuICAgICAgICAgICAgICAgICAgICBfYyhcInBcIiwgW1xuICAgICAgICAgICAgICAgICAgICAgIF9jKFwiaW1nXCIsIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHN0YXRpY0NsYXNzOiBcImltZy1yZXNwb25zaXZlIGNlbnRlci1ibG9ja1wiLFxuICAgICAgICAgICAgICAgICAgICAgICAgYXR0cnM6IHsgc3JjOiBfdm0uaW1hZ2VVUkwgfSxcbiAgICAgICAgICAgICAgICAgICAgICB9KSxcbiAgICAgICAgICAgICAgICAgICAgXSksXG4gICAgICAgICAgICAgICAgICBdKSxcbiAgICAgICAgICAgICAgICAgIF92bS5fdihcIiBcIiksXG4gICAgICAgICAgICAgICAgICBfYyhcImRpdlwiLCB7IHN0YXRpY0NsYXNzOiBcImNvbC1tZC02IGFsaWduLWNlbnRlclwiIH0sIFtcbiAgICAgICAgICAgICAgICAgICAgX2MoXCJoM1wiLCBbX3ZtLl92KF92bS5fcyhfdm0uJHQoXCJuZXdfaW1hZ2VcIikpKV0pLFxuICAgICAgICAgICAgICAgICAgICBfdm0uX3YoXCIgXCIpLFxuICAgICAgICAgICAgICAgICAgICBfYyhcInBcIiwgW1xuICAgICAgICAgICAgICAgICAgICAgIF9jKFwiaW1nXCIsIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHN0YXRpY0NsYXNzOiBcImltZy1yZXNwb25zaXZlIGNlbnRlci1ibG9ja1wiLFxuICAgICAgICAgICAgICAgICAgICAgICAgYXR0cnM6IHsgc3JjOiBfdm0ubmV3SW1hZ2UgfSxcbiAgICAgICAgICAgICAgICAgICAgICB9KSxcbiAgICAgICAgICAgICAgICAgICAgXSksXG4gICAgICAgICAgICAgICAgICBdKSxcbiAgICAgICAgICAgICAgICBdKSxcbiAgICAgICAgICAgICAgXSksXG4gICAgICAgICAgICAgIF92bS5fdihcIiBcIiksXG4gICAgICAgICAgICAgIF9jKFwiZGl2XCIsIHsgc3RhdGljQ2xhc3M6IFwibW9kYWwtZm9vdGVyXCIgfSwgW1xuICAgICAgICAgICAgICAgIF9jKFxuICAgICAgICAgICAgICAgICAgXCJidXR0b25cIixcbiAgICAgICAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICAgICAgc3RhdGljQ2xhc3M6IFwiYnRuIGJ0bi1kZWZhdWx0XCIsXG4gICAgICAgICAgICAgICAgICAgIGF0dHJzOiB7IHR5cGU6IFwiYnV0dG9uXCIsIFwiZGF0YS1kaXNtaXNzXCI6IFwibW9kYWxcIiB9LFxuICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAgIFtfdm0uX3YoX3ZtLl9zKF92bS4kdChcImNhbmNlbFwiKSkpXVxuICAgICAgICAgICAgICAgICksXG4gICAgICAgICAgICAgICAgX3ZtLl92KFwiIFwiKSxcbiAgICAgICAgICAgICAgICBfYyhcbiAgICAgICAgICAgICAgICAgIFwiYnV0dG9uXCIsXG4gICAgICAgICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgICAgIHN0YXRpY0NsYXNzOiBcImJ0biBidG4td2FybmluZ1wiLFxuICAgICAgICAgICAgICAgICAgICBhdHRyczogeyB0eXBlOiBcImJ1dHRvblwiIH0sXG4gICAgICAgICAgICAgICAgICAgIG9uOiB7XG4gICAgICAgICAgICAgICAgICAgICAgY2xpY2s6IGZ1bmN0aW9uICgkZXZlbnQpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICRldmVudC5wcmV2ZW50RGVmYXVsdCgpXG4gICAgICAgICAgICAgICAgICAgICAgICByZXR1cm4gX3ZtLnVwZGF0ZUltYWdlLmFwcGx5KG51bGwsIGFyZ3VtZW50cylcbiAgICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAgIFtfdm0uX3YoX3ZtLl9zKF92bS4kdChcInJlcGxhY2VfaW1hZ2VcIikpKV1cbiAgICAgICAgICAgICAgICApLFxuICAgICAgICAgICAgICAgIF92bS5fdihcIiBcIiksXG4gICAgICAgICAgICAgICAgX2MoXG4gICAgICAgICAgICAgICAgICBcImJ1dHRvblwiLFxuICAgICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICBzdGF0aWNDbGFzczogXCJidG4gYnRuLXByaW1hcnlcIixcbiAgICAgICAgICAgICAgICAgICAgYXR0cnM6IHsgdHlwZTogXCJidXR0b25cIiB9LFxuICAgICAgICAgICAgICAgICAgICBvbjoge1xuICAgICAgICAgICAgICAgICAgICAgIGNsaWNrOiBmdW5jdGlvbiAoJGV2ZW50KSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAkZXZlbnQucHJldmVudERlZmF1bHQoKVxuICAgICAgICAgICAgICAgICAgICAgICAgcmV0dXJuIF92bS5jbG9uZUltYWdlLmFwcGx5KG51bGwsIGFyZ3VtZW50cylcbiAgICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAgIFtfdm0uX3YoX3ZtLl9zKF92bS4kdChcImNyZWF0ZV9uZXdfaW1hZ2VcIikpKV1cbiAgICAgICAgICAgICAgICApLFxuICAgICAgICAgICAgICBdKSxcbiAgICAgICAgICAgIF0pLFxuICAgICAgICAgIF1cbiAgICAgICAgKSxcbiAgICAgIF1cbiAgICApLFxuICAgIF92bS5fdihcIiBcIiksXG4gICAgX2MoXG4gICAgICBcImRpdlwiLFxuICAgICAge1xuICAgICAgICBzdGF0aWNDbGFzczogXCJtb2RhbCBmYWRlXCIsXG4gICAgICAgIGF0dHJzOiB7IGlkOiBcImNvbmZpcm1Nb2RhbFwiLCB0YWJpbmRleDogXCItMVwiLCByb2xlOiBcImRpYWxvZ1wiIH0sXG4gICAgICB9LFxuICAgICAgW1xuICAgICAgICBfYyhcbiAgICAgICAgICBcImRpdlwiLFxuICAgICAgICAgIHsgc3RhdGljQ2xhc3M6IFwibW9kYWwtZGlhbG9nXCIsIGF0dHJzOiB7IHJvbGU6IFwiZG9jdW1lbnRcIiB9IH0sXG4gICAgICAgICAgW1xuICAgICAgICAgICAgX2MoXCJkaXZcIiwgeyBzdGF0aWNDbGFzczogXCJtb2RhbC1jb250ZW50XCIgfSwgW1xuICAgICAgICAgICAgICBfYyhcImRpdlwiLCB7IHN0YXRpY0NsYXNzOiBcIm1vZGFsLWhlYWRlclwiIH0sIFtcbiAgICAgICAgICAgICAgICBfdm0uX20oMSksXG4gICAgICAgICAgICAgICAgX3ZtLl92KFwiIFwiKSxcbiAgICAgICAgICAgICAgICBfYyhcImg0XCIsIHsgc3RhdGljQ2xhc3M6IFwibW9kYWwtdGl0bGVcIiB9LCBbXG4gICAgICAgICAgICAgICAgICBfdm0uX3YoX3ZtLl9zKF92bS4kdChcImFyZV95b3Vfc3VyZVwiKSkpLFxuICAgICAgICAgICAgICAgIF0pLFxuICAgICAgICAgICAgICBdKSxcbiAgICAgICAgICAgICAgX3ZtLl92KFwiIFwiKSxcbiAgICAgICAgICAgICAgX2MoXCJkaXZcIiwgeyBzdGF0aWNDbGFzczogXCJtb2RhbC1ib2R5XCIgfSwgW1xuICAgICAgICAgICAgICAgIF9jKFwicFwiLCBbXG4gICAgICAgICAgICAgICAgICBfdm0uX3YoXG4gICAgICAgICAgICAgICAgICAgIFwiXFxuICAgICAgICAgICAgICAgICAgICAgICAgXCIgK1xuICAgICAgICAgICAgICAgICAgICAgIF92bS5fcyhfdm0uJHQoXCJtZXNzYWdlLmNhbm5vdF9iZV91bmRvbmVcIikpICtcbiAgICAgICAgICAgICAgICAgICAgICBcIlxcbiAgICAgICAgICAgICAgICAgICAgICAgIFwiXG4gICAgICAgICAgICAgICAgICApLFxuICAgICAgICAgICAgICAgIF0pLFxuICAgICAgICAgICAgICBdKSxcbiAgICAgICAgICAgICAgX3ZtLl92KFwiIFwiKSxcbiAgICAgICAgICAgICAgX2MoXCJkaXZcIiwgeyBzdGF0aWNDbGFzczogXCJtb2RhbC1mb290ZXJcIiB9LCBbXG4gICAgICAgICAgICAgICAgX2MoXG4gICAgICAgICAgICAgICAgICBcImJ1dHRvblwiLFxuICAgICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICBzdGF0aWNDbGFzczogXCJidG4gYnRuLXdhcm5pbmdcIixcbiAgICAgICAgICAgICAgICAgICAgYXR0cnM6IHsgdHlwZTogXCJidXR0b25cIiB9LFxuICAgICAgICAgICAgICAgICAgICBvbjoge1xuICAgICAgICAgICAgICAgICAgICAgIGNsaWNrOiBmdW5jdGlvbiAoJGV2ZW50KSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAkZXZlbnQucHJldmVudERlZmF1bHQoKVxuICAgICAgICAgICAgICAgICAgICAgICAgcmV0dXJuIF92bS5jb25maXJtVXBkYXRlLmFwcGx5KG51bGwsIGFyZ3VtZW50cylcbiAgICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAgIFtfdm0uX3YoX3ZtLl9zKF92bS4kdChcImNvbnRpbnVlXCIpKSldXG4gICAgICAgICAgICAgICAgKSxcbiAgICAgICAgICAgICAgICBfdm0uX3YoXCIgXCIpLFxuICAgICAgICAgICAgICAgIF9jKFxuICAgICAgICAgICAgICAgICAgXCJidXR0b25cIixcbiAgICAgICAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICAgICAgc3RhdGljQ2xhc3M6IFwiYnRuIGJ0bi1kZWZhdWx0XCIsXG4gICAgICAgICAgICAgICAgICAgIGF0dHJzOiB7IHR5cGU6IFwiYnV0dG9uXCIgfSxcbiAgICAgICAgICAgICAgICAgICAgb246IHtcbiAgICAgICAgICAgICAgICAgICAgICBjbGljazogZnVuY3Rpb24gKCRldmVudCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgJGV2ZW50LnByZXZlbnREZWZhdWx0KClcbiAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybiBfdm0ucmVqZWN0VXBkYXRlLmFwcGx5KG51bGwsIGFyZ3VtZW50cylcbiAgICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAgIFtfdm0uX3YoX3ZtLl9zKF92bS4kdChcImNhbmNlbFwiKSkpXVxuICAgICAgICAgICAgICAgICksXG4gICAgICAgICAgICAgIF0pLFxuICAgICAgICAgICAgXSksXG4gICAgICAgICAgXVxuICAgICAgICApLFxuICAgICAgXVxuICAgICksXG4gIF0pXG59XG52YXIgc3RhdGljUmVuZGVyRm5zID0gW1xuICBmdW5jdGlvbiAoKSB7XG4gICAgdmFyIF92bSA9IHRoaXNcbiAgICB2YXIgX2ggPSBfdm0uJGNyZWF0ZUVsZW1lbnRcbiAgICB2YXIgX2MgPSBfdm0uX3NlbGYuX2MgfHwgX2hcbiAgICByZXR1cm4gX2MoXG4gICAgICBcImJ1dHRvblwiLFxuICAgICAge1xuICAgICAgICBzdGF0aWNDbGFzczogXCJjbG9zZVwiLFxuICAgICAgICBhdHRyczoge1xuICAgICAgICAgIHR5cGU6IFwiYnV0dG9uXCIsXG4gICAgICAgICAgXCJkYXRhLWRpc21pc3NcIjogXCJtb2RhbFwiLFxuICAgICAgICAgIFwiYXJpYS1sYWJlbFwiOiBcIkNsb3NlXCIsXG4gICAgICAgIH0sXG4gICAgICB9LFxuICAgICAgW19jKFwic3BhblwiLCB7IGF0dHJzOiB7IFwiYXJpYS1oaWRkZW5cIjogXCJ0cnVlXCIgfSB9LCBbX3ZtLl92KFwiw5dcIildKV1cbiAgICApXG4gIH0sXG4gIGZ1bmN0aW9uICgpIHtcbiAgICB2YXIgX3ZtID0gdGhpc1xuICAgIHZhciBfaCA9IF92bS4kY3JlYXRlRWxlbWVudFxuICAgIHZhciBfYyA9IF92bS5fc2VsZi5fYyB8fCBfaFxuICAgIHJldHVybiBfYyhcbiAgICAgIFwiYnV0dG9uXCIsXG4gICAgICB7XG4gICAgICAgIHN0YXRpY0NsYXNzOiBcImNsb3NlXCIsXG4gICAgICAgIGF0dHJzOiB7XG4gICAgICAgICAgdHlwZTogXCJidXR0b25cIixcbiAgICAgICAgICBcImRhdGEtZGlzbWlzc1wiOiBcIm1vZGFsXCIsXG4gICAgICAgICAgXCJhcmlhLWxhYmVsXCI6IFwiQ2xvc2VcIixcbiAgICAgICAgfSxcbiAgICAgIH0sXG4gICAgICBbX2MoXCJzcGFuXCIsIHsgYXR0cnM6IHsgXCJhcmlhLWhpZGRlblwiOiBcInRydWVcIiB9IH0sIFtfdm0uX3YoXCLDl1wiKV0pXVxuICAgIClcbiAgfSxcbl1cbnJlbmRlci5fd2l0aFN0cmlwcGVkID0gdHJ1ZVxuXG5leHBvcnQgeyByZW5kZXIsIHN0YXRpY1JlbmRlckZucyB9IiwibW9kdWxlLmV4cG9ydHMgPSBGaWxlcm9ib3RJbWFnZUVkaXRvcjsiLCIvLyBUaGUgbW9kdWxlIGNhY2hlXG52YXIgX193ZWJwYWNrX21vZHVsZV9jYWNoZV9fID0ge307XG5cbi8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG5mdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cdC8vIENoZWNrIGlmIG1vZHVsZSBpcyBpbiBjYWNoZVxuXHR2YXIgY2FjaGVkTW9kdWxlID0gX193ZWJwYWNrX21vZHVsZV9jYWNoZV9fW21vZHVsZUlkXTtcblx0aWYgKGNhY2hlZE1vZHVsZSAhPT0gdW5kZWZpbmVkKSB7XG5cdFx0cmV0dXJuIGNhY2hlZE1vZHVsZS5leHBvcnRzO1xuXHR9XG5cdC8vIENyZWF0ZSBhIG5ldyBtb2R1bGUgKGFuZCBwdXQgaXQgaW50byB0aGUgY2FjaGUpXG5cdHZhciBtb2R1bGUgPSBfX3dlYnBhY2tfbW9kdWxlX2NhY2hlX19bbW9kdWxlSWRdID0ge1xuXHRcdC8vIG5vIG1vZHVsZS5pZCBuZWVkZWRcblx0XHQvLyBubyBtb2R1bGUubG9hZGVkIG5lZWRlZFxuXHRcdGV4cG9ydHM6IHt9XG5cdH07XG5cblx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG5cdF9fd2VicGFja19tb2R1bGVzX19bbW9kdWxlSWRdKG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG5cdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG5cdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbn1cblxuLy8gZXhwb3NlIHRoZSBtb2R1bGVzIG9iamVjdCAoX193ZWJwYWNrX21vZHVsZXNfXylcbl9fd2VicGFja19yZXF1aXJlX18ubSA9IF9fd2VicGFja19tb2R1bGVzX187XG5cbiIsInZhciBkZWZlcnJlZCA9IFtdO1xuX193ZWJwYWNrX3JlcXVpcmVfXy5PID0gKHJlc3VsdCwgY2h1bmtJZHMsIGZuLCBwcmlvcml0eSkgPT4ge1xuXHRpZihjaHVua0lkcykge1xuXHRcdHByaW9yaXR5ID0gcHJpb3JpdHkgfHwgMDtcblx0XHRmb3IodmFyIGkgPSBkZWZlcnJlZC5sZW5ndGg7IGkgPiAwICYmIGRlZmVycmVkW2kgLSAxXVsyXSA+IHByaW9yaXR5OyBpLS0pIGRlZmVycmVkW2ldID0gZGVmZXJyZWRbaSAtIDFdO1xuXHRcdGRlZmVycmVkW2ldID0gW2NodW5rSWRzLCBmbiwgcHJpb3JpdHldO1xuXHRcdHJldHVybjtcblx0fVxuXHR2YXIgbm90RnVsZmlsbGVkID0gSW5maW5pdHk7XG5cdGZvciAodmFyIGkgPSAwOyBpIDwgZGVmZXJyZWQubGVuZ3RoOyBpKyspIHtcblx0XHR2YXIgW2NodW5rSWRzLCBmbiwgcHJpb3JpdHldID0gZGVmZXJyZWRbaV07XG5cdFx0dmFyIGZ1bGZpbGxlZCA9IHRydWU7XG5cdFx0Zm9yICh2YXIgaiA9IDA7IGogPCBjaHVua0lkcy5sZW5ndGg7IGorKykge1xuXHRcdFx0aWYgKChwcmlvcml0eSAmIDEgPT09IDAgfHwgbm90RnVsZmlsbGVkID49IHByaW9yaXR5KSAmJiBPYmplY3Qua2V5cyhfX3dlYnBhY2tfcmVxdWlyZV9fLk8pLmV2ZXJ5KChrZXkpID0+IChfX3dlYnBhY2tfcmVxdWlyZV9fLk9ba2V5XShjaHVua0lkc1tqXSkpKSkge1xuXHRcdFx0XHRjaHVua0lkcy5zcGxpY2Uoai0tLCAxKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdGZ1bGZpbGxlZCA9IGZhbHNlO1xuXHRcdFx0XHRpZihwcmlvcml0eSA8IG5vdEZ1bGZpbGxlZCkgbm90RnVsZmlsbGVkID0gcHJpb3JpdHk7XG5cdFx0XHR9XG5cdFx0fVxuXHRcdGlmKGZ1bGZpbGxlZCkge1xuXHRcdFx0ZGVmZXJyZWQuc3BsaWNlKGktLSwgMSlcblx0XHRcdHZhciByID0gZm4oKTtcblx0XHRcdGlmIChyICE9PSB1bmRlZmluZWQpIHJlc3VsdCA9IHI7XG5cdFx0fVxuXHR9XG5cdHJldHVybiByZXN1bHQ7XG59OyIsIi8vIGdldERlZmF1bHRFeHBvcnQgZnVuY3Rpb24gZm9yIGNvbXBhdGliaWxpdHkgd2l0aCBub24taGFybW9ueSBtb2R1bGVzXG5fX3dlYnBhY2tfcmVxdWlyZV9fLm4gPSAobW9kdWxlKSA9PiB7XG5cdHZhciBnZXR0ZXIgPSBtb2R1bGUgJiYgbW9kdWxlLl9fZXNNb2R1bGUgP1xuXHRcdCgpID0+IChtb2R1bGVbJ2RlZmF1bHQnXSkgOlxuXHRcdCgpID0+IChtb2R1bGUpO1xuXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQoZ2V0dGVyLCB7IGE6IGdldHRlciB9KTtcblx0cmV0dXJuIGdldHRlcjtcbn07IiwiLy8gZGVmaW5lIGdldHRlciBmdW5jdGlvbnMgZm9yIGhhcm1vbnkgZXhwb3J0c1xuX193ZWJwYWNrX3JlcXVpcmVfXy5kID0gKGV4cG9ydHMsIGRlZmluaXRpb24pID0+IHtcblx0Zm9yKHZhciBrZXkgaW4gZGVmaW5pdGlvbikge1xuXHRcdGlmKF9fd2VicGFja19yZXF1aXJlX18ubyhkZWZpbml0aW9uLCBrZXkpICYmICFfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZXhwb3J0cywga2V5KSkge1xuXHRcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIGtleSwgeyBlbnVtZXJhYmxlOiB0cnVlLCBnZXQ6IGRlZmluaXRpb25ba2V5XSB9KTtcblx0XHR9XG5cdH1cbn07IiwiX193ZWJwYWNrX3JlcXVpcmVfXy5nID0gKGZ1bmN0aW9uKCkge1xuXHRpZiAodHlwZW9mIGdsb2JhbFRoaXMgPT09ICdvYmplY3QnKSByZXR1cm4gZ2xvYmFsVGhpcztcblx0dHJ5IHtcblx0XHRyZXR1cm4gdGhpcyB8fCBuZXcgRnVuY3Rpb24oJ3JldHVybiB0aGlzJykoKTtcblx0fSBjYXRjaCAoZSkge1xuXHRcdGlmICh0eXBlb2Ygd2luZG93ID09PSAnb2JqZWN0JykgcmV0dXJuIHdpbmRvdztcblx0fVxufSkoKTsiLCJfX3dlYnBhY2tfcmVxdWlyZV9fLm8gPSAob2JqLCBwcm9wKSA9PiAoT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsKG9iaiwgcHJvcCkpIiwiLy8gZGVmaW5lIF9fZXNNb2R1bGUgb24gZXhwb3J0c1xuX193ZWJwYWNrX3JlcXVpcmVfXy5yID0gKGV4cG9ydHMpID0+IHtcblx0aWYodHlwZW9mIFN5bWJvbCAhPT0gJ3VuZGVmaW5lZCcgJiYgU3ltYm9sLnRvU3RyaW5nVGFnKSB7XG5cdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIFN5bWJvbC50b1N0cmluZ1RhZywgeyB2YWx1ZTogJ01vZHVsZScgfSk7XG5cdH1cblx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsICdfX2VzTW9kdWxlJywgeyB2YWx1ZTogdHJ1ZSB9KTtcbn07IiwiLy8gbm8gYmFzZVVSSVxuXG4vLyBvYmplY3QgdG8gc3RvcmUgbG9hZGVkIGFuZCBsb2FkaW5nIGNodW5rc1xuLy8gdW5kZWZpbmVkID0gY2h1bmsgbm90IGxvYWRlZCwgbnVsbCA9IGNodW5rIHByZWxvYWRlZC9wcmVmZXRjaGVkXG4vLyBbcmVzb2x2ZSwgcmVqZWN0LCBQcm9taXNlXSA9IGNodW5rIGxvYWRpbmcsIDAgPSBjaHVuayBsb2FkZWRcbnZhciBpbnN0YWxsZWRDaHVua3MgPSB7XG5cdFwiaW1hZ2VFZGl0b3JcIjogMFxufTtcblxuLy8gbm8gY2h1bmsgb24gZGVtYW5kIGxvYWRpbmdcblxuLy8gbm8gcHJlZmV0Y2hpbmdcblxuLy8gbm8gcHJlbG9hZGVkXG5cbi8vIG5vIEhNUlxuXG4vLyBubyBITVIgbWFuaWZlc3RcblxuX193ZWJwYWNrX3JlcXVpcmVfXy5PLmogPSAoY2h1bmtJZCkgPT4gKGluc3RhbGxlZENodW5rc1tjaHVua0lkXSA9PT0gMCk7XG5cbi8vIGluc3RhbGwgYSBKU09OUCBjYWxsYmFjayBmb3IgY2h1bmsgbG9hZGluZ1xudmFyIHdlYnBhY2tKc29ucENhbGxiYWNrID0gKHBhcmVudENodW5rTG9hZGluZ0Z1bmN0aW9uLCBkYXRhKSA9PiB7XG5cdHZhciBbY2h1bmtJZHMsIG1vcmVNb2R1bGVzLCBydW50aW1lXSA9IGRhdGE7XG5cdC8vIGFkZCBcIm1vcmVNb2R1bGVzXCIgdG8gdGhlIG1vZHVsZXMgb2JqZWN0LFxuXHQvLyB0aGVuIGZsYWcgYWxsIFwiY2h1bmtJZHNcIiBhcyBsb2FkZWQgYW5kIGZpcmUgY2FsbGJhY2tcblx0dmFyIG1vZHVsZUlkLCBjaHVua0lkLCBpID0gMDtcblx0aWYoY2h1bmtJZHMuc29tZSgoaWQpID0+IChpbnN0YWxsZWRDaHVua3NbaWRdICE9PSAwKSkpIHtcblx0XHRmb3IobW9kdWxlSWQgaW4gbW9yZU1vZHVsZXMpIHtcblx0XHRcdGlmKF9fd2VicGFja19yZXF1aXJlX18ubyhtb3JlTW9kdWxlcywgbW9kdWxlSWQpKSB7XG5cdFx0XHRcdF9fd2VicGFja19yZXF1aXJlX18ubVttb2R1bGVJZF0gPSBtb3JlTW9kdWxlc1ttb2R1bGVJZF07XG5cdFx0XHR9XG5cdFx0fVxuXHRcdGlmKHJ1bnRpbWUpIHZhciByZXN1bHQgPSBydW50aW1lKF9fd2VicGFja19yZXF1aXJlX18pO1xuXHR9XG5cdGlmKHBhcmVudENodW5rTG9hZGluZ0Z1bmN0aW9uKSBwYXJlbnRDaHVua0xvYWRpbmdGdW5jdGlvbihkYXRhKTtcblx0Zm9yKDtpIDwgY2h1bmtJZHMubGVuZ3RoOyBpKyspIHtcblx0XHRjaHVua0lkID0gY2h1bmtJZHNbaV07XG5cdFx0aWYoX193ZWJwYWNrX3JlcXVpcmVfXy5vKGluc3RhbGxlZENodW5rcywgY2h1bmtJZCkgJiYgaW5zdGFsbGVkQ2h1bmtzW2NodW5rSWRdKSB7XG5cdFx0XHRpbnN0YWxsZWRDaHVua3NbY2h1bmtJZF1bMF0oKTtcblx0XHR9XG5cdFx0aW5zdGFsbGVkQ2h1bmtzW2NodW5rSWRdID0gMDtcblx0fVxuXHRyZXR1cm4gX193ZWJwYWNrX3JlcXVpcmVfXy5PKHJlc3VsdCk7XG59XG5cbnZhciBjaHVua0xvYWRpbmdHbG9iYWwgPSBzZWxmW1wid2VicGFja0NodW5raW5pdF9jbXNfYnVuZGxlXCJdID0gc2VsZltcIndlYnBhY2tDaHVua2luaXRfY21zX2J1bmRsZVwiXSB8fCBbXTtcbmNodW5rTG9hZGluZ0dsb2JhbC5mb3JFYWNoKHdlYnBhY2tKc29ucENhbGxiYWNrLmJpbmQobnVsbCwgMCkpO1xuY2h1bmtMb2FkaW5nR2xvYmFsLnB1c2ggPSB3ZWJwYWNrSnNvbnBDYWxsYmFjay5iaW5kKG51bGwsIGNodW5rTG9hZGluZ0dsb2JhbC5wdXNoLmJpbmQoY2h1bmtMb2FkaW5nR2xvYmFsKSk7IiwiIiwiLy8gc3RhcnR1cFxuLy8gTG9hZCBlbnRyeSBtb2R1bGUgYW5kIHJldHVybiBleHBvcnRzXG4vLyBUaGlzIGVudHJ5IG1vZHVsZSBkZXBlbmRzIG9uIG90aGVyIGxvYWRlZCBjaHVua3MgYW5kIGV4ZWN1dGlvbiBuZWVkIHRvIGJlIGRlbGF5ZWRcbnZhciBfX3dlYnBhY2tfZXhwb3J0c19fID0gX193ZWJwYWNrX3JlcXVpcmVfXy5PKHVuZGVmaW5lZCwgW1widmVuZG9ycy1ub2RlX21vZHVsZXNfY29yZS1qc19tb2R1bGVzX2VzX2FycmF5X2luY2x1ZGVzX2pzLW5vZGVfbW9kdWxlc19jb3JlLWpzX21vZHVsZXNfZXNfYXJyLWMyZGU3MFwiXSwgKCkgPT4gKF9fd2VicGFja19yZXF1aXJlX18oXCIuL3NyYy9SZXNvdXJjZXMvcHVibGljL2pzL2ZpbGVib3QuanNcIikpKVxuX193ZWJwYWNrX2V4cG9ydHNfXyA9IF9fd2VicGFja19yZXF1aXJlX18uTyhfX3dlYnBhY2tfZXhwb3J0c19fKTtcbiIsIiJdLCJuYW1lcyI6WyJoZWFkZXJzIiwibGFuZ2F1Z2UiLCJuYW1lIiwibW91bnRlZCIsImNvbG9yU2NoZW1lIiwidG9vbHMiLCJsYW5ndWFnZSIsInRyYW5zbGF0aW9ucyIsImVuIiwiZGUiLCJkYXRhIiwibG9jYWxlIiwiaW1hZ2VVUkwiLCJpZCIsImNvbnRleHQiLCJwcm92aWRlciIsImltYWdlRWRpdG9yIiwibmV3SW1hZ2UiLCJhbGVydCIsImFsZXJ0TWVzc2FnZSIsImFsZXJ0VHlwZSIsImZpbGVFeHRlbnNpb24iLCJ3YXRjaCIsInZhbCIsIm1ldGhvZHMiLCJlZGl0SW1hZ2UiLCJkb3dubG9hZCIsIiQiLCJjbG9uZUltYWdlIiwiYXhpb3MiLCJwb3N0IiwiYXhpb3NDb25maWciLCJ0aGVuIiwiY29uc29sZSIsInVwZGF0ZUltYWdlIiwicmVqZWN0VXBkYXRlIiwiY29uZmlybVVwZGF0ZSIsIndpbmRvdyIsIkZpbGVyb2JvdEltYWdlRWRpdG9yQmFzZSIsIkZpbGVyb2JvdEltYWdlRWRpdG9yIiwiVnVlIiwiVnVlSTE4biIsIkVkaXRvciIsInVzZSIsImkxOG4iLCJtZXNzYWdlcyIsInJlbmRlciIsImgiLCIkbW91bnQiXSwic291cmNlUm9vdCI6IiJ9