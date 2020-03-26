class ExoCollection<T> {
  protected _items:any = {};

  constructor(items?:any) {
    if (items) {
      this._items = items;
    }
  }

  public add(key:string, item:any):this {
    this._items[key] = item;
    return this;
  }

  public remove(key:string):this {
    delete this._items[key];
    return this;
  }

  public each(callback:any):this {
    _.each(this.getAll(), callback);
    return this;
  }

  public count():number {
    return _.keys(this._items).length;
  }

  public has(id:any) {
    return typeof this._items[id] !== 'undefined' ? true : false;
  }

  public getAll() {
    return this._items;
  }

  public getFirst():T {
    if (!this.count()) {
      return null;
    }
    const items = this.getAll();
    return items[_.keys(items)[0]];
  }

  public getLast():T {
    if (!this.count()) {
      return null;
    }
    const items = this.getAll();
    return items[_.keys(items)[_.keys(items).length - 1]];
  }

  public getNext(key:string, loop?:boolean):T {
    const items = this.getAll();
    const keys = _.keys(items);
    const loc = keys.indexOf(key);
    if (typeof items[keys[loc + 1]] !== 'undefined') {
      return items[keys[loc + 1]];
    }
    if (loop) {
      return this.getFirst();
    }
    return null;
  }

  public getPrev(key:string, loop?:boolean):T {
    const items = this.getAll();
    const keys = _.keys(items);
    const loc = keys.indexOf(key);
    if (typeof items[keys[loc - 1]] !== 'undefined') {
      return items[keys[loc - 1]];
    }
    if (loop) {
      return this.getLast();
    }
    return null;
  }

  public getById(id:any) {
    return this.has(id) ? this._items[id] : null;
  }

  public getByDelta(delta:number) {
    let count = 0;
    const items = this.getAll();
    const keys = _.keys(items);
    if (keys[delta]) {
      return items[keys[delta]];
    }
    return null;
  }

}
