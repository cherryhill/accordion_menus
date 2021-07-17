<?php

namespace Drupal\accordion_menus\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure accordion menus settings.
 */
class AccordionMenusConfigForm extends ConfigFormBase {

  /**
   * Config settings.
   */
  const SETTINGS = 'accordion_menus.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'accordion_menus_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    // Get list of menus.
    $menus = menu_ui_get_menus();
    $form['accordion_menus'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Accordion Menus'),
      '#options' => $menus,
      '#description' => $this->t('Select menu to make them accordion menu.'),
      '#default_value' => !empty($config->get('accordion_menus')) ? $config->get('accordion_menus') : [],
    ];

    $form['accordion_advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced settings'),
      '#open' => FALSE,
    ];

    $form['accordion_advanced']['accordion_menus_no_submenus'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Accordion without sub menu item'),
      '#options' => $menus,
      '#description' => $this->t('Menus which have no sub menu item, will show also in accordion menu.'),
      '#default_value' => !empty($config->get('accordion_menus_no_submenus')) ? $config->get('accordion_menus_no_submenus') : [],
    ];
    $form['accordion_advanced']['accordion_menus_default_closed'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Accordion menu closed by default'),
      '#options' => $menus,
      '#description' => $this->t('Allow all trees closed at beginning.'),
      '#default_value' => !empty($config->get('accordion_menus_default_closed')) ? $config->get('accordion_menus_default_closed') : [],
    ];
    $form['accordion_advanced']['default_tab'] = [
      '#type' => 'details',
      '#title' => $this->t('Default tab'),
      '#description' => $this->t('Consider all first level menu item and value will start from 0. Example for first menu item, it will be 0, for second it will be 1 and so on.'),
      '#open' => TRUE,
    ];

    foreach ($menus as $menu_name => $menu) {
      $form['accordion_advanced']['default_tab']['accordion_menus_active_tab_' . $menu_name] = [
        '#type' => 'textfield',
        '#title' => $this->t($menu . ': Active menu tab'),
        '#attributes' => ['type' => 'number'],
        '#maxlength' => 3,
        '#default_value' => !empty($config->get('accordion_menus_active_tab_' . $menu_name)) ? $config->get('accordion_menus_active_tab_' . $menu_name) : 0,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration and Set the submitted configuration setting.
    $output = $this->configFactory->getEditable(static::SETTINGS)
      ->set('accordion_menus', $form_state->getValue('accordion_menus'))
      ->set('accordion_menus_no_submenus', $form_state->getValue('accordion_menus_no_submenus'))
      ->set('accordion_menus_default_closed', $form_state->getValue('accordion_menus_default_closed'));

    $menus = menu_ui_get_menus();
    $active = [];
    foreach ($menus as $menu_name => $menu) {
      $output->set('accordion_menus_active_tab_' . $menu_name, $form_state->getValue('accordion_menus_active_tab_' . $menu_name));
      $active[$menu_name] = $form_state->getValue('accordion_menus_active_tab_' . $menu_name);
    }

    $output->set('accordion_menus_active_tab', $active);
    $output->save();

    parent::submitForm($form, $form_state);

    // Clear cache is needed to effect this value on block derivetive plugin
    // system. See @src/Plugin/Derivative/AccordionMenusBlock.
    drupal_flush_all_caches();
  }

}
