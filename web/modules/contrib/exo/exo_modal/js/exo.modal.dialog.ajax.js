"use strict";!function(i,p){p.AjaxCommands.prototype.closeDialog&&(p.AjaxCommands.prototype.closeDrupalDialog=p.AjaxCommands.prototype.closeDialog),p.AjaxCommands.prototype.closeDialog=function(o,a,t){var e=i(a.selector);p.AjaxCommands.prototype.closeDrupalDialog&&e.length&&e.is(":ui-dialog")?p.AjaxCommands.prototype.closeDrupalDialog(o,a,t):p.ExoModal&&p.AjaxCommands.prototype.exoModalClose(o,a,t)},p.AjaxCommands.prototype.setDialogOption&&(p.AjaxCommands.prototype.setDrupalDialogOption=p.AjaxCommands.prototype.setDialogOption),p.AjaxCommands.prototype.setDialogOption=function(o,a,t){var e=i(a.selector);if(p.AjaxCommands.prototype.setDrupalDialogOption&&e.length&&e.is(":ui-dialog"))p.AjaxCommands.prototype.setDrupalDialogOption(o,a,t);else if(p.ExoModal){var l=p.ExoModal.getVisible();l.count()&&l.getLast().set(a.optionName,a.optionValue)}},p.AjaxCommands.prototype.exoModalClose=function(o,a,t){if(p.ExoModal){var e=p.ExoModal.getVisible();if(e.count()){var l=e.getLast();i(window).trigger("dialog:beforeclose",[l,l.getElement()]),l.close(),i(window).trigger("dialog:afterclose",[l,l.getElement()])}}}}(jQuery,Drupal);