/*! For license information please see plugin.js.LICENSE.txt */
"use strict";!function(){function t(t,e){for(var n=t.length,r=0,a=0;a<n;a+=1){var s=t[a];if(e>=r&&r+s.getText().length>=e)return{element:s,offset:e-r};r+=s.getText().length}return null}CKEDITOR.plugins.add("textmatch",{}),CKEDITOR.plugins.textMatch={},CKEDITOR.plugins.textMatch.match=function(t,e){var n=CKEDITOR.plugins.textMatch.getTextAndOffset(t),r=CKEDITOR.dom.selection.FILLING_CHAR_SEQUENCE,a=0;if(n){0==n.text.indexOf(r)&&(a=r.length,n.text=n.text.replace(r,""),n.offset-=a);var s=e(n.text,n.offset);return s?{range:CKEDITOR.plugins.textMatch.getRangeInText(t,s.start,s.end+a),text:n.text.slice(s.start,s.end)}:null}},CKEDITOR.plugins.textMatch.getTextAndOffset=function(t){if(!t.collapsed)return null;var e,n="",r=0,a=CKEDITOR.plugins.textMatch.getAdjacentTextNodes(t),s=!1,g=t.startContainer.type!=CKEDITOR.NODE_ELEMENT;e=g?function(t,e){for(var n=0;n<t.length;n++)if(e(t[n]))return n;return-1}(a,(function(e){return t.startContainer.equals(e)})):t.startOffset-(a[0]?a[0].getIndex():0);for(var f=a.length,l=0;l<f;l+=1){var o=a[l];n+=o.getText(),s||(g?l==e?(s=!0,r+=t.startOffset):r+=o.getText().length:(l==e&&(s=!0),l>0&&(r+=a[l-1].getText().length),f==e&&l+1==f&&(r+=o.getText().length)))}return{text:n,offset:r}},CKEDITOR.plugins.textMatch.getRangeInText=function(e,n,r){var a=new CKEDITOR.dom.range(e.root),s=CKEDITOR.plugins.textMatch.getAdjacentTextNodes(e),g=t(s,n),f=t(s,r);return a.setStart(g.element,g.offset),a.setEnd(f.element,f.offset),a},CKEDITOR.plugins.textMatch.getAdjacentTextNodes=function(t){if(!t.collapsed)throw new Error("Range must be collapsed.");var e,n,r,a,s=[];for(t.startContainer.type!=CKEDITOR.NODE_ELEMENT?(e=t.startContainer.getParent().getChildren(),n=t.startContainer.getIndex()):(e=t.startContainer.getChildren(),n=t.startOffset),a=n;(r=e.getItem(--a))&&r.type==CKEDITOR.NODE_TEXT;)s.unshift(r);for(a=n;(r=e.getItem(a++))&&r.type==CKEDITOR.NODE_TEXT;)s.push(r);return s}}();