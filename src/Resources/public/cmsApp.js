const App={init(e){this.setup_xeditable(e)},create_xeditable(e,t,a,n){let i=$(e);return i.unbind(),i.editable(t).on("contextmenu",(function(e){$(this).editable("show"),e.preventDefault()})).on("save",a).on("hidden",n),i},setup_xeditable(e,t){jQuery(".x-editable",e).editable({emptyclass:"editable-empty btn btn-sm btn-default",emptytext:'<i class="fas fa-pencil-alt"></i>',container:"body",placement:"auto",params:function(e){if(e.pk=jQuery(this).attr("data-pk"),this.dataset.xEditableParams){JSON.parse(this.dataset.xEditableParams).forEach((function(t){Object.entries(t).forEach((([t,a])=>{e[t]=a}))}))}return e},success(e){if(t)return t(e);if(e instanceof Object&&e.pk)return jQuery(this).attr("data-pk",e.pk),e;let a=document.createElement("template");a.innerHTML=e.trim();let n=a.content.querySelector(".x-editable"),i=this;i.replaceWith(n),window.dispatchEvent(new CustomEvent("xeditable:success",{detail:{subject:i}})),App.setup_xeditable(i.closest("td"))},error:e=>"application/json"===e.getResponseHeader("Content-Type")?JSON.parse(e.responseText):e.responseText})}};KTUtil.onDOMContentLoaded((()=>{App.init(document.body)})),window.CMSApp=App;