class ExoImage {
  protected isBuilt:boolean = false;
  protected supportsWebP:boolean = false;
  protected triggerResize:Function;
  protected defaults:ExoSettingsGroupInterface = {
    "ratio_distortion": 60,
    "upscale": 320,
    "downscale": 1900,
    "multiplier": 1,
    "animate": 1,
    "bg": 0,
    "visible": 1,
    "handler": 'scale',
    "ratio": {
      "width": 1,
      "height": 1,
    },
  };

  /**
   * Drupal Attach event.
   * @param context
   */
  public attach(context:HTMLElement):Promise<boolean> {
    return new Promise((resolve, reject) => {
      if (this.isBuilt === false) {
        this.build(context);
        if (drupalSettings.exoImage.defaults.webp === true) {
          this.checkSupportForWebP().then(() => {
            this.supportsWebP = true;
            this.init(context);
            resolve(true);
          }, () => {
            this.init(context);
            resolve(true);
          });
        }
        else {
          this.init(context);
          resolve(true);
        }
      }
      else {
        this.init(context);
        resolve(true);
      }
    });
  }

  public build(context) {
    this.isBuilt = true;
    Drupal.Exo.event('breakpoint').on('exo.image', data => {
      this.init();
    });

    Drupal.Exo.$window.on('resize.exo.image', _.debounce(() => {
      if (Drupal.Exo.breakpoint.name === 'xlarge') {
        this.init();
      }
    }, 200));

    this.triggerResize = _.debounce(() => {
      Drupal.Exo.$window.trigger('resize');
    }, 200);
  }

  public init(context?) {
    if (typeof context === 'undefined') {
      context = document;
    }
    var el = context.querySelectorAll('.exo-image');
    if (el.length > 0) {
      for (var i = 0; i < el.length; i++) {
        if (!this.isHidden(el[i])) {
          const data = this.fetchData(el[i]);
          if (data.visible === 1 && el[i].getAttribute('data-w') === null) {
            Drupal.Exo.trackElementPosition(el[i], $element => {
              Drupal.Exo.untrackElementPosition($element[0]);
              this.renderEl($element[0]);
            });
          }
          else {
            this.renderEl(el[i]);
          }
        }
      }
    }
  }

  protected isHidden(el:HTMLElement) {
    var style = window.getComputedStyle(el);
    return (style.display === 'none');
  }

  protected fetchData(el:HTMLElement) {
    const data = JSON.parse(el.getAttribute('data-exo-image'));
    for (const key in drupalSettings.exoImage.defaults) {
      if (drupalSettings.exoImage.defaults.hasOwnProperty(key) && typeof data[key] === 'undefined') {
        data[key] = drupalSettings.exoImage.defaults[key];
      }
    }
    for (const key in this.defaults) {
      if (this.defaults.hasOwnProperty(key) && typeof data[key] === 'undefined') {
        data[key] = this.defaults[key];
      }
    }
    if (Drupal.Exo.isIE()) {
      // Treat IE is the lame software it is.
      data.animate = 0;
      data.visible = 0;
    }
    // Images that are not visible cannot be rendered so they must
    // be handled by location tracking. When manually displaying images,
    // the Drupal.Exo.checkElementPosition() method should be used.
    if (el.offsetWidth === 0) {
      data.visible = 1;
    }
    return Drupal.Exo.cleanData(data, this.defaults);
  }

