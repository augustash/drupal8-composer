/**
 * Event handling.
 */
interface ExoEventInterface<T> {
  on(name:string, handler:{(data?:T):void }):void;
  off(name:string):void;
}

class ExoEvent<T> implements ExoEventInterface<T> {
  private handlers:{ [s: string]: { (data?:T):void; }; } = {};

  public on(name:string, handler:{ (data?:T):void }) :void {
    this.handlers[name] = handler;
  }

  public off(name:string):void {
    delete this.handlers[name];
  }

  public trigger(data?:T) {
    for (let name in this.handlers) {
      if (typeof this.handlers[name] !== 'undefined') {
        this.handlers[name](data);
      }
      else {
        console.log(name, this);
      }
    }
  }

  public expose() :ExoEventInterface<T> {
    return this;
  }
}
