
Drupal.AjaxCommands.prototype.exoToolbarDialog = function(ajax, response, status){
  const itemId = response.item_id;
  const dialogItem:ExoToolbarDialogItem = Drupal.ExoToolbarDialog.getItem(itemId);
  if (dialogItem) {
    dialogItem.build(ajax, response, status);
  }
}
