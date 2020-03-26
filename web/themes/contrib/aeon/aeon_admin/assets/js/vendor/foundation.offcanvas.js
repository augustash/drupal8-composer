"use strict";function _classCallCheck(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}var _createClass=function(){function e(e,t){for(var i=0;i<t.length;i++){var s=t[i];s.enumerable=s.enumerable||!1,s.configurable=!0,"value"in s&&(s.writable=!0),Object.defineProperty(e,s.key,s)}}return function(t,i,s){return i&&e(t.prototype,i),s&&e(t,s),t}}();!function(e){var t=function(){function t(i,s){_classCallCheck(this,t),this.$element=i,this.options=e.extend({},t.defaults,this.$element.data(),s),this.$lastTrigger=e(),this.$triggers=e(),this._init(),this._events(),Foundation.registerPlugin(this,"OffCanvas"),Foundation.Keyboard.register("OffCanvas",{ESCAPE:"close"})}return _createClass(t,[{key:"_init",value:function(){var t=this.$element.attr("id");if(this.$element.attr("aria-hidden","true"),this.$triggers=e(document).find('[data-open="'+t+'"], [data-close="'+t+'"], [data-toggle="'+t+'"]').attr("aria-expanded","false").attr("aria-controls",t),this.options.closeOnClick)if(e(".js-off-canvas-exit").length)this.$exiter=e(".js-off-canvas-exit");else{var i=document.createElement("div");i.setAttribute("class","js-off-canvas-exit"),e("[data-off-canvas-content]").append(i),this.$exiter=e(i)}this.options.isRevealed=this.options.isRevealed||new RegExp(this.options.revealClass,"g").test(this.$element[0].className),this.options.isRevealed&&(this.options.revealOn=this.options.revealOn||this.$element[0].className.match(/(reveal-for-medium|reveal-for-large)/g)[0].split("-")[2],this._setMQChecker()),this.options.transitionTime||(this.options.transitionTime=1e3*parseFloat(window.getComputedStyle(e("[data-off-canvas-wrapper]")[0]).transitionDuration))}},{key:"_events",value:function(){this.$element.off(".zf.trigger .zf.offcanvas").on({"open.zf.trigger":this.open.bind(this),"close.zf.trigger":this.close.bind(this),"toggle.zf.trigger":this.toggle.bind(this),"keydown.zf.offcanvas":this._handleKeyboard.bind(this)}),this.options.closeOnClick&&this.$exiter.length&&this.$exiter.on({"click.zf.offcanvas":this.close.bind(this)})}},{key:"_setMQChecker",value:function(){var t=this;e(window).on("changed.zf.mediaquery",function(){Foundation.MediaQuery.atLeast(t.options.revealOn)?t.reveal(!0):t.reveal(!1)}).one("load.zf.offcanvas",function(){Foundation.MediaQuery.atLeast(t.options.revealOn)&&t.reveal(!0)})}},{key:"reveal",value:function(e){var t=this.$element.find("[data-close]");e?(this.close(),this.isRevealed=!0,this.$element.off("open.zf.trigger toggle.zf.trigger"),t.length&&t.hide()):(this.isRevealed=!1,this.$element.on({"open.zf.trigger":this.open.bind(this),"toggle.zf.trigger":this.toggle.bind(this)}),t.length&&t.show())}},{key:"open",value:function(t,i){if(!this.$element.hasClass("is-open")&&!this.isRevealed){var s=this;e(document.body);this.options.forceTop&&e("body").scrollTop(0);var n=e("[data-off-canvas-wrapper]");n.addClass("is-off-canvas-open is-open-"+s.options.position),s.$element.addClass("is-open"),this.$triggers.attr("aria-expanded","true"),this.$element.attr("aria-hidden","false").trigger("opened.zf.offcanvas"),this.options.closeOnClick&&this.$exiter.addClass("is-visible"),i&&(this.$lastTrigger=i),this.options.autoFocus&&n.one(Foundation.transitionend(n),function(){s.$element.hasClass("is-open")&&(s.$element.attr("tabindex","-1"),s.$element.focus())}),this.options.trapFocus&&n.one(Foundation.transitionend(n),function(){s.$element.hasClass("is-open")&&(s.$element.attr("tabindex","-1"),s.trapFocus())})}}},{key:"_trapFocus",value:function(){var e=Foundation.Keyboard.findFocusable(this.$element),t=e.eq(0),i=e.eq(-1);e.off(".zf.offcanvas").on("keydown.zf.offcanvas",function(e){var s=Foundation.Keyboard.parseKey(e);"TAB"===s&&e.target===i[0]&&(e.preventDefault(),t.focus()),"SHIFT_TAB"===s&&e.target===t[0]&&(e.preventDefault(),i.focus())})}},{key:"close",value:function(t){if(this.$element.hasClass("is-open")&&!this.isRevealed){var i=this;e("[data-off-canvas-wrapper]").removeClass("is-off-canvas-open is-open-"+i.options.position),i.$element.removeClass("is-open"),this.$element.attr("aria-hidden","true").trigger("closed.zf.offcanvas"),this.options.closeOnClick&&this.$exiter.removeClass("is-visible"),this.$triggers.attr("aria-expanded","false"),this.options.trapFocus&&e("[data-off-canvas-content]").removeAttr("tabindex")}}},{key:"toggle",value:function(e,t){this.$element.hasClass("is-open")?this.close(e,t):this.open(e,t)}},{key:"_handleKeyboard",value:function(e){var t=this;Foundation.Keyboard.handleKey(e,"OffCanvas",{close:function(){return t.close(),t.$lastTrigger.focus(),!0},handled:function(){e.stopPropagation(),e.preventDefault()}})}},{key:"destroy",value:function(){this.close(),this.$element.off(".zf.trigger .zf.offcanvas"),this.$exiter.off(".zf.offcanvas"),Foundation.unregisterPlugin(this)}}]),t}();t.defaults={closeOnClick:!0,transitionTime:0,position:"left",forceTop:!0,isRevealed:!1,revealOn:null,autoFocus:!0,revealClass:"reveal-for-",trapFocus:!1},Foundation.plugin(t,"OffCanvas")}(jQuery);