(function ($, _, Drupal) {

  /**
   * eXo Alchemist admin behavior.
   */
  Drupal.behaviors.exoAlchemistAdminAppearance = {
    attach: function(context) {
      const submit = _.debounce(() => {
        this.submit();
      }, 200);

      $('.exo-alchemist-appearance-form')
      .find('select, input:not(:text, :submit)')
      .once('exo.alchemist')
      .each((index, element) => {
        $(element).change(e => {
          if ($(e.target).is(':not(:text, :submit)')) {
            submit();
          }
        });
      });

      $('.exo-alchemist-appearance-form[data-exo-alchemist-revert]').once('exo.alchemist.revert').each((index, element) => {
        const $modifiers = $('.exo-modifier');
        const modifierClasses = [];
        if ($('.exo-alchemist-appearance-form').closest('.exo-modal').length) {
          Drupal.Exo.$window.on('exo-modal:onClosing.alchemist.appearance', e => {
            Drupal.Exo.$window.off('exo-modal:onClosing.alchemist.appearance');
            $modifiers.each((index, element) => {
              $(element).attr('class', modifierClasses[index]);
            });
          });
        }
        $modifiers.each((index, element) => {
          modifierClasses.push($(element).attr('class'));
        });
      });

      // This causes an immediate rebuild. Which I'm not sure we want.
      // $('.exo-alchemist-appearance-form[data-exo-alchemist-refresh]').once('exo.alchemist.refresh').each((index, element) => {
      //   submit();
      // });
    },

    submit: function (e:JQueryEventObject) {
      $('#exo-alchemist-appearance-refresh').first().trigger('mousedown');
    }
  }

  Drupal.AjaxCommands.prototype.exoComponentModifierAttributes = function (ajax, response, status) {
    $('.exo-alchemist-appearance-form[data-exo-alchemist-revert] .exo-alchemist-revert-message').removeClass('hidden');
    console.log(response);
    for (const modifierName in response.argument) {
      if (response.argument.hasOwnProperty(modifierName)) {
        const attributes = response.argument[modifierName];
        const $element = $('[data-exo-alchemist-modifier="' + attributes['data-exo-alchemist-modifier'] + '"]');
        console.log($element, '[data-exo-alchemist-modifier="' + attributes['data-exo-alchemist-modifier'] + '"]');
        if (attributes.hasOwnProperty('class')) {
          $element.removeClass(function (index, css) {
            return (css.match (/(^|\s)exo-modifier--\S+/g) || []).join(' ');
          });
          $element.addClass(attributes['class'].join(' '));
          if (typeof Drupal.ExoAlchemistAdmin !== 'undefined') {
            Drupal.ExoAlchemistAdmin.watch();
            // $element.off(Drupal.Exo.transitionEvent).one(Drupal.Exo.transitionEvent, e => {
            //   Drupal.ExoAlchemistAdmin.resize();
            // })
          }
        }
      }
    }
  }

})(jQuery, _, Drupal);
