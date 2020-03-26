
declare var CKEDITOR:any;

(function (Drupal, debounce, CKEDITOR, $, displace, AjaxCommands) {

  Drupal.ckeditor.openDialog = function openDialog(editor, url, existingValues, saveCallback, dialogSettings) {
    var $target = $(editor.container.$);
    if (editor.elementMode === CKEDITOR.ELEMENT_MODE_REPLACE) {
      $target = $target.find('.cke_contents');
    }

    $target.css('position', 'relative').find('.ckeditor-dialog-loading').remove();

    var classes = dialogSettings.dialogClass ? dialogSettings.dialogClass.split(' ') : [];
    dialogSettings.class = classes.join(' ');

    var $content = $('<div class="ckeditor-dialog-loading"><span style="top: -40px;" class="ckeditor-dialog-loading-link">' + Drupal.t('Loading...') + '</span></div>');
    $content.appendTo($target);

    var ckeditorAjaxDialog = Drupal.ajax({
      dialog: dialogSettings,
      dialogType: 'exo_modal',
      selector: '.ckeditor-dialog-loading-link',
      url: url,
      progress: { type: 'throbber' },
      submit: {
        editor_object: existingValues
      }
    });
    ckeditorAjaxDialog.execute();

    window.setTimeout(function () {
      $content.find('span').animate({ top: '0px' });
    }, 1000);

    Drupal.ckeditor.saveCallback = saveCallback;
  }

  $(window).on('exo-modal:afterRender', function (e, modal) {
    $('.ui-dialog--narrow').css('zIndex', CKEDITOR.config.baseFloatZIndex + 1);
  });

  $(window).on('exo-modal:onOpening', function (e, modal) {
    $('.ckeditor-dialog-loading').animate({ top: '-40px' }, function () {
      $(this).remove();
    });
  });

  $(window).on('exo-modal:onClosed', function (e, modal) {
    if (Drupal.ckeditor.saveCallback) {
      Drupal.ckeditor.saveCallback = null;
    }
  });

})(Drupal, Drupal.debounce, CKEDITOR, jQuery, Drupal.displace, Drupal.AjaxCommands);
