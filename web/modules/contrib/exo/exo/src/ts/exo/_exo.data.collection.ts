class ExoDataCollection<T> extends ExoCollection<T> {
  protected $elements:JQuery;

  public add(item:any):this {
    if (item instanceof ExoData) {
      super.add(item.getId(), item);
      this._items = this.sortKeysBy(this._items, (value:ExoData, key) => {
        return value.getWeight();
      });
    }
    return this;
  }

  public elements() {
    if (!this.$elements) {
      let selectors = [];
      for (let id in this._items) {
        selectors.push(this._items[id].getSelector());
      }
      this.$elements = jQuery(selectors.join(', '));
    }
    return this.$elements;
  }

  public getData() {
    const data = {};
    this.each(item => {
      data[item.getId()] = item.getData();
    });
    return data;
  }

  /**
   * Underscore sort object keys.
   *
   * Like _.sortBy(), but on keys instead of values, returning an object, not an
   * array. Defaults to alphanumeric sort.
   */
  protected sortKeysBy(obj:any, comparator) {
    var keys = _.sortBy(_.keys(obj), function (key) {
      return comparator ? comparator(obj[key], key) : key;
    });

    return _.object(keys, _.map(keys, function (key) {
      return obj[key];
    }));
  }
}