  protected renderEl(el:HTMLElement) {
    const data = this.fetchData(el);
    if (isNaN(data.fid) === false && data.fid % 1 === 0 && Number(data.fid) > 0) {
      const size = this.size(el);
      const w = Number(el.getAttribute('data-w'));
      const h = Number(el.getAttribute('data-h'));
      if (size[0] !== w || size[1] !== h) {
        if (size[0] > 0) {
          const preview = el.querySelector && el.querySelector<HTMLImageElement>('img.exo-image-preview');
          let src = '/images/' + size[0] + '/' + size[1] + '/' + data.fid + '/' + encodeURIComponent(data.filename);
          if (this.supportsWebP) {
            src = src.substr(0, src.lastIndexOf('.')) + ".webp";
          }
          el.setAttribute('data-w', size[0].toString());
          el.setAttribute('data-h', size[1].toString());
          let attempt = 0;
          const image = new Image();
          image.className = 'exo-image-reveal exo-image-actual';
          image.width = size[0];
          image.height = size[1];
          image.onload = e => {
            const currentImg = el.querySelector && el.querySelector<HTMLImageElement>('img.exo-image-actual');
            if (currentImg) {
              currentImg.parentNode.removeChild(currentImg);
              data.animate = 0;
            }
            el.appendChild(image);
            if (data.bg === 1) {
              image.style.visibility = 'hidden';
              image.classList.remove('exo-image-reveal');
              el.style.backgroundImage = 'url("' + src + '")';
              if (data.animate === 1) {
                setTimeout(() => {
                  preview.classList.add('exo-image-fadeout');
                  preview.addEventListener(Drupal.Exo.animationEvent, e => {
                    preview.parentNode.removeChild(preview);
                    preview.removeEventListener(Drupal.Exo.animationEvent, e => {});
                  });
                }, 50);
              }
              else {
                if (preview) {
                  setTimeout(() => {
                    preview.parentNode.removeChild(preview);
                  }, 50);
                }
              }
            }
            else {
              if (data.animate === 1) {
                if (preview) {
                  preview.classList.add('exo-image-float');
                }
                image.addEventListener(Drupal.Exo.animationEvent, e => {
                  if (preview) {
                    preview.parentNode.removeChild(preview);
                  }
                  image.removeEventListener(Drupal.Exo.animationEvent, e => {});
                  image.classList.remove('exo-image-reveal');
                });
              }
              else {
                if (preview) {
                  preview.parentNode.removeChild(preview);
                }
                image.classList.remove('exo-image-reveal');
              }
            }
          }
          image.onerror = e => {
            // Continue trying to load the image on failure.
            attempt++;
            if (attempt < 3) {
              image.setAttributeNS('http://www.w3.org/1999/xlink','href', src);
            }
          }
          image.src = src;
          this.triggerResize();
        }
      }
    }
  }

  protected size(el:HTMLElement) {
    if (el.offsetWidth === 0) {
      return { 0: 0, 1: 0 };
    }
    const data = this.fetchData(el);

    // Determine size in relation to max size of breakpoint.
    const max = Drupal.Exo.getPxFromEm(Drupal.Exo.getMeasurementValue(Drupal.Exo.breakpoint.max));
    const maxWidth = document.documentElement.clientWidth > max ? max : document.documentElement.clientWidth;
    const ratio = Math.round(el.offsetWidth / maxWidth * 10) / 10;
    let size = {
      0: Math.ceil(max * ratio),
      1: 0
    };

    if (Drupal.Exo.breakpoint.name === 'xlarge') {
      size[0] = Math.round(el.offsetWidth * 100) / 100;
    }

    if (size[0] === document.documentElement.clientWidth) {
      size[0] = Drupal.Exo.getPxFromEm(Drupal.Exo.breakpoint.max);
    }

    // Get the screen multiplier to deliver higher quality images.
    var multiplier = 1;
    if (data.multiplier === 1) {
      multiplier = Number(window.devicePixelRatio);
      if (isNaN(multiplier) === true || multiplier <= 0) {
        multiplier = 1;
      }
    }
    size[0] = Math.round(size[0] * multiplier);

    // Make sure the requested image isn't to small.
    if (size[0] < data.upscale) {
      size = this.resize(size, data.upscale, 0);
    }

    // Downscale the image if it is to larger.
    if (size[0] > data.downscale) {
      size = this.resize(size, data.downscale, 0);
    }

    // Force number to be divisible by 20 so ratio with thumbnail is correct.
    // @see ExoImageFormatter.
    size[0] = Math.ceil(size[0] / 20) * 20;

    // Set height for aspect ratio crop.
    if (data.handler === 'ratio') {
      size[1] = Math.round(size[0] * data.thumb_ratio);
    }
    return size;
  }

  protected resize(size, r, d) {
    if (size[d] === 0) {
      return size;
    }

    // Clone values into new array.
    var new_size = {
      0: size[0],
      1: size[1]
    };
    new_size[d] = r;

    var inverse_d = Math.abs(d - 1);
    if (size[inverse_d] === 0) {
      return new_size;
    }
    new_size[inverse_d] = Math.round(new_size[inverse_d] * (new_size[d] / size[d]));

    return new_size;
  }

  protected checkSupportForWebP(feature?:string) {
    const images = {
      basic: "data:image/webp;base64,UklGRjIAAABXRUJQVlA4ICYAAACyAgCdASoCAAEALmk0mk0iIiIiIgBoSygABc6zbAAA/v56QAAAAA==",
      lossless: "data:image/webp;base64,UklGRh4AAABXRUJQVlA4TBEAAAAvAQAAAAfQ//73v/+BiOh/AAA="
    };
    return new Promise((resolve, reject) => {
      var img = new Image();
      img.onload = function() { resolve(); };
      img.onerror = function() { reject(); };
      img.src = images[feature || 'basic'];
    });
  }
}

Drupal.ExoImage = new ExoImage();
