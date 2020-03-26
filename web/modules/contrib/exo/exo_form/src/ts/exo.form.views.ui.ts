(function ($, Drupal) {

  'use strict';

  Drupal.viewsUi.Checkboxifier = function (button) {
    this.$button = $(button);
    this.$parent = this.$button.closest('div.views-expose, div.views-grouped');
    this.$input = this.$parent.find('input:checkbox, input:radio');

    this.$button.hide();
    this.$parent.find('.exposed-description, .grouped-description').hide();

    this.$input.on('click.exo.form.views', () => {
      this.$button.trigger('click').trigger('submit');
    });
  };

})(jQuery, Drupal);
