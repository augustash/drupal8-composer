/**
 * @file
 * Global eXo javascript.
 */

TSinclude('./exo/_exo.manager.ts')
TSinclude('./exo/_exo.collection.ts')
TSinclude('./exo/_exo.data.ts')
TSinclude('./exo/_exo.data.manager.ts')
TSinclude('./exo/_exo.data.collection.ts')
TSinclude('./exo/_exo.event.ts')

(function ($, _, Drupal, displace) {
  TSinclude('./exo/_exo.ts')
  TSinclude('./exo/_exo.displace.ts')

  // Maximum time allotted for loading.
  let loadTimeout = setTimeout(() => {
    Drupal.Exo.init(document.body);
  }, 1000);

  // Support loadCSS used by the advagg module.
  if (typeof loadCSS !== 'undefined') {
    Drupal.Exo.debug('log', 'Exo', 'Found loadCSS');

    // Wait till stylesheets are loaded.
    const domain = (url) => {
      return url.replace('http://', '').replace('https://', '').split('/')[0];
    }
    const $sheets = $('link[rel="stylesheet"]').filter((id, element) => {
      return location.href && element.href ? domain(location.href) === domain(element.href) : false;
    });

    if ($sheets.length) {
      Drupal.Exo.debug('log', 'Exo', 'Sheets to Load', $sheets);
      let count = 0;
      $sheets.each((id, element) => {
        const href = $(element).prop('href');
        Drupal.Exo.debug('log', 'Exo', 'Load', href);
        onloadCSS(element, function() {
          count++;
          if (count == $sheets.length) {
            clearTimeout(loadTimeout);
            Drupal.Exo.init(document.body);
          }
        });
      });
    }
    else {
      clearTimeout(loadTimeout);
      Drupal.Exo.init(document.body);
    }
  }
  else {
    clearTimeout(loadTimeout);
    Drupal.behaviors.exo = {
      attach: function(context) {
        Drupal.Exo.init(document.body);
        delete Drupal.behaviors.exo;
      }
    }
  }

  // When CSS has been loaded.
  function onloadCSS( ss, callback ) {
    var called;
    function newcb(){
        if( !called && callback ){
          called = true;
          callback.call( ss );
        }
    }
    if( ss.addEventListener ){
      ss.addEventListener( "load", newcb );
    }
    if( ss.attachEvent ){
      ss.attachEvent( "onload", newcb );
    }
    if( "isApplicationInstalled" in navigator && "onloadcssdefined" in ss ) {
      ss.onloadcssdefined( newcb );
    }
  }

})(jQuery, _, Drupal, Drupal.displace);
