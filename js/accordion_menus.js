(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.accordionMenus = {
    attach: function (context, settings) {
      // Get data from server.
      var accordionMenus = drupalSettings.accordion_menus;
      var closedByDefault = accordionMenus.accordion_closed;
      var activeMenuTab = accordionMenus.active_tab;

      $('.accordion_menus_block_container', context).accordion({header: 'h2.accordion-title', heightStyle: 'content'});

      // Set auto height as well as default active tab.
      $.each(activeMenuTab, function (menuName, tab) {
        $('.accordion_menus_block_container.' + menuName, context).accordion({active: parseInt(tab)});
      });

      // Make collapsible by default.
      $.each(closedByDefault, function (i, val) {
        $('.accordion_menus_block_container.' + val, context).accordion({collapsible: true, active: false});
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
