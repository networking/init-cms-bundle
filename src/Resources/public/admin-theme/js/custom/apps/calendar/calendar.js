"use strict";var KTAppCalendar=function(){var e,t,n,a,o,r,i,l,d,c,s,m,u,v,f,p,y,D,k,_,g,b,S,h,T,Y,w,L,E,M={id:"",eventName:"",eventDescription:"",eventLocation:"",startDate:"",endDate:"",allDay:!1};const x=()=>{v.innerText="Add a New Event",u.show();const o=f.querySelectorAll('[data-kt-calendar="datepicker"]'),i=f.querySelector("#kt_calendar_datepicker_allday");i.addEventListener("click",(e=>{e.target.checked?o.forEach((e=>{e.classList.add("d-none")})):(l.setDate(M.startDate,!0,"Y-m-d"),o.forEach((e=>{e.classList.remove("d-none")})))})),q(M),D.addEventListener("click",(function(o){o.preventDefault(),p&&p.validate().then((function(o){console.log("validated!"),"Valid"==o?(D.setAttribute("data-kt-indicator","on"),D.disabled=!0,setTimeout((function(){D.removeAttribute("data-kt-indicator"),Swal.fire({text:"New event added to calendar!",icon:"success",buttonsStyling:!1,confirmButtonText:"Ok, got it!",customClass:{confirmButton:"btn btn-primary"}}).then((function(o){if(o.isConfirmed){u.hide(),D.disabled=!1;let o=!1;i.checked&&(o=!0),0===c.selectedDates.length&&(o=!0);var d=moment(r.selectedDates[0]).format(),s=moment(l.selectedDates[l.selectedDates.length-1]).format();if(!o){const e=moment(r.selectedDates[0]).format("YYYY-MM-DD"),t=e;d=e+"T"+moment(c.selectedDates[0]).format("HH:mm:ss"),s=t+"T"+moment(m.selectedDates[0]).format("HH:mm:ss")}e.addEvent({id:N(),title:t.value,description:n.value,location:a.value,start:d,end:s,allDay:o}),e.render(),f.reset()}}))}),2e3)):Swal.fire({text:"Sorry, looks like there are some errors detected, please try again.",icon:"error",buttonsStyling:!1,confirmButtonText:"Ok, got it!",customClass:{confirmButton:"btn btn-primary"}})}))}))},B=()=>{L.addEventListener("click",(o=>{o.preventDefault(),w.hide(),(()=>{v.innerText="Edit an Event",u.show();const o=f.querySelectorAll('[data-kt-calendar="datepicker"]'),i=f.querySelector("#kt_calendar_datepicker_allday");i.addEventListener("click",(e=>{e.target.checked?o.forEach((e=>{e.classList.add("d-none")})):(l.setDate(M.startDate,!0,"Y-m-d"),o.forEach((e=>{e.classList.remove("d-none")})))})),q(M),D.addEventListener("click",(function(o){o.preventDefault(),p&&p.validate().then((function(o){console.log("validated!"),"Valid"==o?(D.setAttribute("data-kt-indicator","on"),D.disabled=!0,setTimeout((function(){D.removeAttribute("data-kt-indicator"),Swal.fire({text:"New event added to calendar!",icon:"success",buttonsStyling:!1,confirmButtonText:"Ok, got it!",customClass:{confirmButton:"btn btn-primary"}}).then((function(o){if(o.isConfirmed){u.hide(),D.disabled=!1,e.getEventById(M.id).remove();let o=!1;i.checked&&(o=!0),0===c.selectedDates.length&&(o=!0);var d=moment(r.selectedDates[0]).format(),s=moment(l.selectedDates[l.selectedDates.length-1]).format();if(!o){const e=moment(r.selectedDates[0]).format("YYYY-MM-DD"),t=e;d=e+"T"+moment(c.selectedDates[0]).format("HH:mm:ss"),s=t+"T"+moment(m.selectedDates[0]).format("HH:mm:ss")}e.addEvent({id:N(),title:t.value,description:n.value,location:a.value,start:d,end:s,allDay:o}),e.render(),f.reset()}}))}),2e3)):Swal.fire({text:"Sorry, looks like there are some errors detected, please try again.",icon:"error",buttonsStyling:!1,confirmButtonText:"Ok, got it!",customClass:{confirmButton:"btn btn-primary"}})}))}))})()}))},q=()=>{t.value=M.eventName?M.eventName:"",n.value=M.eventDescription?M.eventDescription:"",a.value=M.eventLocation?M.eventLocation:"",r.setDate(M.startDate,!0,"Y-m-d");const e=M.endDate?M.endDate:moment(M.startDate).format();l.setDate(e,!0,"Y-m-d");const o=f.querySelector("#kt_calendar_datepicker_allday"),i=f.querySelectorAll('[data-kt-calendar="datepicker"]');M.allDay?(o.checked=!0,i.forEach((e=>{e.classList.add("d-none")}))):(c.setDate(M.startDate,!0,"Y-m-d H:i"),m.setDate(M.endDate,!0,"Y-m-d H:i"),l.setDate(M.startDate,!0,"Y-m-d"),o.checked=!1,i.forEach((e=>{e.classList.remove("d-none")})))},C=e=>{M.id=e.id,M.eventName=e.title,M.eventDescription=e.description,M.eventLocation=e.location,M.startDate=e.startStr,M.endDate=e.endStr,M.allDay=e.allDay},N=()=>Date.now().toString()+Math.floor(1e3*Math.random()).toString();return{init:function(){const q=document.getElementById("kt_modal_add_event");f=q.querySelector("#kt_modal_add_event_form"),t=f.querySelector('[name="calendar_event_name"]'),n=f.querySelector('[name="calendar_event_description"]'),a=f.querySelector('[name="calendar_event_location"]'),o=f.querySelector("#kt_calendar_datepicker_start_date"),i=f.querySelector("#kt_calendar_datepicker_end_date"),d=f.querySelector("#kt_calendar_datepicker_start_time"),s=f.querySelector("#kt_calendar_datepicker_end_time"),y=document.querySelector('[data-kt-calendar="add"]'),D=f.querySelector("#kt_modal_add_event_submit"),k=f.querySelector("#kt_modal_add_event_cancel"),_=q.querySelector("#kt_modal_add_event_close"),v=f.querySelector('[data-kt-calendar="title"]'),u=new bootstrap.Modal(q);const A=document.getElementById("kt_modal_view_event");var H,F,O,I,R,V;w=new bootstrap.Modal(A),g=A.querySelector('[data-kt-calendar="event_name"]'),b=A.querySelector('[data-kt-calendar="all_day"]'),S=A.querySelector('[data-kt-calendar="event_description"]'),h=A.querySelector('[data-kt-calendar="event_location"]'),T=A.querySelector('[data-kt-calendar="event_start_date"]'),Y=A.querySelector('[data-kt-calendar="event_end_date"]'),L=A.querySelector("#kt_modal_view_event_edit"),E=A.querySelector("#kt_modal_view_event_delete"),H=document.getElementById("kt_calendar_app"),O=(F=moment().startOf("day")).format("YYYY-MM"),I=F.clone().subtract(1,"day").format("YYYY-MM-DD"),R=F.format("YYYY-MM-DD"),V=F.clone().add(1,"day").format("YYYY-MM-DD"),(e=new FullCalendar.Calendar(H,{headerToolbar:{left:"prev,next today",center:"title",right:"dayGridMonth,timeGridWeek,timeGridDay"},initialDate:R,navLinks:!0,selectable:!0,selectMirror:!0,select:function(e){C(e),x()},eventClick:function(e){C({id:e.event.id,title:e.event.title,description:e.event.extendedProps.description,location:e.event.extendedProps.location,startStr:e.event.startStr,endStr:e.event.endStr,allDay:e.event.allDay}),(()=>{var e,t,n;w.show(),M.allDay?(e="All Day",t=moment(M.startDate).format("Do MMM, YYYY"),n=moment(M.endDate).format("Do MMM, YYYY")):(e="",t=moment(M.startDate).format("Do MMM, YYYY - h:mm a"),n=moment(M.endDate).format("Do MMM, YYYY - h:mm a")),g.innerText=M.eventName,b.innerText=e,S.innerText=M.eventDescription?M.eventDescription:"--",h.innerText=M.eventLocation?M.eventLocation:"--",T.innerText=t,Y.innerText=n})()},editable:!0,dayMaxEvents:!0,events:[{id:N(),title:"All Day Event",start:O+"-01",end:O+"-02",description:"Toto lorem ipsum dolor sit incid idunt ut",className:"fc-event-danger fc-event-solid-warning",location:"Federation Square"},{id:N(),title:"Reporting",start:O+"-14T13:30:00",description:"Lorem ipsum dolor incid idunt ut labore",end:O+"-14T14:30:00",className:"fc-event-success",location:"Meeting Room 7.03"},{id:N(),title:"Company Trip",start:O+"-02",description:"Lorem ipsum dolor sit tempor incid",end:O+"-03",className:"fc-event-primary",location:"Seoul, Korea"},{id:N(),title:"ICT Expo 2021 - Product Release",start:O+"-03",description:"Lorem ipsum dolor sit tempor inci",end:O+"-05",className:"fc-event-light fc-event-solid-primary",location:"Melbourne Exhibition Hall"},{id:N(),title:"Dinner",start:O+"-12",description:"Lorem ipsum dolor sit amet, conse ctetur",end:O+"-13",location:"Squire's Loft"},{id:N(),title:"Repeating Event",start:O+"-09T16:00:00",end:O+"-09T17:00:00",description:"Lorem ipsum dolor sit ncididunt ut labore",className:"fc-event-danger",location:"General Area"},{id:N(),title:"Repeating Event",description:"Lorem ipsum dolor sit amet, labore",start:O+"-16T16:00:00",end:O+"-16T17:00:00",location:"General Area"},{id:N(),title:"Conference",start:I,end:V,description:"Lorem ipsum dolor eius mod tempor labore",className:"fc-event-primary",location:"Conference Hall A"},{id:N(),title:"Meeting",start:R+"T10:30:00",end:R+"T12:30:00",description:"Lorem ipsum dolor eiu idunt ut labore",location:"Meeting Room 11.06"},{id:N(),title:"Lunch",start:R+"T12:00:00",end:R+"T14:00:00",className:"fc-event-info",description:"Lorem ipsum dolor sit amet, ut labore",location:"Cafeteria"},{id:N(),title:"Meeting",start:R+"T14:30:00",end:R+"T15:30:00",className:"fc-event-warning",description:"Lorem ipsum conse ctetur adipi scing",location:"Meeting Room 11.10"},{id:N(),title:"Happy Hour",start:R+"T17:30:00",end:R+"T21:30:00",className:"fc-event-info",description:"Lorem ipsum dolor sit amet, conse ctetur",location:"The English Pub"},{id:N(),title:"Dinner",start:V+"T18:00:00",end:V+"T21:00:00",className:"fc-event-solid-danger fc-event-light",description:"Lorem ipsum dolor sit ctetur adipi scing",location:"New York Steakhouse"},{id:N(),title:"Birthday Party",start:V+"T12:00:00",end:V+"T14:00:00",className:"fc-event-primary",description:"Lorem ipsum dolor sit amet, scing",location:"The English Pub"},{id:N(),title:"Site visit",start:O+"-28",end:O+"-29",className:"fc-event-solid-info fc-event-light",description:"Lorem ipsum dolor sit amet, labore",location:"271, Spring Street"}],datesSet:function(){}})).render(),p=FormValidation.formValidation(f,{fields:{calendar_event_name:{validators:{notEmpty:{message:"Event name is required"}}},calendar_event_start_date:{validators:{notEmpty:{message:"Start date is required"}}},calendar_event_end_date:{validators:{notEmpty:{message:"End date is required"}}}},plugins:{trigger:new FormValidation.plugins.Trigger,bootstrap:new FormValidation.plugins.Bootstrap5({rowSelector:".fv-row",eleInvalidClass:"",eleValidClass:""})}}),r=flatpickr(o,{enableTime:!1,dateFormat:"Y-m-d"}),l=flatpickr(i,{enableTime:!1,dateFormat:"Y-m-d"}),c=flatpickr(d,{enableTime:!0,noCalendar:!0,dateFormat:"H:i"}),m=flatpickr(s,{enableTime:!0,noCalendar:!0,dateFormat:"H:i"}),B(),y.addEventListener("click",(e=>{M={id:"",eventName:"",eventDescription:"",startDate:new Date,endDate:new Date,allDay:!1},x()})),E.addEventListener("click",(t=>{t.preventDefault(),Swal.fire({text:"Are you sure you would like to delete this event?",icon:"warning",showCancelButton:!0,buttonsStyling:!1,confirmButtonText:"Yes, delete it!",cancelButtonText:"No, return",customClass:{confirmButton:"btn btn-primary",cancelButton:"btn btn-active-light"}}).then((function(t){t.value?(e.getEventById(M.id).remove(),w.hide()):"cancel"===t.dismiss&&Swal.fire({text:"Your event was not deleted!.",icon:"error",buttonsStyling:!1,confirmButtonText:"Ok, got it!",customClass:{confirmButton:"btn btn-primary"}})}))})),k.addEventListener("click",(function(e){e.preventDefault(),Swal.fire({text:"Are you sure you would like to cancel?",icon:"warning",showCancelButton:!0,buttonsStyling:!1,confirmButtonText:"Yes, cancel it!",cancelButtonText:"No, return",customClass:{confirmButton:"btn btn-primary",cancelButton:"btn btn-active-light"}}).then((function(e){e.value?(f.reset(),u.hide()):"cancel"===e.dismiss&&Swal.fire({text:"Your form has not been cancelled!.",icon:"error",buttonsStyling:!1,confirmButtonText:"Ok, got it!",customClass:{confirmButton:"btn btn-primary"}})}))})),_.addEventListener("click",(function(e){e.preventDefault(),Swal.fire({text:"Are you sure you would like to cancel?",icon:"warning",showCancelButton:!0,buttonsStyling:!1,confirmButtonText:"Yes, cancel it!",cancelButtonText:"No, return",customClass:{confirmButton:"btn btn-primary",cancelButton:"btn btn-active-light"}}).then((function(e){e.value?(f.reset(),u.hide()):"cancel"===e.dismiss&&Swal.fire({text:"Your form has not been cancelled!.",icon:"error",buttonsStyling:!1,confirmButtonText:"Ok, got it!",customClass:{confirmButton:"btn btn-primary"}})}))})),(e=>{e.addEventListener("hidden.bs.modal",(e=>{p&&p.resetForm(!0)}))})(q)}}}();KTUtil.onDOMContentLoaded((function(){KTAppCalendar.init()}));