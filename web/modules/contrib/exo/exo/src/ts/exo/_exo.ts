class Exo {
  protected label:string = 'Exo';
  protected doDebug:boolean = false;
  public $window:JQuery<Window>;
  public $document:JQuery<Document>;
  public $body:JQuery;
  public $exoBody:JQuery;
  public $exoCanvas:JQuery;
  public $exoContent:JQuery;
  protected $exoShadow:JQuery;
  protected $elementPositions:JQuery;
  protected resizeCallbacks:ExoCallbacks = {};
  protected exoShadowTimeout:number;
  protected initPromises:Array<Promise<boolean>> = [];
  protected revealPromises:Array<Promise<boolean>> = [];
  protected initialized:boolean = false;
  protected shadowUsage:number = 0;
  protected shadowCallbacks:Array<Function> = [];
  protected _isMobile:boolean = null;
  private readonly events = {
    init: new ExoEvent<void>(),
    ready: new ExoEvent<void>(),
    reveal: new ExoEvent<void>(),
    breakpoint: new ExoEvent<any>(),
  };

  public speed:number = 300; // Should be the same as speed set in _variables.scss.
  public animationEvent:string;
  public transitionEvent:string;
  public breakpoint:any = {
    min: null,
    max: null,
    name: null,
  };

  constructor() {
    this.$window = $(window);
    this.$document = $(document);
    this.$body = $('body');
    this.$exoBody = $('#exo-body');
    this.$exoCanvas = $('#exo-canvas');
    this.$exoContent = $('#exo-content');
    this.$exoShadow = $('#exo-shadow');
    this.animationEvent = this.whichAnimationEvent();
    this.transitionEvent = this.whichTransitionEvent();
    this.refreshBreakpoint();

    this.$exoShadow.on('click.exoShowShadow', e => {
      const callback = this.shadowCallbacks[this.shadowCallbacks.length - 1];
      if (callback) {
        callback(e);
      }
    });

    if (this.isFirefox()) {
      this.$body.addClass('is-firefox');
    }
    else if (this.isIE()) {
      this.$body.addClass('is-ie');
    }

    if (this.isTouch()) {
      this.$body.addClass('has-touch');
    }
    else {
      this.$body.addClass('no-touch');
    }
  }

  public init() {
    this.debug('log', this.label, 'Init');
    // Due to deferred loading of CSS, we want to wait until ready to enable
    // animations.
    // We use a timeout so other modules that act on the init event have time
    // to register their promises.
    setTimeout(() => {
      this.doInit();
    });
  }

  protected doInit() {
    // All dependencies have been met. We are not yet ready to reveal the page
    // content but any modules that need to act before content is revealed can
    // do so with this event.
    this.event('init').trigger();

    // Trigger resize event.
    this.onResize();

    // Ready will be fired after 3 seconds to matter what.
    const initTimer = setTimeout(() => {
      this.ready();
    }, 3000);

    // Once all the dependencies have finished, unleashed the init.
    this.debug('log', this.label, 'Init Promises', this.initPromises);
    Promise.all(this.initPromises).then(values => {
      clearTimeout(initTimer);
      setTimeout(() => {
        this.ready();
      });
    });

    // Resize on once all images have been loaded.
    this.$exoBody.imagesLoaded(() => {
      if (this.initialized === true) {
        this.displaceContent();
      }
      setTimeout(() => {
        if (typeof Drupal.drimage !== 'undefined') {
          Drupal.drimage.init();
        }
      });
    });

    const resizeThrottle = _.throttle(() => {
      this.onResize();
    }, 99);
    this.$window.on('resize.exo', (e) => {
      this.refreshBreakpoint();
      resizeThrottle();
    });
  }

  protected ready() {
    this.debug('log', this.label, 'Ready');
    this.initialized = true;
    this.$body.addClass('exo-ready');
    this.event('ready').trigger();
    this.$document.trigger('exoReady');
    this.displaceContent();
    this.resizeContent();
    if (window.localStorage) {
      window.localStorage.setItem('exoBodySize', JSON.stringify(displace.offsets));
      window.localStorage.setItem('exoContentHeight', String(this.$exoContent.height()));
    }

    // Once all the dependencies have finished, unleashed the init.
    this.debug('log', this.label, 'Reveal Promises', this.revealPromises);
    Promise.all(this.revealPromises).then(values => {
      this.debug('log', this.label, 'Reveal');
      this.event('reveal').trigger();
      this.$document.trigger('exoReveal');
    });
  }

  /**
   * Check if eXo has been initialized.
   */
  public isInitialized():boolean {
    return this.initialized;
  }

  /**
   * Add a dependency to eXo initialization.
   *
   * @param promise
   */
  public addInitWait(promise:Promise<boolean>) {
    this.debug('log', this.label, 'Init Wait Added');
    this.initPromises.push(promise);
  }

  /**
   * Add a dependency to eXo reveal.
   *
   * @param promise
   */
  public addRevealWait(promise:Promise<boolean>) {
    this.debug('log', this.label, 'Reveal Wait Added');
    this.revealPromises.push(promise);
  }

  /**
   * Return the #exo-body jQuery element.
   */
  public getBodyElement():JQuery {
    return this.$exoBody;
  }

  /**
   * On resize callback.
   */
  public onResize() {
    this.debug('log', this.label, 'onResize');
    Drupal.ExoDisplace.calculate();
    if (this.initialized === true) {
      this.displaceContent();
      this.resizeContent();
      for (const key in this.resizeCallbacks) {
        if (this.resizeCallbacks.hasOwnProperty(key)) {
          this.resizeCallbacks[key]();
        }
      }
    }
    this.checkElementPosition();
  }

  public addOnResize(id:string, callback:Function) {
    this.resizeCallbacks[id] = callback;
  }

  public removeOnResize(id:string) {
    if (typeof this.resizeCallbacks[id] !== 'undefined') {
      delete this.resizeCallbacks[id];
    }
  }

  /**
   * Displace content area.
   *
   * @see exo.html.twig.
   */
  public displaceContent(offsets?) {
    offsets = offsets || displace.offsets;
    this.debug('log', this.label, 'displaceContent', offsets);
    this.$exoBody.css({
      paddingTop:displace.offsets.top,
      paddingBottom:displace.offsets.bottom,
      paddingLeft:displace.offsets.left,
      paddingRight:displace.offsets.right,
    });
  }

  /**
   * Resize content area.
   */
  public resizeContent() {
    const height = this.$window.height() - (parseInt(this.$exoBody.css('paddingTop')) + parseInt(this.$exoBody.css('paddingBottom')));
    this.debug('log', this.label, 'resizeContent', height);
    this.$exoContent.css('min-height', height);
  }

  /**
   * Show content shadow.
   *
   * @return {Promise<void>}
   */
  public showShadow(options?:ExoShadowOptionsInterface):Promise<void> {
    options = _.extend({
      opacity: .8,
      onClick: null
    }, options);
    this.shadowUsage++;
    if (options.onClick) {
      this.shadowCallbacks.push(options.onClick);
    }
    return new Promise<void>((resolve, reject) => {
      clearTimeout(this.exoShadowTimeout);
      this.$exoShadow.addClass('active');
      this.exoShadowTimeout = setTimeout(() => {
        this.$exoShadow.addClass('animate').css('opacity', options.opacity);
        resolve();
      }, 20);
    });
  }

  /**
   * Hide content shadow.
   *
   * @return {Promise<void>}
   */
  public hideShadow():Promise<void> {
    return new Promise<void>((resolve, reject) => {
      this.shadowUsage--;
      this.shadowCallbacks.pop();
      if (this.shadowUsage <= 0) {
        this.shadowUsage = 0;
        this.shadowCallbacks = [];
        clearTimeout(this.exoShadowTimeout);
        this.$exoShadow.removeClass('animate').css('opacity', 0);
        this.exoShadowTimeout = setTimeout(() => {
          this.$exoShadow.removeClass('active');
          resolve();
        }, this.speed);
      }
      else {
        resolve();
      }
    });
  }

  public trackElementPosition($element:HTMLElement|JQuery, inViewportCallback?:Function, outViewportCallback?:Function) {
    if ($element instanceof HTMLElement) {
      $element = $($element);
    }
    if ($element.once('exo.track.position').length) {
      if (!this.$elementPositions) {
        this.$elementPositions = $();
        this.$window.on('scroll.exo', _.throttle(e => this.checkElementPositionOnScroll(), 99));
      }
      if (inViewportCallback) {
        $element.data('exoInViewportCallback', inViewportCallback);
      }
      if (outViewportCallback) {
        $element.data('exoOutViewportCallback', outViewportCallback);
      }
      $element.imagesLoaded(() => {
        this.$elementPositions = this.$elementPositions.add($element);
        this.checkElementPosition();
      });
    }
  }

  public untrackElementPosition($element:HTMLElement|JQuery) {
    if (this.$elementPositions && this.$elementPositions.length) {
      if ($element instanceof HTMLElement) {
        $element = $($element);
      }
      this.$elementPositions = this.$elementPositions.not($element);
    }
  }

  public checkElementPosition() {
    if (typeof this.$elementPositions !== 'undefined' && this.$elementPositions.length) {
      this.$elementPositions.each((index, element) => {
        const $element = $(element);
        let offsetTop = Math.round($element.offset().top);
        let offsetBottom = offsetTop + $element.outerHeight();
        // If out of range, load immediately.
        if (offsetTop < 0) {
          offsetTop = 0;
          offsetBottom = this.$document.height();
        }
        $element.data('exoPosition', {
          in: offsetTop,
          out: offsetBottom,
        });
      });
      this.checkElementPositionOnScroll();
    }
  }

  protected checkElementPositionOnScroll() {
    if (this.$elementPositions && this.$elementPositions.length) {
      const offsetTop = Math.round(this.$document.scrollTop() + displace.offsets.top);
      const wrapperHeight = Math.round(Drupal.Exo.$window.height() - displace.offsets.top - displace.offsets.bottom);
      const offsetBottom = offsetTop + wrapperHeight;
      this.$elementPositions.each((index, element) => {
        const $element = $(element);
        const active = $element.data('exoActive');
        const position = $element.data('exoPosition');
        if (offsetTop < position.out && offsetBottom > position.in) {
          if (!active) {
            $element.data('exoActive', true);
            const inViewportCallback = $element.data('exoInViewportCallback');
            if (typeof inViewportCallback === 'function') {
              inViewportCallback($element);
            }
          }
        }
        else if (active) {
          $element.data('exoActive', false);
          const outViewportCallback = $element.data('exoOutViewportCallback');
          if (typeof outViewportCallback === 'function') {
            outViewportCallback($element);
          }
        }
      });
    }
  }

  /**
   * Refresh breakpoint information. See _global.scss.
   */
  public refreshBreakpoint() {
    const value:any = {};
    const property:string = String(window.getComputedStyle(document.querySelector('body'), ':before').getPropertyValue('content'));
    property.split('|').forEach(section => {
      const parts = section.replace('"', '').split(':');
      value[parts[0]] = parts[1];
    });
    if (value.min !== this.breakpoint.min) {
      this.breakpoint = value;
      this.event('breakpoint').trigger(value);
    }
  }

  public lockOverflow($element?:HTMLElement|JQuery) {
    if ($element) {
      if ($element instanceof HTMLElement) {
        $element = $($element);
      }
      bodyScrollLock.disableBodyScroll($element.get(0));
    }
    else {
      $('html').addClass('exo-lock-overflow');
      if (this.isMobile()) {
        this.$body.css('overflow', 'hidden');
      }
    }
  }

  public unlockOverflow($element?:HTMLElement|JQuery) {
    if ($element) {
      if ($element instanceof HTMLElement) {
        $element = $($element);
      }
      bodyScrollLock.enableBodyScroll($element.get(0));
    }
    else {
      $('html').removeClass('exo-lock-overflow');
      if (this.isMobile()) {
        this.$body.css('overflow', '');
      }
    }
  }

  /**
   * Remove measurement unit from string.
   */
  public getMeasurementValue(value:string) {
    let separators = /%|px|em|cm|vh|vw/;
    return parseInt(String(value).split(separators)[0]);
  }

  /**
   * Remove measurement unit from string.
   */
  public getMeasurementUnit(value:string) {
    return String(value).match(/[\d.\-\+]*\s*(.*)/)[1] || '';
  }

  /**
   * Convert PX to EM.
   */
  public getPxFromEm(em:string|number) {
    em = this.getMeasurementValue(String(em));
    return em * parseFloat(getComputedStyle(document.querySelector('body'))['font-size']);
  }

  /**
   * Determine if browser is IE version.
   */
  public isIE(version?:number):boolean {
    if(version === 9){
      return navigator.appVersion.indexOf('MSIE 9.') !== -1;
    } else {
      const userAgent = navigator.userAgent;
      return userAgent.indexOf('MSIE ') > -1 || userAgent.indexOf('Trident/') > -1;
    }
  }

  public isMobile():boolean {
    if (this._isMobile === null) {
      var check = false;
      (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
      this._isMobile = check;
    }
    return this._isMobile;
  }

  /**
   * Determine if browser is Firefox version.
   */
  public isFirefox():boolean {
    return navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
  }

  /**
   * Determine if browser is iOS.
   */
  public isIos():boolean {
    return (/iPhone|iPod/.test(navigator.userAgent)) && !window.MSStream;
  }

  /**
   * Determine if browser is iPadOS.
   */
  public isIpadOs():boolean {
    return (/iPad/.test(navigator.userAgent)) && !window.MSStream;
  }

  /**
   * Determine if browser is Desktop Safari.
   */
  public isSafari():boolean {
    return window.safari !== undefined && !this.isIos() && !this.isIpadOs();
  }

  /**
   * Determine if touch enabled device.
   */
  public isTouch():boolean {
    return 'ontouchstart' in document.documentElement;
  }

  /**
   * Determine the appropriate event for CSS3 animation end.
   */
  public whichAnimationEvent(){
    let transition;
    const el = document.createElement('fakeelement');

    var transitions = {
      'animation' :'animationend',
      'OAnimation' :'oAnimationEnd',
      'MozAnimation' :'animationend',
      'WebkitAnimation' :'webkitAnimationEnd'
    }

    for (transition in transitions){
      if (el.style[transition] !== undefined){
        return transitions[transition];
      }
    }
  }

  /**
   * Generate a unique id.
   *
   * @return {string}
   *   A unique id.
   */
  public guid() {
    function s4() {
      return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
    }
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
  }

  /**
   * Determine the appropriate event for CSS3 transition end.
   */
  public whichTransitionEvent(){
    let transition;
    const el = document.createElement('fakeelement');

    var transitions = {
      'transition':'transitionend',
      'OTransition':'oTransitionEnd',
      'MozTransition':'transitionend',
      'WebkitTransition':'webkitTransitionEnd'
    }

    for (transition in transitions){
      if (el.style[transition] !== undefined){
        return transitions[transition];
      }
    }
  }

  /**
   * Get events for subscribing and triggering.
   */
  public event(type:string):ExoEvent<any> {
    if (typeof this.events[type] !== 'undefined') {
      return this.events[type].expose();
    }
    return null;
  }

  /**
   * Convert a string to a function if it exists within the global scope.
   */
  public stringToCallback(str:string):{object:object, function:string} {
    let callback:{object:object, function:string} = null;
    if (typeof str === 'string') {
      const parts:Array<string> = str.split('.');
      let scope = window;
      parts.forEach(value => {
        if (scope[value]) {
          if (typeof scope[value] === 'function' && typeof scope === 'object') {
            callback = {
              object: scope,
              function: value
            };
          }
          else {
            scope = scope[value];
          }
        }
      });
    }
    return callback;
  }

  public cleanData(data, defaults) {
    jQuery.each(data, (index, val) => {
      if (val == 'true') {
        data[index] = true;
      } else if (val == 'false') {
        data[index] = false;
      } else if ((val === '1' || val === 1) && typeof defaults[index] === 'boolean') {
        data[index] = true;
      } else if ((val === '0' || val === 0) && typeof defaults[index] === 'boolean') {
        data[index] = false;
      } else if (/^\d+$/.test(val)) {
        data[index] = parseInt(val);
      }
    });
    return data;
  }

  public toCamel(str:string) {
    return str.replace(/[-_]+([a-z])/g, function (g) { return g[1].toUpperCase(); });
  }

  public toSnake(str:string) {
    return str.replace( /([A-Z])/g, "_$1").toLowerCase();
  }

  public toDashed(str:string) {
    return str.replace( /([A-Z])/g, "-$1").toLowerCase();
  }

  /**
   * Log debug message.
   */
  public debug(type:string, label:string, ...args) {
    if (label === this.label && this.doDebug === false) {
      return;
    }
    switch (type) {
      case 'info':
        console.info('[eXo ' + label + ']', ...args); // eslint-disable-line no-console
        break;
      case 'warn':
        console.warn('[eXo ' + label + ']', ...args); // eslint-disable-line no-console
        break;
      case 'error':
        console.error('[eXo ' + label + ']', ...args); // eslint-disable-line no-console
        break;
      default:
        console.log('[eXo ' + label + ']', ...args); // eslint-disable-line no-console
        break;
    }
  }

}

Drupal.Exo = new Exo();
