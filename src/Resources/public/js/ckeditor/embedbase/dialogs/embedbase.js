/*! For license information please see embedbase.js.LICENSE.txt */
CKEDITOR.dialog.add("embedBase",(function(e){"use strict";var t=e.lang.embedbase;return{title:t.title,minWidth:350,minHeight:50,onLoad:function(){var t=this,n=null;function i(){t.setState(CKEDITOR.DIALOG_STATE_IDLE),n=null}this.on("ok",(function(a){a.data.hide=!1,a.stop(),t.setState(CKEDITOR.DIALOG_STATE_BUSY);var l=t.getValueOf("info","url"),o=t.getModel(e);n=o.loadContent(l,{noNotifications:!0,callback:function(){o.isReady()||e.widgets.finalizeCreation(o.wrapper.getParent(!0)),e.fire("saveSnapshot"),t.hide(),i()},errorCallback:function(e){t.getContentElement("info","url").select(),alert(o.getErrorMessage(e,l,"Given")),i()}})}),null,null,15),this.on("cancel",(function(e){e.data.hide&&n&&(n.cancel(),i())}))},contents:[{id:"info",elements:[{type:"text",id:"url",label:e.lang.common.url,required:!0,setup:function(e){this.setValue(e.data.url)},validate:function(){return!!this.getDialog().getModel(e).isUrlValid(this.getValue())||t.unsupportedUrlGiven}}]}]}}));