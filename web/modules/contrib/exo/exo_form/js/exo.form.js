"use strict";!function(r,n){var t,o;n.behaviors.exoForm={attach:function(e){if(t=r("form.exo-form:visible"),r(e).find(".exo-form .container-inline").removeClass("container-inline"),r(e).find(".exo-form .form--inline").removeClass("form--inline").addClass("exo-form-inline"),r(e).find(".exo-form-container-hide").each(function(){r(this).text().trim().length&&r(this).removeClass("exo-form-container-hide")}),r(e).find("td .dropbutton-wrapper").once("exo.form.td.dropbutton").each(function(e,o){setTimeout(function(){r(o).css("min-width",r(o).outerWidth())})}).parent().addClass("exo-form-table-compact"),r(e).find("table").once("exo.form.td.dropbutton").each(function(e,o){var t=r(o);t.outerWidth()>t.parent().outerWidth()+2&&t.wrap('<div class="exo-form-table-overflow" />')}),r(e).find("td > .exo-icon").once("exo.form.td.dropbutton").each(function(e,o){var t=r(o).parent();1===t.children(":not(.exo-icon-label)").length&&t.addClass("exo-form-table-compact")}),t.filter(".exo-form-wrap").each(function(e,o){"<"!==r(o).html().trim()[0]&&r(o).addClass("exo-form-wrap-pad")}),r(e).find(".exo-form-vertical-tabs .vertical-tabs__menu-item a, .exo-form-horizontal-tabs .horizontal-tab-button a, .exo-form-element-type-details summary, .exo-form-container summary").once("exo.form.vertical-tabs").on("click",function(){n.behaviors.exoForm.processForm(r(this).closest(".exo-form"))}),r(e).find(".webform-tabs").once("exo.form.refresh").each(function(e){r(this).addClass("horizontal-tabs").wrap('<div class="exo-form-horizontal-tabs exo-form-element exo-form-element-js" />'),r(this).find(".item-list ul").addClass("horizontal-tabs-list").find("> li").addClass("horizontal-tab-button"),r(this).find("> .webform-tab").addClass("horizontal-tabs-pane").wrapAll('<div class="horizontal-tabs-panes" />')}).on("tabsbeforeactivate",function(e,o){o.oldPanel.hide(),o.newPanel.show()}),t.once("exo.form.watch").on("click",function(e){n.behaviors.exoForm.processForm(),setTimeout(function(){n.behaviors.exoForm.processForm()},2e3)}).each(function(e,o){var t=r(o),n=t.closest("[data-exo-theme]");n.length&&t.removeClass(function(e,o){return(o.match(/(^|\s)exo-form-theme-\S+/g)||[]).join(" ")}).addClass("exo-form-theme-"+n.data("exo-theme"))}),t.each(function(){n.behaviors.exoForm.processForm()}),void 0!==n.ExoModal&&n.ExoModal.event("opening").on("exo.form",function(e){var o=e.getElement().find(".exo-form");o.length||(o=e.getElement().closest(".exo-form")),o.length&&setTimeout(function(){n.behaviors.exoForm.attach(e.getElement()[0])})}),!this.once){var o=function(){t.find(".exo-form-inline").each(function(e,o){var t=r(o);t.removeClass("exo-form-inline-stack"),t.outerWidth()>n.Exo.$window.width()&&t.addClass("exo-form-inline-stack")})};this.once=!0,n.Exo.addOnResize("exo.form.core",o),o()}},processForm:function(e){clearTimeout(o),o=setTimeout(function(){(e=e||t).find(".exo-form-hide").removeClass("exo-form-hide"),e.find(".exo-form-element-js > .exo-form-element-inner").each(function(e,o){r(o).closest(".exo-form-hide-exclude").length||r(o).find("> *:visible").length||r(o).parent().addClass("exo-form-hide")}),e.find(".exo-form-element-js:not(.messages)").each(function(e,o){r(o).closest(".exo-form-hide-exclude").length||(0==r(o).children().length?r(o).addClass("exo-form-hide"):0===r(o).innerHeight()&&r(o).addClass("exo-form-hide"))}),r(".exo-form-element-first",e).removeClass("exo-form-element-first"),r(".exo-form-element-last",e).removeClass("exo-form-element-last"),r(".fieldset-wrapper, .details-wrapper, .form-wrapper, .exo-modal-content",e).add(e).each(function(){var e=r(this),o=e.find("> .exo-form-element-js:not(.exo-form-element-first-exclude):visible:first"),t=e.find("> .exo-form-element-js:not(.exo-form-element-last-exclude):visible:last");o.length&&"<"===e.html().trim()[0]&&o.addClass("exo-form-element-first"),t.length&&t.last().addClass("exo-form-element-last")})})}},n.Exo.$document.on("state:disabled state:required state:visible state:checked state:collapsed",function(e){n.behaviors.exoForm.processForm()})}(jQuery,Drupal);