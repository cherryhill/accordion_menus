<?php

namespace Drupal\accordion_menus\Plugin\Block;

use Drupal\Core\Link;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  /**
   * Constructs a new AccordionMenuBlock.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   The menu tree service.
   */
  public function __construct(MenuLinkTreeInterface $menu_tree) {
    $this->menuTree = $menu_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('menu.link_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $elements = [];
    $output = [];

    $menu_name = $this->getDerivativeId();
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);
    $parameters->setMinDepth(0)->onlyEnabledLinks();

    $tree = $this->menuTree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuTree->transform($tree, $manipulators);

    $output['#theme'] = 'accordian_menus_block';
    $output['#attached']['library'][] = 'accordion_menus/accordion_menus_widget';

    foreach ($tree as $key => $menu_item) {
      if ($menu_item->hasChildren) {
        $elements[$key] = [
          'content' => $this->generateSubMenuTree($menu_item->subtree),
          'title' => $menu_item->link->getTitle(),
        ];
      }
    }

    $output['#elements'] = $elements;
    return $output;
  }

  /**
   * Generate submenu output.
   */
  private function generateSubMenuTree($menu) {
    $output = [];
    $item_lists = [];
    foreach ($menu as $item) {
      // If menu element disabled skip this branch.
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
