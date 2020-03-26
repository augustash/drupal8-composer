(function ($, Drupal) {

  'use strict';

  let $scope:JQuery;
  let timeout:number;

  Drupal.behaviors.exoForm = {
    attach: function (context) {
      $scope = $('form.exo-form:visible');
      // Container inline has been replaced with exo-form-inline.
      $(context).find('.exo-form .container-inline').removeClass('container-inline');
      $(context).find('.exo-form .form--inline').removeClass('form--inline').addClass('exo-form-inline');
      $(context).find('.exo-form-container-hide').each(function () {
        if ($(this).text().trim().length) {
          $(this).removeClass('exo-form-container-hide');
        }
      });

      $(context).find('td .dropbutton-wrapper').once('exo.form.td.dropbutton').each((index, element) => {
        setTimeout(() => {
          $(element).css('min-width', $(element).outerWidth());
        });
      }).parent().addClass('exo-form-table-compact');

      $(context).find('table').once('exo.form.td.dropbutton').each((index, element) => {
        const $table = $(element);
        if ($table.outerWidth() > $table.parent().outerWidth() + 2) {
          $table.wrap('<div class="exo-form-table-overflow" />');
        }
      })
      $(context).find('td > .exo-icon').once('exo.form.td.dropbutton').each((index, element) => {
        const $td = $(element).parent();
        if ($td.children(':not(.exo-icon-label)').length === 1) {
          $td.addClass('exo-form-table-compact');
        }
      });

      $(context).find('.exo-form .exo-form-inline').each((index, element) => {
        const $children = $(element).find('> .exo-form-element-js');
        if ($children.length > 6) {
          $(element).addClass('exo-form-inline-stack');
        }
      });

      $scope.filter('.exo-form-wrap').each((index, element) => {
        if ($(element).html().trim()[0] !== '<') {
          $(element).addClass('exo-form-wrap-pad');
        }
      });

      $(context).find('.exo-form-vertical-tabs .vertical-tabs__menu-item a, .exo-form-horizontal-tabs .horizontal-tab-button a, .exo-form-element-type-details summary, .exo-form-container summary').once('exo.form.vertical-tabs').on('click', function () {
        Drupal.behaviors.exoForm.processForm($(this).closest('.exo-form'));
      });

      $(context).find('.webform-tabs').once('exo.form.refresh').each(function (e) {
        $(this).addClass('horizontal-tabs').wrap('<div class="exo-form-horizontal-tabs exo-form-element exo-form-element-js" />');
        $(this).find('.item-list ul').addClass('horizontal-tabs-list').find('> li').addClass('horizontal-tab-button');
        $(this).find('> .webform-tab').addClass('horizontal-tabs-pane').wrapAll('<div class="horizontal-tabs-panes" />');
      }).on('tabsbeforeactivate', function (e, ui) {
        ui.oldPanel.hide();
        ui.newPanel.show();
      });

      $scope.once('exo.form.watch').on('click', e => {
        Drupal.behaviors.exoForm.processForm();
      }).each((index, element) => {
        const $localscope = $(element);
        // Support utilization of a parent which defines an exo theme.
        const $parentTheme = $localscope.closest('[data-exo-theme]');
        if ($parentTheme.length) {
          $localscope.removeClass(function (index, className) {
            return (className.match (/(^|\s)exo-form-theme-\S+/g) || []).join(' ');
          }).addClass('exo-form-theme-' + $parentTheme.data('exo-theme'));
        }
      });

      // Process each request.
      $scope.each(() => {
        Drupal.behaviors.exoForm.processForm();
      });

      if (typeof Drupal.ExoModal !== 'undefined') {
        // eXo Forms that exist within modals are not visible by the time
        // behaviors are bound. So we make sure to watch for opened and then
        // bind.
        Drupal.ExoModal.event('opening').on('exo.form', (modal:ExoModal) => {
          let $form = modal.getElement().find('.exo-form');
          if (!$form.length) {
            // Sometimes the modal is within the actual form.
            $form = modal.getElement().closest('.exo-form');
          }
          if ($form.length) {
            setTimeout(function () {
              Drupal.behaviors.exoForm.attach(modal.getElement()[0]);
            });
          }
        });
      }
    },

    processForm: function ($context:JQuery) {
      clearTimeout(timeout);
      const isMobile = Drupal.Exo.isMobile();
      if (isMobile) {
        const viewport = $('meta[name="viewport"]');
        if (viewport.length) {
          const content = viewport.attr('content');
          viewport.data('exo-viewport', content);
          viewport.attr('content', content + ', maximum-scale=1');
        }
      }
      timeout = setTimeout(() => {
        $context = $context || $scope;

        $context.find('.exo-form-hide').removeClass('exo-form-hide');
        $context.find('.exo-form-element-js > .exo-form-element-inner').each((index, element) => {
          if ($(element).closest('.exo-form-hide-exclude').length) {
            return;
          }
          if (!$(element).find('> *:visible').length) {
            $(element).parent().addClass('exo-form-hide');
          }
        });
        $context.find('.exo-form-element-js:not(.messages)').each((index, element) => {
          if ($(element).closest('.exo-form-hide-exclude').length) {
            return;
          }
          if ($(element).children().length == 0) {
            $(element).addClass('exo-form-hide');
          }
          else if ($(element).innerHeight() === 0) {
            $(element).addClass('exo-form-hide');
          }
        });

        // This has issues with layout builder.
        // $context.find('.visually-hidden').each((index, element) => {
        //   if ($(element).parent().parent().hasClass('exo-form-element-js')) {
        //     $(element).parent().parent().addClass('exo-form-hide');
        //   }
        // });

        $('.exo-form-element-first', $context).removeClass('exo-form-element-first');
        $('.exo-form-element-last', $context).removeClass('exo-form-element-last');
        $('.fieldset-wrapper, .details-wrapper, .form-wrapper, .exo-modal-content', $context).add($context).each(function () {
          const $context = $(this);
          const $firstVisibleChild = $context.find('> .exo-form-element-js:not(.exo-form-element-first-exclude):visible:first');
          const $lastVisibleChild = $context.find('> .exo-form-element-js:not(.exo-form-element-last-exclude):visible:last');
          if ($firstVisibleChild.length && $context.html().trim()[0] === '<') {
            $firstVisibleChild.addClass('exo-form-element-first');
          }
          if ($lastVisibleChild.length) {
            $lastVisibleChild.last().addClass('exo-form-element-last');
          }
        });

        if (isMobile) {
          const viewport = $('meta[name="viewport"]');
          if (viewport.length) {
            viewport.attr('content', viewport.data('exo-viewport'));
          }
        }
      });
    },
  };

  Drupal.Exo.$document.on('state:disabled state:required state:visible state:checked state:collapsed', e => {
    Drupal.behaviors.exoForm.processForm();
  });

})(jQuery, Drupal);
