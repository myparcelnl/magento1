mypajQuery = jQueryIWD;
(function(){var e;("undefined"==typeof e||null===e)&&(e=mypajQuery),e(document).ready(function(){var a,n,i,t,p,r,l,s,o,d,m,c,y,u,v,f,h,g,w,b,k,_,C,x,S,B,H,I,T,N,L,M,z,J,O,D,j,E,F,G,W,P,Q;return a=function(a){return e(document.getElementById("myparcel").shadowRoot).find(a)},null==window.mypa&&(window.mypa={}),window.mypa.initialize=function(){var e,a,n;return e=document.getElementById("myparcel"),null!=e?(n=e.createShadowRoot(),n.innerHTML=document.getElementById("myparcel-template").innerHTML,null!=(a=WebComponents.ShadowCSS)?a.shimStyling(n,"myparcel"):void 0):void 0},window.mypa.initialize(),null==(g=window.mypa).fn&&(g.fn={}),null==(w=window.mypa).settings&&(w.settings={}),null==(b=window.mypa.settings).price&&(b.price={}),null==(k=window.mypa.settings).text&&(k.text={}),null==(_=window.mypa.settings).base_url&&(_.base_url="//localhost:8080/api/delivery_options"),l="disabled",o="Handtekening voor ontvangst",n="Alleen geadresseerde",c="NL",i=1,d="morning",r="default",s="night",u="pickup",v="pickup_express",h={morning:"morning",standaard:"default",avond:"night"},t=["monday","tuesday","wednesday","thursday","friday","saturday","sunday"],p=["ma","di","wo","do","vr","za","zo"],m="08:30:00",y="16:00:00",I={},I[""+m]="morning",I[""+y]="normal",f=I,x=function(){return a(".mypa-tab-container").toggleClass("mypa-slider-pos-1").toggleClass("mypa-slider-pos-0")},window.mypa.fn.updatePage=S=function(n,t,p){var r,l,s,o,d,m;o=window.mypa.settings.price;for(l in o)if(r=o[l],"string"!=typeof r&&"function"!=typeof r)throw"Price needs to be of type string";return d=window.mypa.settings,m=d.base_url,null==t&&(t=d.number),null==n&&(n=d.postal_code),null==p&&(p=d.street),null==p&&null==n&&null==t?(a("#mypa-no-options").html("Geen adres opgegeven"),void a(".mypa-overlay").removeClass("mypa-hidden")):(a("#mypa-no-options").html("Bezig met laden..."),a(".mypa-overlay").removeClass("mypa-hidden"),a(".mypa-location").html(p+" "+t),s={url:m,data:{cc:c,carrier:i,number:t,postal_code:n,delivery_time:null!=d.delivery_time?d.delivery_time:void 0,delivery_date:null!=d.delivery_date?d.delivery_date:void 0,cutoff_time:null!=d.cutoff_time?d.cutoff_time:void 0,dropoff_days:null!=d.dropoff_days?d.dropoff_days:void 0,dropoff_delay:null!=d.dropoff_delay?d.dropoff_delay:void 0,deliverydays_window:null!=d.deliverydays_window?d.deliverydays_window:void 0,exlude_delivery_type:null!=d.exclude_delivery_type?d.exclude_delivery_type:void 0},success:O},e.ajax(s))},O=function(e){return"No results"===e.data.message?(a("#mypa-no-options").html("Geen bezorgopties gevonden voor het opgegevn adres."),void a(".mypa-overlay").removeClass("mypa-hidden")):(a(".mypa-overlay").addClass("mypa-hidden"),a("#mypa-delivery-option-check").bind("click",function(){return z(a("input[name=date]:checked").val())}),M(e.data.delivery),L(e.data.pickup),a("#mypa-delivery-options-title").on("click",function(){var e;return e=a("input[name=date]:checked").val(),z(e),Q()}),a("#mypa-pickup-options-title").on("click",function(){return a("#mypa-pickup").prop("checked",!0),Q()}),Q())},L=function(e){var n,i,t,p,r,l,s,o,d,c,h;if(e.length<1)return void a("#mypa-pickup-row").addClass("mypa-hidden");for(a("#mypa-pickup-row").removeClass("mypa-hidden"),d=window.mypa.settings.price[u],s=window.mypa.settings.price[v],a(".mypa-pickup-price").html(d),a(".mypa-pickup-price").toggleClass("mypa-hidden",null==d),a(".mypa-pickup-express-price").html(s),a(".mypa-pickup-express-price").toggleClass("mypa-hidden",null==s),window.mypa.pickupFiltered=n={},e=W(e),i=0,p=e.length;p>i;i++)for(o=e[i],c=o.time,t=0,r=c.length;r>t;t++)h=c[t],null==n[l=f[h.start]]&&(n[l]=[]),n[f[h.start]].push(o);return null==n[f[m]]&&a("#mypa-pickup-express").parent().css({display:"none"}),E("#mypa-pickup-address",n[f[y]][0]),E("#mypa-pickup-express-address",n[f[m]][0]),a("#mypa-pickup-address").off().bind("click",D),a("#mypa-pickup-express-address").off().bind("click",J)},W=function(e){return e.sort(function(e,a){return parseInt(e.distance)-parseInt(a.distance)})},E=function(e,n){var i;return i=" - "+n.location+", "+n.street+" "+n.number,a(e).html(i),a(e).parent().find("input").val(JSON.stringify(n))},D=function(){return j(window.mypa.pickupFiltered[f[y]]),a(".mypa-location-time").html("- Vanaf 16.00 uur"),a("#mypa-pickup").prop("checked",!0),!1},J=function(){return j(window.mypa.pickupFiltered[f[m]]),a(".mypa-location-time").html("- Vanaf 08.30 uur"),a("#mypa-pickup-express").prop("checked",!0),!1},j=function(e){var n,i,t,r,l,s,o,d,m,c,y,u,v;for(x(),a(".mypa-onoffswitch-checkbox:checked").prop("checked",!1),C(),a("#mypa-location-container").html(""),r=t=0,y=e.length-1;y>=0?y>=t:t>=y;r=y>=0?++t:--t){for(d=e[r],c=N(d.opening_hours),m="",n=l=0;6>=l;n=++l){for(m+="<div>\n  <div class='mypa-day-of-the-week'>\n    "+p[n]+":\n  </div>\n  <div class='mypa-opening-hours-list'>",u=c[n],s=0,o=u.length;o>s;s++)v=u[s],m+="<div>"+v+"</div>";c[n].length<1&&(m+="<div><i>Gesloten</i></div>"),m+="</div></div>"}i="<div for='mypa-pickup-location-"+r+'\' class="mypa-row-lg">\n  <input id="mypa-pickup-location-'+r+'" type="radio" name="mypa-pickup-option" value=\''+JSON.stringify(d)+"'>\n  <label for='mypa-pickup-location-"+r+'\' class=\'mypa-row-title\'>\n    <div class="mypa-checkmark mypa-main">\n      <div class="mypa-circle"></div>\n      <div class="mypa-checkmark-stem"></div>\n      <div class="mypa-checkmark-kick"></div>\n    </div>\n    <span class="mypa-highlight mypa-inline-block">'+d.location+", <b class='mypa-inline-block'>"+d.street+" "+d.number+"</b>,\n    <i class='mypa-inline-block'>"+String(Math.round(d.distance/100)/10).replace(".",",")+" Km</i></span>\n  </label>\n  <i class='mypa-info'>\n  </i>\n  <div class='mypa-opening-hours'>\n    "+m+"\n  </div>\n</div>",a("#mypa-location-container").append(i)}return a("input[name=mypa-pickup-option]").bind("click",function(e){var n,i;return x(),n=JSON.parse(a(e.currentTarget).val()),i="#"+a("input[name=mypa-delivery-time]:checked").parent().find("span.mypa-address").attr("id"),E(i,n)})},N=function(e){var a,n,i,p;for(a=[],i=0,p=t.length;p>i;i++)n=t[i],a.push(e[n]);return a},M=function(e){var n,i,t,p,r,l,s,o,d;if(e.length<1)return void a("mypa-delivery-row").addClass("mypa-hidden");for(a("mypa-delivery-row").removeClass("mypa-hidden"),e.sort(T),r=window.mypa.sortedDeliverytimes={},n=a("#mypa-tabs").html(""),a("#mypa-delivery-options-container").width(),o=0,s=0,d=e.length;d>s;s++)p=e[s],r[p.date]=p.time,t=moment(p.date),l='<input type="radio" id="mypa-date-'+o+'" class="mypa-date" name="date" checked value="'+p.date+"\">\n<label for='mypa-date-"+o+"' class='mypa-tab active'>\n  <span class='day-of-the-week'>"+t.format("dddd")+"</span>\n  <br>\n  <span class='date'>"+t.format("DD MMMM")+"</span>\n</label>",n.append(l),o++;return i=a(".mypa-tab"),i.length>0&&(i.bind("click",P),i[0].click()),n.width(105*e.length),H()},P=function(e){var n;if(a("#mypa-delivery-option-check").prop("checked")===!0)return n=a("#"+a(e.currentTarget).prop("for"))[0].value,z(n)},z=function(e){var i,t,p,r,m,c,y,u,v,f,g,w,b,k,_;for(a("#mypa-delivery-options").html(""),m="",r=window.mypa.sortedDeliverytimes[e],v=0,u=0,g=r.length;g>u;u++)_=r[u],"avond"===_.price_comment&&(_.price_comment=s),k=window.mypa.settings.price[h[_.price_comment]],f={date:e,time:[_]},i="","standard"===_.price_comment&&(i="checked"),m+='<label for="mypa-time-'+v+"\" class='mypa-row-subitem'>\n  <input id='mypa-time-"+v+'\' type="radio" name="mypa-delivery-time" value=\''+JSON.stringify(f)+"' "+i+'>\n  <label for="mypa-time-'+v+'" class="mypa-checkmark">\n    <div class="mypa-circle mypa-circle-checked"></div>\n    <div class="mypa-checkmark-stem"></div>\n    <div class="mypa-checkmark-kick"></div>\n  </label>\n  <span class="mypa-highlight">'+moment(_.start,"HH:mm:SS").format("H.mm")+" - "+moment(_.end,"HH:mm:SS").format("H.mm")+" uur</span>",null!=k&&(m+="<span class='mypa-price'>"+k+"</span>"),m+="</label>",v++;return c=window.mypa.settings.price.signed,y=window.mypa.settings.text.signed,null==y&&(y=o),w=window.mypa.settings.price.only_recipient,b=window.mypa.settings.text.only_recipient,null==b&&(b=n),t=window.mypa.settings.price.combi_options,p="disabled"!==w&&"disabled"!==c&&null!=t,p&&(m+="<div class='mypa-combination-price'><span class='mypa-price mypa-hidden'>"+t+"</span>"),w!==l&&(m+='<label for="mypa-only-recipient" class=\'mypa-row-subitem\'>\n  <input type="checkbox" name="mypa-only-recipient" class="mypa-onoffswitch-checkbox" id="mypa-only-recipient">\n  <div class="mypa-switch-container">\n    <div class="mypa-onoffswitch">\n      <label class="mypa-onoffswitch-label" for="mypa-only-recipient">\n        <span class="mypa-onoffswitch-inner"></span>\n       <span class="mypa-onoffswitch-switch"></span>\n      </label>\n    </div>\n  </div>\n  <span>'+b,null!=w&&(m+="<span class='mypa-price'>"+w+"</span>"),m+="</span></label>"),c!==l&&(m+='<label for="mypa-signed" class=\'mypa-row-subitem\'>\n  <input type="checkbox" name="mypa-signed" class="mypa-onoffswitch-checkbox" id="mypa-signed">\n  <div class="mypa-switch-container">\n    <div class="mypa-onoffswitch">\n      <label class="mypa-onoffswitch-label" for="mypa-signed">\n        <span class="mypa-onoffswitch-inner"></span>\n      <span class="mypa-onoffswitch-switch"></span>\n      </label>\n    </div>\n  </div>\n  <span>'+y,c&&(m+="<span class='mypa-price'>"+c+"</span>"),m+="</span></label>"),p&&(m+="</div>"),a("#mypa-delivery-options").html(m),a(".mypa-combination-price input").on("change",C),a("#mypa-delivery-options .mypa-row-subitem input[name=mypa-delivery-time]").on("change",function(e){var n;return n=JSON.parse(a(e.currentTarget).val()).time[0].price_comment,n===d||n===s?(a("input#mypa-only-recipient").prop("checked",!0).prop("disabled",!0),a("label[for=mypa-only-recipient] span.mypa-price").html("incl.")):(w=window.mypa.settings.price.only_recipient,a("input#mypa-only-recipient").prop("disabled",!1),a("label[for=mypa-only-recipient] span.mypa-price").html(w)),C()}),a("input[name=mypa-delivery-time]:checked").length<1?a(a("input[name=mypa-delivery-time]")[0]).prop("checked",!0):void 0},C=function(){var e,n,i,t;return t=a("#mypa-delivery-options .mypa-row-subitem input[name=mypa-delivery-time]:checked").val(),null!=t&&(n=JSON.parse(t).time[0].price_comment),i=n===d||n===s,e=a("input[name=mypa-only-recipient]").prop("checked")&&a("input[name=mypa-signed]").prop("checked")&&!i,a(".mypa-combination-price").toggleClass("mypa-combination-price-active",e),a(".mypa-combination-price > .mypa-price").toggleClass("mypa-price-active",e),a(".mypa-combination-price > .mypa-price").toggleClass("mypa-hidden",!e),a(".mypa-combination-price label .mypa-price").toggleClass("mypa-hidden",e)},H=function(){var e;return e=window.mypa.slider={},e.barLength=a("#mypa-tabs-container").outerWidth(),e.bars=a("#mypa-tabs").outerWidth()/e.barLength,e.currentBar=0,a("#mypa-date-slider-right").removeClass("mypa-slider-disabled"),a("#mypa-date-slider-left").unbind().bind("click",F),a("#mypa-date-slider-right").unbind().bind("click",G)},F=function(e){var n,i,t;if(t=window.mypa.slider,1===t.currentBar)a(e.currentTarget).addClass("mypa-slider-disabled");else{if(t.currentBar<1)return!1;a(e.currentTarget).removeClass("mypa-slider-disabled")}return a("#mypa-date-slider-right").removeClass("mypa-slider-disabled"),t.currentBar--,n=a("#mypa-tabs"),i=t.currentBar*t.barLength*-1,i=104*parseInt(i/104),n.css({left:i})},G=function(e){var n,i,t;if(t=window.mypa.slider,parseInt(t.currentBar)===parseInt(t.bars-1))a(e.currentTarget).addClass("mypa-slider-disabled");else{if(t.currentBar>=t.bars-1)return!1;a(e.currentTarget).removeClass("mypa-slider-disabled")}return a("#mypa-date-slider-left").removeClass("mypa-slider-disabled"),t.currentBar++,n=a("#mypa-tabs"),i=t.currentBar*t.barLength*-1,i=104*parseInt(i/104),n.css({left:i})},T=function(e,a){var n,i,t;return n=moment(e.date),i=moment(a.date),t=moment.max(n,i),t===n?1:-1},Q=function(){var n;return n=a("input[name=mypa-delivery-time]:checked").val(),e("#mypa-input").val(n)},B=function(){return moment.locale(c),S(),null},jQuery(document).ready(B)})}).call(this);