/**
 * @file
 * This file is only used to provide typings and interfaces and is not output
 * as javascript.
 */

declare var TSinclude:any;
declare var Drupal:DrupalInterface;
declare var drupalSettings:any;
declare var displace:any;
declare var debounce:any;
declare var Modernizr:any;
declare var autosize:any;
declare var loadCSS:any;
declare var onloadCSS:any;
declare var bodyScrollLock:BodyScrollLock;
declare var noUiSlider:any;

interface JQuery {
  once(id?): JQuery;
  findOnce:any;
  imagesLoaded:any;
  overlaps:any;
}

interface BodyScrollLock {
  disableBodyScroll?: any;
  enableBodyScroll?: any;
  clearAllBodyScrollLocks?: any;
}

interface Window {
  jQuery: any;
  drupalSettings: any;
  MSStream: any;
  opera: any;
  safari: any;
  Shuffle: any;
}

interface Document {
  documentMode?: any;
}

interface DrupalInterface {
  [s: string]: any;
  Exo: Exo;
  ExoDisplace: ExoDisplace;
}

interface ExoSettingInstance {
  new(string): any
}

interface ExoSettingsGroupInterface {
  [s: string]: any;
}

interface ExoDisplaceOffsetsInterface {
  top: number;
  bottom: number;
  left: number;
  right: number;
}

interface ExoShadowOptionsInterface {
  opacity?:number;
  onClick?:Function;
}

interface ExoCallbacks {
  [s: string]: Function;
}

interface ExoConstructable<T> {
  new() : T;
}
