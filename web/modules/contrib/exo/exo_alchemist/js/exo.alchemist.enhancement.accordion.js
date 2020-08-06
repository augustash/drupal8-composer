"use strict";function _classCallCheck(e,i){if(!(e instanceof i))throw new TypeError("Cannot call a class as a function")}function _defineProperties(e,i){for(var t=0;t<i.length;t++){var n=i[t];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function _createClass(e,i,t){return i&&_defineProperties(e.prototype,i),t&&_defineProperties(e,t),e}!function(o,d){var i=function(){function i(e){var n=this;_classCallCheck(this,i),this.id="",this.speed=5e3,this.$wrapper=e,this.id=e.data("ee--accordion-id"),this.$items=e.find('.ee--accordion-item[data-ee--accordion-id="'+this.id+'"]'),this.$triggers=e.find('.ee--accordion-trigger[data-ee--accordion-id="'+this.id+'"]'),this.$contents=e.find('.ee--accordion-content[data-ee--accordion-id="'+this.id+'"]'),this.$contents.hide(),this.isLayoutBuilder()&&(d.ExoAlchemistAdmin.lockNestedFields(this.$items),o(document).off("exoComponentFieldEditActive.exo.alchemist.enhancement.accordion").on("exoComponentFieldEditActive.exo.alchemist.enhancement.accordion",function(e,i){var t=o(i);t.hasClass("ee--accordion-item")&&(n.show(t,!1),d.ExoAlchemistAdmin.sizeFieldOverlay(t),d.ExoAlchemistAdmin.sizeTarget(t))})),this.show(this.$triggers.first(),!1),this.$triggers.on("click.exo.alchemist.enhancement.accordion",function(e){e.preventDefault(),n.show(o(e.currentTarget))})}return _createClass(i,[{key:"show",value:function(e,i){var t=this;i=void 0===i||i;var n=e.closest('.ee--accordion-item[data-ee--accordion-id="'+this.id+'"]'),o=n.find('.ee--accordion-content[data-ee--accordion-id="'+this.id+'"]');if(o.length){var s=n.hasClass("show"),c=this.$items.filter(".show"),a=c.find('.ee--accordion-content[data-ee--accordion-id="'+this.id+'"]');if(this.isLayoutBuilder()){if(s)return;d.ExoAlchemistAdmin.lockNestedFields(c)}c.removeClass("show"),i?a.slideToggle(350,"swing"):a.hide(),s||(n.addClass("show"),i?o.slideToggle(350,"swing",function(){t.isLayoutBuilder()&&d.ExoAlchemistAdmin.unlockNestedFields(n)}):(o.show(),this.isLayoutBuilder()&&d.ExoAlchemistAdmin.unlockNestedFields(n)))}}},{key:"isLayoutBuilder",value:function(){return d.ExoAlchemistAdmin&&d.ExoAlchemistAdmin.isLayoutBuilder()}}]),i}();d.behaviors.exoAlchemistEnhancementAccordion={attach:function(e){o(".ee--accordion-wrapper").once("exo.alchemist.enhancement").each(function(){new i(o(this))})}}}(jQuery,Drupal,drupalSettings);