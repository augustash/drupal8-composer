!function(a,e,r,o){"use strict";r.Ajax.prototype.beforeSend=function(e,s){if(this.$form){s.extraData=s.extraData||{},s.extraData.ajax_iframe_upload="1";var t=a.fieldValue(this.element);null!==t&&(s.extraData[this.element.name]=t)}if(a(this.element).prop("disabled",!0),this.progress&&this.progress.type){"throbber"===this.progress.type&&o.exoLoader.alwaysFullscreen&&(this.progress.type="fullscreen");var r="setProgressIndicator"+this.progress.type.slice(0,1).toUpperCase()+this.progress.type.slice(1).toLowerCase();r in this&&"function"==typeof this[r]&&this[r].call(this)}},r.Ajax.prototype.setProgressIndicatorThrobber=function(){var e=this;this.progress.element=a('<div class="ajax-progress ajax-progress-throbber"><div class="ajax-loader">'+o.exoLoader.markup+"</div></div>"),this.progress.message&&!o.exoLoader.hideAjaxMessage&&this.progress.element.find(".ajax-loader").after('<div class="message">'+this.progress.message+"</div>"),a(this.element).after(this.progress.element),setTimeout(function(){e.progress.element.addClass("active")},10)},r.Ajax.prototype.setProgressIndicatorFullscreen=function(){var e=this;this.progress.element=a('<div class="ajax-progress ajax-progress-fullscreen">'+o.exoLoader.markup+"</div>"),a(o.exoLoader.throbberPosition).after(this.progress.element),setTimeout(function(){e.progress.element.addClass("active")},10)},r.Ajax.prototype.successOriginal=r.Ajax.prototype.success,r.Ajax.prototype.success=function(e,s){var t=this;this.progress.element&&this.progress.element.hasClass("active")?(this.progress.element.one(r.Exo.transitionEvent,function(){r.Ajax.prototype.successOriginal.call(t,e,s)}),this.progress.element.removeClass("active")):r.Ajax.prototype.successOriginal.call(this,e,s)}}(jQuery,0,Drupal,drupalSettings);