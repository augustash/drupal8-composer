"use strict";!function(a){Drupal.behaviors.exoAutoSubmit={focused:null,submit:function(t){this.focused=t.target.getAttribute("data-drupal-selector");var e=a(t.target);e.closest(".exo-auto-submit-disable").length||e.closest("form").find("[data-exo-auto-submit-click]").first().click()},attach:function(u){var o=this,i=[16,17,18,20,33,34,35,36,37,38,39,40,9,13,27];this.focused&&setTimeout(function(){var t=a('[data-drupal-selector="'+o.focused+'"]',u);if(t.length){if(t.focus(),t.is("input:text")){var e=t.val();t.val(""),t.val(e)}o.focused=null}}),a("form[data-exo-auto-submit-full-form]",u).add("[data-exo-auto-submit]",u).filter("form, select, input:not(:text, :submit)").once("exo.auto-submit").each(function(t,e){a(e).change(function(t){a(t.target).is(":not(:text, :submit, [data-exo-auto-submit-exclude])")&&setTimeout(function(){o.submit(t)},10)})}),a("[data-exo-auto-submit-full-form] input:text, input:text[data-exo-auto-submit]",u).filter(":not([data-exo-auto-submit-exclude])").once("exo.auto-submit").each(function(t,e){var u=0;a(e).bind("keydown keyup",function(t){-1===a.inArray(t.keyCode,i)&&u&&clearTimeout(u)}).keyup(function(t){-1===a.inArray(t.keyCode,i)&&(u=setTimeout(function(){o.submit(t)},500))}).bind("change",function(t){-1===a.inArray(t.keyCode,i)&&(u=setTimeout(function(){o.submit(t)},500))})})}}}(jQuery);