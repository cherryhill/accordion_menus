
CONTENTS OF THIS FILE
---------------------

 * Author
 * Description
 * Installation
 * Dependencies
 * Configuration

AUTHOR
------
Biswajit Mondal (http://drupal.org/u/bisw)

DESCRIPTION
-----------
This module will display a Drupal menu using a jQuery UI accordion effect. The
top-level menu items are referred to as header items. The accordion effect is
invoked when the triggering event occurs on a header item. The triggering event
may be a mouse down, mouse click, or mouse over. The submenu expands to display
the menu items beneath the header. A subsequent triggering event on the same
header item collapses the menu beneath it.

INSTALLATION
------------
To use this module, install it in a modules directory. See
http://drupal.org/node/895232 for further information.

DEPENDENCIES
------------
It uses two core drupal library 'core/jquery' and 'core/jquery.ui.accordion'.
They are already added.

CONFIGURATION
-------------
This module will create accordion menu block for all menus. By default, 
no accordion menu block will be created. To act menus as accordion menu, 
configure them at
admin/config/user-interface/accordion_menu.

Configure each accordion menu as other block.
