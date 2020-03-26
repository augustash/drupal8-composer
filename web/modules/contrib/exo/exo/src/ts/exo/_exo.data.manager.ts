class ExoDataManager<T> extends ExoManager<T> {
  protected instances:ExoDataCollection<T>;

  /**
   * Initial build.
   * @param context
   */
  public build():void {
    this.instances = new ExoDataCollection<T>();
  }

  /**
   * Add instance.
   */
  protected addInstance(id:string, instance:T) {
    this.instances.add(instance);
  }

}
