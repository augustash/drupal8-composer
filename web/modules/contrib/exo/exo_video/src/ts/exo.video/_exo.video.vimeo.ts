class ExoVideoVimeo extends ExoVideoBase {
  protected static apiState:number = 0;
  protected started:boolean = false;
  protected time:number = 0;

  protected make() {
    super.make();
    ExoVideoVimeo.getApi().then(() => {
      this.videoBuild();
    });
  }

  protected videoBuild() {
    if (typeof this.player === 'undefined') {
      const parameters = {
        id: this.get('videoId'),
        autoplay: true,
        background: true,
        loop: true,
        byline: false,
        portrait: false
      };
      this.player = new Vimeo.Player(this.getId() + '-video', parameters);
      this.player.ready().then(() => {
        this.videoReady();
      });
    }
  }

  protected videoReady() {
    super.videoReady();
    this.$video.find('iframe').css({width: '100%', height: '100%'});
    this.videoPrepare();
  }

  protected videoPrepare() {
    this.player.on('timeupdate', (e) => {
      this.time = e.seconds;
      if (this.time > .5) {
        this.player.off('timeupdate');
        this.$video.css('transform', '').hide().fadeIn();
        this.videoWatch();
      }
    });
  }

  public videoTime() {
    return this.time;
  }

  protected videoPlay() {
    return this.player.play();
  }

  protected videoPause() {
    return this.player.pause();
  }

  protected videoRewind() {
    return this.player.setCurrentTime(0);
  }

  protected videoMute() {
    return this.player.setVolume(0);
  }

  protected videoUnMute() {
    return this.player.setVolume(1);
  }

  public static getApi():Promise<void> {
    return new Promise((resolve, reject) => {
      if (ExoVideoVimeo.apiState === 0) {
        ExoVideoVimeo.apiState = 1;
        var tag = document.createElement('script');
        tag.src = 'https://player.vimeo.com/api/player.js';
        tag.onload = () => {
          ExoVideoVimeo.apiState = 2;
          resolve();
          Drupal.Exo.$document.trigger('exo-video-vimeo-ready');
        };
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
      }
      else if (ExoVideoVimeo.apiState === 1) {
        Drupal.Exo.$document.one('exo-video-vimeo-ready', function () {
          resolve();
        });
      }
      else {
        resolve();
      }
    });
  }
}

Drupal.ExoVideoProviders['vimeo'] = ExoVideoVimeo;
