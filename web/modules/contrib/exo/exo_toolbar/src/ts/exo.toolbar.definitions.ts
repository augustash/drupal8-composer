/**
 * @file
 * This file is only used to provide typings and interfaces and is not output
 * as javascript.
 */

 interface DrupalInterface {
  ExoToolbar:ExoToolbars;
  ExoToolbarToggle:ExoToolbarToggle;
  ExoToolbarDialog:ExoToolbarDialog;
  ExoToolbarDialogTypes: ExoToolbarDialogTypesInterface;
}

interface ExoToolbarRegions {
  [s: string]: ExoToolbarRegion;
}

interface ExoToolbarSections {
  [s: string]: ExoToolbarSection;
}

interface ExoToolbarItems {
  [s: string]: ExoToolbarItem;
}

interface ExoToolbarItemGeometry {
  top: number;
  left: number;
  right: number;
  bottom: number;
  width: number;
  height: number;
  offsets: ExoToolbarItemPositionOffsetGeometry;
}

interface ExoToolbarItemPositionOffsetGeometry {
  top: number;
  left: number;
  right: number;
  bottom: number;
}

interface ExoToolbarDialogTypesInterface {
  [s: string]: any;
}

interface ExoToolbarDialogTypeInterface {
  build(ajax:any, response:any, status:any):Promise<void>;
  show():this;
  hide():this;
  shouldRebuild():boolean;
  useShadow():boolean;
}
