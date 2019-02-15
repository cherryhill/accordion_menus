<?php

namespace Drupal\accordion_menus\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;

/**
 * Provides a accordion Menu block.
 *
 * @Block(
 *   id = "accordion_menus_block",
 *   admin_label = @Translation("Accordion Menus"),
 *   category = @Translation("Accordion Menus"),
 *   deriver = "Drupal\accordion_menus\Plugin\Derivative\AccordionMenusBlock"
 * )
 */
class AccordionMenusBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $elements = [];
    $output = [];

    $menu_name = $this->getDerivativeId();
    $menu_tree = \Drupal::menuTree();
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
    $parameters->setMinDepth(0)->onlyEnabledLinks();

    $tree = $menu_tree->load($menu_name, $parameters);
    $manipulators = array(
      array('callable' => 'menu.default_tree_manipulators:checkAccess'),
      array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
    );
    $tree = $menu_tree->transform($tree, $manipulators);

    $output['#theme'] = 'accordian_menus_block';
    $output['#attached']['library'][] = 'accordion_menus/accordion_menus_widget';
   
    foreach ($tree as $key => $menu_item) {
      if ($menu_item->hasChildren) {
        $elements[$key] = [
          'content' => $this->generateSubMenuTree($menu_item->subtree),
          'title' => $menu_item->link->getTitle()
        ];
      }
    }

    $output['#elements'] = $elements;
    return $output;
  }

  private function generateSubMenuTree($menu) {
    $output = [];
    $item_lists = [];
    foreach($menu as $item) {
      //If menu element disabled skip this branch
      if ($item->link->isEnabled()) {
        $item_lists[] = Link::fromTextAndUrl($item->link->getTitle(), $item->link->getUrlObject());
      }
    }

    $output = [
      '#theme' => 'item_list',
      '#items' => $item_lists,
    ];

    return $output;
  }

}
