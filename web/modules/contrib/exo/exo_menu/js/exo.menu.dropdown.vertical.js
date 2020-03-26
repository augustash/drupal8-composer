"use strict";function _typeof(e){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function _classCallCheck(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function _defineProperties(e,t){for(var n=0;n<t.length;n++){var o=t[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,o.key,o)}}function _createClass(e,t,n){return t&&_defineProperties(e.prototype,t),n&&_defineProperties(e,n),e}function _possibleConstructorReturn(e,t){return!t||"object"!==_typeof(t)&&"function"!=typeof t?_assertThisInitialized(e):t}function _assertThisInitialized(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function _get(e,t,n){return(_get="undefined"!=typeof Reflect&&Reflect.get?Reflect.get:function(e,t,n){var o=_superPropBase(e,t);if(o){var r=Object.getOwnPropertyDescriptor(o,t);return r.get?r.get.call(n):r.value}})(e,t,n||e)}function _superPropBase(e,t){for(;!Object.prototype.hasOwnProperty.call(e,t)&&null!==(e=_getPrototypeOf(e)););return e}function _getPrototypeOf(e){return(_getPrototypeOf=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function _inherits(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&_setPrototypeOf(e,t)}function _setPrototypeOf(e,t){return(_setPrototypeOf=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}!function(o){var e=function(){function t(){var e;return _classCallCheck(this,t),(e=_possibleConstructorReturn(this,_getPrototypeOf(t).apply(this,arguments))).defaults={itemIcon:"",transitionIn:"fadeIn",transitionOut:""},e}return _inherits(t,ExoMenuStyleBase),_createClass(t,[{key:"build",value:function(){var n=this;_get(_getPrototypeOf(t.prototype),"build",this).call(this);this.get("expandable");this.get("itemIcon")&&this.$element.find(".expanded > a").append(this.get("itemIcon")),this.$element.find(".expanded > a").on("click.exo.menu.style.dropdown",function(e){var t=o(e.currentTarget);e.preventDefault(),n.toggle(t.closest(".expanded"))})}},{key:"toggle",value:function(e,t){t=!1!==t,e.hasClass("expand")?this.hide(e,t):this.show(e,t)}},{key:"show",value:function(e,t){var n=this,o=e.find("> .exo-menu-level");t=!1!==t,o.length&&(e.addClass("expand"),t&&""!==this.get("transitionIn")&&void 0!==Drupal.Exo.animationEvent&&(o.off(Drupal.Exo.animationEvent+".exo.menu.hide"),o.removeClass("exo-animate-"+this.get("transitionOut")),o.addClass("exo-animate-"+this.get("transitionIn")),o.one(Drupal.Exo.animationEvent+".exo.menu.show",function(e){o.off(Drupal.Exo.animationEvent+".exo.menu.show"),o.removeClass("exo-animate-"+n.get("transitionIn"))})))}},{key:"hide",value:function(t,e){var n=this,o=t.find("> .exo-menu-level");e=!1!==e,o.length&&(e&&""!==this.get("transitionOut")&&void 0!==Drupal.Exo.animationEvent?(o.off(Drupal.Exo.animationEvent+".exo.menu.show"),o.removeClass("exo-animate-"+this.get("transitionIn")),o.addClass("exo-animate-"+this.get("transitionOut")),o.one(Drupal.Exo.animationEvent+".exo.menu.hide",function(e){t.removeClass("expand"),o.off(Drupal.Exo.animationEvent+".exo.menu.hide"),o.removeClass("exo-animate-"+n.get("transitionOut")),o.find(".expand").removeClass("expand")})):t.removeClass("expand"))}}]),t}();Drupal.ExoMenuStyles.dropdown_vertical=e}(jQuery);