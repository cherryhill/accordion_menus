<?php

namespace Drupal\accordion_menus\Plugin\Block;

use Drupal\Core\Link;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
class AccordionMenusBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  /**
   * Constructs a new AccordionMenuBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   The menu tree service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MenuLinkTreeInterface $menu_tree) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->menuTree = $menu_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu.link_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $items = [];
    $menu_name = $this->getDerivativeId();
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);
    $parameters->setMinDepth(0)->onlyEnabledLinks();

    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];

    $tree = $this->menuTree->load($menu_name, $parameters);
    $tree = $this->menuTree->transform($tree, $manipulators);

    // Get accordion configuration.
    $config = \Drupal::config('accordion_menus.settings');
    $closed_by_default = array_filter($config->get('accordion_menus_default_closed'));
    $no_submenu = $config->get('accordion_menus_no_submenus');
    $without_submenu = in_array($menu_name, $no_submenu, TRUE) ? TRUE : FALSE;

    foreach ($tree as $key => $item) {
      $link = $item->link;

      // Only render accessible links.
      if ($this->isAccordionMenusLinkInaccessible($item)) {
        continue;
      }

      if ($item->subtree) {
        $items[$key] = [
          'content' => $this->generateSubMenuTree($item->subtree),
          'title' => $link->getTitle(),
        ];
      } elseif ($without_submenu) {
        $items[$key] = [
          'content' => [
            '#theme' => 'item_list',
            '#items' => [Link::fromTextAndUrl($link->getTitle(), $link->getUrlObject())],
          ],
          'title' => $link->getTitle(),
        ];
      }
    }

    return [
      '#theme' => 'accordian_menus_block',
      '#elements' => ['menu_name' => $menu_name, 'items' => $items],
      '#attached' => [
        'library' => ['accordion_menus/accordion_menus_widget'],
        'drupalSettings' => ['accordion_menus' => ['accordion_closed' => $closed_by_default]],
      ],
    ];
  }

  /**
   * Generate submenu output.
   */
  public function generateSubMenuTree($sub_menus) {
    $items = [];
    foreach ($sub_menus as $sub_item) {
      // Only render accessible links.
      if ($this->isAccordionMenusLinkInaccessible($sub_item)) {
        continue;
      }

      $items[] = Link::fromTextAndUrl($sub_item->link->getTitle(), $sub_item->link->getUrlObject());
    }

    return [
      '#theme' => 'item_list',
      '#items' => $items,
    ];
  }

  public function isAccordionMenusLinkInaccessible($item) {
    if (!$item->link->isEnabled()
      || ($item->access !== NULL && !$item->access instanceof AccessResultInterface)
      || ($item->access instanceof AccessResultInterface && !$item->access->isAllowed())) {
      return TRUE;
    }

    return FALSE;
  }


}
