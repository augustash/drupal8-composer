"use strict";function _typeof(e){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function _classCallCheck(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function _defineProperties(e,t){for(var n=0;n<t.length;n++){var i=t[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}function _createClass(e,t,n){return t&&_defineProperties(e.prototype,t),n&&_defineProperties(e,n),e}function _possibleConstructorReturn(e,t){return!t||"object"!==_typeof(t)&&"function"!=typeof t?_assertThisInitialized(e):t}function _assertThisInitialized(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function _get(e,t,n){return(_get="undefined"!=typeof Reflect&&Reflect.get?Reflect.get:function(e,t,n){var i=_superPropBase(e,t);if(i){var o=Object.getOwnPropertyDescriptor(i,t);return o.get?o.get.call(n):o.value}})(e,t,n||e)}function _superPropBase(e,t){for(;!Object.prototype.hasOwnProperty.call(e,t)&&null!==(e=_getPrototypeOf(e)););return e}function _getPrototypeOf(e){return(_getPrototypeOf=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function _inherits(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&_setPrototypeOf(e,t)}function _setPrototypeOf(e,t){return(_setPrototypeOf=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}!function(i){var e=function(){function t(){var e;return _classCallCheck(this,t),(e=_possibleConstructorReturn(this,_getPrototypeOf(t).apply(this,arguments))).defaults={itemDelayInterval:60,width:"50%",transitionIn:"fadeIn",transitionOut:"fadeOut",itemIcon:""},e.open=!1,e}return _inherits(t,ExoMenuStyleBase),_createClass(t,[{key:"build",value:function(){var n=this;_get(_getPrototypeOf(t.prototype),"build",this).call(this),Drupal.ExoModal&&Drupal.ExoModal.getById(this.exoMenu.getId()).then(function(e){n.modal=e}),this.$element.find(".level-0 > ul > li.expanded > .exo-menu-link").on("click.exo.menu.style.mega",function(e){var t=i(e.currentTarget);e.preventDefault(),n.toggle(t.parent())}).each(function(e,t){n.get("itemIcon")&&i(t).find("> span").append(n.get("itemIcon")),t.style.webkitAnimationDelay=t.style.animationDelay=e*n.get("itemDelayInterval")+"ms"}),this.$element.find(".level-1").each(function(e,t){i(t).find(".exo-menu-link").each(function(e,t){t.style.webkitAnimationDelay=t.style.animationDelay=e*n.get("itemDelayInterval")+"ms"})}),""!==this.get("transitionIn")&&void 0!==Drupal.Exo.animationEvent&&this.$element.find(".level-0").addClass("exo-animate exo-animate-in exo-animate-"+this.get("transitionIn")),this.refresh(),this.resize(),Drupal.Exo.$document.on("drupalViewportOffsetChange.exo.menu.mega.vertical",function(e){n.resize()})}},{key:"refresh",value:function(){var t=this;this.hideActive(!1);var n=this.$element.find(".level-0 > ul > .active-trail");n.length?this.hideActive(!1).then(function(e){return t.show(n,!0)}):this.setWrapHeight(),this.resize()}},{key:"resize",value:function(){var e=Drupal.Exo.getMeasurementValue(this.get("width")),t=Drupal.Exo.getMeasurementUnit(this.get("width"))||"px",n=e,i=0;switch(this.$element.removeClass("exo-menu-can-shift"),this.$element.removeClass("exo-menu-shift"),t){case"px":!0===this.open?i=n:n+=e,n+i>Drupal.Exo.$window.width()&&(this.addBack(),this.shift(),e=n=i=100,t="%");break;case"%":!0===this.open?i=100-n:n=100}this.modal&&(this.modal.hasOpenPanel()||(!0===this.open?this.modal.setWidth(n+i+t):this.modal.setWidth(e+t))),this.$element.find(".level-0 > ul").css({width:n+t}),this.$element.find(".level-1").css({width:i+t})}},{key:"toggle",value:function(t,n){var i=this;n=!1!==n,t.hasClass("expand")?this.hide(t,n):this.hideActive(n).then(function(e){return i.show(t,n)})}},{key:"hideActive",value:function(i){var o=this;return new Promise(function(t,e){var n=o.$element.find(".level-0 > ul > .expand");n.length?o.hide(n,i).then(function(e){t(!0)}):t(!0)})}},{key:"addBack",value:function(){var t=this;this.$back||(this.$back=i('<a href="#" class="exo-menu-back">Back</a>').on("click.exo.menu.mega.vertical",function(e){e.preventDefault(),t.hideActive()}).prependTo(this.$element.find(".level-1")))}},{key:"shift",value:function(){this.$element.addClass("exo-menu-can-shift"),!0===this.open&&this.$element.addClass("exo-menu-shift")}},{key:"show",value:function(i,o){var a=this;return new Promise(function(t,e){var n=i.find("> .exo-menu-level");o=!1!==o,n.length&&(a.open=!0,a.resize(),i.addClass("expand"),o&&""!==a.get("transitionIn")&&void 0!==Drupal.Exo.animationEvent?(n.off(Drupal.Exo.animationEvent+".exo.menu.hide"),i.removeClass("exo-animate exo-animate-out"),i.addClass("exo-animate exo-animate-in"),n.removeClass("exo-animate-"+a.get("transitionOut")),n.addClass("exo-animate-"+a.get("transitionIn")),n.one(Drupal.Exo.animationEvent+".exo.menu.show",function(e){n.off(Drupal.Exo.animationEvent+".exo.menu.show"),n.removeClass("exo-animate-"+a.get("transitionIn")),t(!0)})):t(!0),a.setWrapHeight(n))})}},{key:"hide",value:function(i,o){var a=this;return new Promise(function(t,e){var n=i.find("> .exo-menu-level");a.$element.removeClass("exo-menu-shift"),o=!1!==o,n.length&&(a.open=!1,o&&""!==a.get("transitionOut")&&void 0!==Drupal.Exo.animationEvent?(n.off(Drupal.Exo.animationEvent+".exo.menu.show"),i.removeClass("exo-animate exo-animate-in"),i.addClass("exo-animate exo-animate-out"),n.removeClass("exo-animate exo-animate-in exo-animate-"+a.get("transitionIn")),n.addClass("exo-animate-"+a.get("transitionOut")),n.one(Drupal.Exo.animationEvent+".exo.menu.hide",function(e){i.removeClass("expand"),n.off(Drupal.Exo.animationEvent+".exo.menu.hide"),n.removeClass("exo-animate-"+a.get("transitionOut")),n.find(".expand").removeClass("expand"),a.setWrapHeight(n),a.resize(),t(!0)})):(i.removeClass("expand"),a.setWrapHeight(n),a.resize(),t(!0)))})}},{key:"setWrapHeight",value:function(n){var i=this;setTimeout(function(){var e=i.$element.find(".level-0").outerHeight(),t=n?n.outerHeight():0;i.$element.height(Math.max(e,t))},10)}}]),t}();Drupal.ExoMenuStyles.mega_vertical=e}(jQuery);