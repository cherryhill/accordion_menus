(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.accordionMenus = {
    attach: function (context, settings) {
      // Get data from server.
      var accordionMenus = drupalSettings.accordion_menus;
      var closedByDefault = accordionMenus.accordion_closed;

      // Set auto height.
      $('.accordion_menus_block_container').accordion({header: 'h2.accordion-title', autoHeight: true});

      // Make collapsible by default.
      $.each(closedByDefault, function(i, val) {
        $('.accordion_menus_block_container.' + val).accordion({ header: 'h2.accordion-title', collapsible: true, active: false });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
