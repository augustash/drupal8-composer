"use strict";!function(t,e,a){e.behaviors.exoLayoutBuilder={attach:function(e){t(e).find(".exo-layout-builder").once("exo-layout-builder").each(function(e,o){var n=t(o);t(document).on("drupalViewportOffsetChange.exo-layout-builder",function(e){n.find(".exo-layout-builder-top").css({paddingTop:a.offsets.top})})}),1<t(".exo-content .messages.warning").length&&t(".exo-content .messages.warning").eq(1).hide()}}}(jQuery,Drupal,Drupal.displace);