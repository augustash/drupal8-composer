/**
 * @file
 * This file is only used to provide typings and interfaces and is not output
 * as javascript.
 */

declare var YT:any;
declare var Vimeo:any;

interface DrupalInterface {
  ExoVideo:ExoVideos;
  ExoVideoProviders: ExoVideoProvidersInterface;
}

interface ExoVideoProvidersInterface {
  [s: string]: any;
}

interface ExoVideoProviderInterface extends ExoData {
}

interface Window {
  onYouTubeIframeAPIReady: any;
}
