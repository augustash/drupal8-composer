/**
 * Manager for data plugins.
 */
class ExoData {
  protected label:string = 'ExoData';
  protected doDebug:boolean = false;
  protected id:string;
  protected dataOriginal:ExoSettingsGroupInterface = {};
  protected data:ExoSettingsGroupInterface = {};
  protected defaults = {};
  protected events = {};

  constructor(id:string) {
    this.id = id;
  }

  /**
   * Will return the changed data and store all data in this.data.
   *
   * @param onlyChanged
   * Can be used to prevent only the differences between this call and last from
   * being passed on.
   */
  public build(data, onlyChanged?:boolean):Promise<ExoSettingsGroupInterface> {
    onlyChanged = onlyChanged !== false;
    return new Promise((resolve, reject) => {
      data = jQuery.extend(true, {}, data);
      this.debug('log', 'Build: Start', '[' + this.id + ']', data);
      if (_.isEqual(data, this.dataOriginal)) {
        this.debug('warn', 'Build: Skip (same data)', '[' + this.id + ']', data, this.dataOriginal);
        reject();
      }
      else {
        const dataOriginal = jQuery.extend(true, {}, data);
        if (onlyChanged) {
          // We only use the difference between the new call and the last call.
          // This allows only new data to be processed.
          data = this.difference(data, this.dataOriginal);
        }
        // We always merge in the defaults to ensure have reliable data.
        // We use jQuery instead of underscore as it has a deep clone.
        data = jQuery.extend(true, {}, this.defaults, data);
        // Clean the data to make sure we have proper variable casting.
        data = Drupal.Exo.cleanData(data, this.defaults);
        this.data = jQuery.extend(true, {}, this.data, data);
        this.dataOriginal = dataOriginal;
        this.debug('info', 'Build: Finish', '[' + this.id + ']', this.data);
        resolve(data);
      }
    });
  }

  public afterBuild() {}

  public get(key):any {
    return typeof this.data[key] !== 'undefined' ? this.data[key] : null;
  }

  public set(key, value):any {
    this.data[key] = value;
    return this;
  }

  public getId():string {
    return this.id;
  }

  public getData() {
    return this.data;
  }

  public getWeight():number {
    return this.get('weight');
  }

  public setWeight(weight:number):this {
    this.set('weight', weight);
    return this;
  }

  public event(type:string):ExoEvent<any> {
    if (typeof this.events[type] !== 'undefined') {
      return this.events[type].expose();
    }
    return null;
  }

  /**
   * Deep diff between two object, using underscore
   * @param  {Object} object Object compared
   * @param  {Object} base   Object to compare with
   * @return {Object}        Return a new object who represent the diff
   */
  public difference(object, base) {
    const changes = (object, base) => (
      _.pick(
        _.mapObject(object, (value, key) => (
          (!_.isEqual(value, base[key])) ?
            ((_.isObject(value) && _.isObject(base[key])) ? changes(value, base[key]) : value) :
            null
        )),
        (value) => (value !== null)
      )
    );
    return changes(object, base);
  }

  /**
   * Log debug message.
   */
  public debug(type:string, ...args) {
    if (this.doDebug === true) {
      Drupal.Exo.debug(type, this.label, ...args);
    }
  }
}
