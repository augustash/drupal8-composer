/**
 * @file
 * This file is only used to provide typings and interfaces and is not output
 * as javascript.
 */

interface DrupalInterface {
  ExoMenu:ExoMenus;
  ExoMenuStyles: ExoMenuStylesInterface;
}

interface ExoMenuStylesInterface {
  [s: string]: any;
}

interface ExoMenuStyleInterface {
  build():void;
  refresh():void;
  getDefaults():ExoSettingsGroupInterface;
  get(key:string):any;
  getElement():JQuery;
}

interface ExoMenuSlideStorage {
  [s: string]: ExoMenuSlideStorageItem;
}

interface ExoMenuSlideStorageItem {
  $menuEl?: JQuery,
  $menuItems?: JQuery,
  backIdx?: string,
  name?: string,
}
