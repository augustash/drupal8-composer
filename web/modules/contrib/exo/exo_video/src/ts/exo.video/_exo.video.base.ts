class ExoVideoBase extends ExoData implements ExoVideoProviderInterface {
  protected defaults: {
    position: 'absolute',
    zIndex: '-1',
    videoRatio: false,
    loop: true,
    autoplay: true,
    mute: false,
    mp4: false,
    webm: false,
    ogg: false,
    provider: 'image',
    videoId: null,
    image: false,
    when: 'always', // always || hover || viewport
    sizing: 'fill', // fill || adjust
    start: 0
  };
  protected $wrapper:JQuery;
  protected $videoWrapper:JQuery;
  protected $video:JQuery;
  protected player:any;
  protected ready:boolean = false;

  constructor(id:string, $wrapper:JQuery) {
    super(id);
    this.$wrapper = $wrapper;
  }

  public build(data):Promise<ExoSettingsGroupInterface> {
    return new Promise((resolve, reject) => {
      super.build(data).then(data => {
        if (data !== null) {
          this.setInnerWrapper();
          this.make();
        }
        resolve(data);
      }, reject);
    });
  }

  protected make() {
    this.$video = jQuery('<div id="' + this.getId() + '-video" class="exo-video-bg" style="transform: translate(-100%, 0); pointer-events:none;"></div>').appendTo(this.$videoWrapper).css({
      position: 'absolute'
    });
  }

  protected setInnerWrapper():void {
    this.$videoWrapper = jQuery('<div class="exo-video-bg-wrapper"></div>').appendTo(this.$wrapper).css({
      zIndex: this.get('zIndex'),
      position: this.get('position'),
      top: 0,
      left: 0,
      right: 0,
      bottom: 0,
      overflow: 'hidden'
    });
    this.makeImageBackground();

    // Track element position to make sure video is resized correctly due to
    // images that control size that might not be loaded.
    Drupal.Exo.trackElementPosition(this.$wrapper, $element => {
      setTimeout(() => {
        this.videoResize();
      });
    });
  }

  protected makeImageBackground():void {
    if (this.get('image')) {
      var parameters = {
        backgroundImage: 'url(' + this.get('image') + ')',
        backgroundSize: 'cover'
      };
      this.$videoWrapper.css(parameters);
    }
  }

  public getWrapper():JQuery {
    return this.$wrapper;
  }

  protected videoReady() {
    Drupal.ExoVideo.onReady(this);
    this.ready = true;
    this.$video = jQuery('#' + this.getId() + '-video');

    if (this.get('mute')) {
      this.videoMute();
    }

    this.videoResizeBind();
  }

  protected videoResizeBind() {
    if (this.get('videoRatio') !== false) {
      Drupal.Exo.$window.on('resize.video-bg', {}, Drupal.debounce(e => {
        this.videoResize();
      }, 100));
      this.videoResize();
    }
  }

  protected videoResize() {
    var w = this.$videoWrapper.width();
    var h = this.$videoWrapper.height();

    var width = w;
    var height = w / this.get('videoRatio');

    if (height < h) {
      height = h;
      width = h * this.get('videoRatio');
    }

    // Round
    height = Math.ceil(height);
    width = Math.ceil(width);

    // Adjust
    var top = Math.round(h / 2 - height / 2);
    var left = Math.round(w / 2 - width / 2);

    var parameters = {
      width: width + 'px',
      height: height + 'px',
      top: top + 'px',
      left: left + 'px'
    };

    this.$video.css(parameters);
  }

  protected videoWatch() {
    if (!this.get('autoplay')) {
      this.videoPause();
    }
    switch (this.get('when')) {
      case 'hover':
        this.videoPause();
        this.videoHoverBind();
        break;

      case 'viewport':
        this.videoPause();
        setTimeout(() => {
          this.videoViewportBind();
        }, 10);
        break;
    }
  }

  protected videoHoverBind() {
    this.$videoWrapper.on('mouseenter.exo.video', e => {
      this.videoPlay();
    }).on('mouseleave.exo.video', e => {
      this.videoPause();
    });
  }

  protected videoViewportBind() {
    Drupal.Exo.trackElementPosition(this.$videoWrapper, () => {
      this.videoPlay();
    }, () => {
      this.videoPause();
    });
  }

  public videoTime() { }

  protected videoPlay() {}

  protected videoPause() {}

  protected videoRewind() {}

  protected videoMute() {}

  protected videoUnMute() {}

}
