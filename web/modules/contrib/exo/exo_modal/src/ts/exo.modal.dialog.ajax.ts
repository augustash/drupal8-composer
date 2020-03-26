(function ($, Drupal) {

  if (Drupal.AjaxCommands.prototype.closeDialog) {
    Drupal.AjaxCommands.prototype.closeDrupalDialog = Drupal.AjaxCommands.prototype.closeDialog;
  }
  Drupal.AjaxCommands.prototype.closeDialog = function (ajax, response, status) {
    var $dialog = $(response.selector);
    if (Drupal.AjaxCommands.prototype.closeDrupalDialog && $dialog.length && $dialog.is(':ui-dialog')) {
      Drupal.AjaxCommands.prototype.closeDrupalDialog(ajax, response, status);
    }
    else if (Drupal.ExoModal) {
      // Support eXo modals. Drupal wants to use its default modals, but when
      // eXo modals are used in it's place, many modules will still call this
      // command to close the "dialog" it thinks is open.
      Drupal.AjaxCommands.prototype.exoModalClose(ajax, response, status);
    }
  };

  if (Drupal.AjaxCommands.prototype.setDialogOption) {
    Drupal.AjaxCommands.prototype.setDrupalDialogOption = Drupal.AjaxCommands.prototype.setDialogOption;
  }
  Drupal.AjaxCommands.prototype.setDialogOption = function (ajax, response, status) {
    var $dialog = $(response.selector);
    if (Drupal.AjaxCommands.prototype.setDrupalDialogOption && $dialog.length && $dialog.is(':ui-dialog')) {
      Drupal.AjaxCommands.prototype.setDrupalDialogOption(ajax, response, status);
    }
    else if (Drupal.ExoModal) {
      // Support eXo modals. Drupal wants to use its default modals, but when
      // eXo modals are used in it's place, many modules will still call this
      // command to change the options of the "dialog" it thinks is open.
      var modals = Drupal.ExoModal.getVisible();
      if (modals.count()) {
        modals.getLast().set(response.optionName, response.optionValue);
      }
    }
  };

  Drupal.AjaxCommands.prototype.exoModalClose = function (ajax, response, status) {
    if (Drupal.ExoModal) {
      var modals = Drupal.ExoModal.getVisible();
      if (modals.count()) {
        modals.getLast().close();
      }
    }
  }

})(jQuery, Drupal);
