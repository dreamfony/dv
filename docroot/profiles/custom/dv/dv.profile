<?php
/**
 * @file
 * Enables modules and site configuration for a social site installation.
 */

use Drupal\user\Entity\User;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_install_tasks().
 */
function dv_install_tasks(&$install_state) {
  $tasks = array(
    'social_install_profile_modules' => array(
      'display_name' => t('Install DV modules'),
      'type' => 'batch',
    ),
  );
  return $tasks;
}


/**
 * Installs required modules via a batch process.
 *
 * @param $install_state
 *   An array of information about the current installation state.
 *
 * @return
 *   The batch definition.
 */
function social_install_profile_modules(&$install_state) {

  $files = system_rebuild_module_data();

  $modules = array(
    'social_core' => 'social_core',
    'social_user' => 'social_user',
    'social_profile' => 'social_profile',
    'social_page' => 'social_page',
  );
  $social_modules = $modules;
  // Always install required modules first. Respect the dependencies between
  // the modules.
  $required = array();
  $non_required = array();

  // Add modules that other modules depend on.
  foreach ($modules as $module) {
    if ($files[$module]->requires) {
      $module_requires = array_keys($files[$module]->requires);
      // Remove the social modules from required modules.
      $module_requires = array_diff_key($module_requires, $social_modules);
      $modules = array_merge($modules, $module_requires);
    }
  }
  $modules = array_unique($modules);
  // Remove the social modules from to install modules.
  $modules = array_diff_key($modules, $social_modules);
  foreach ($modules as $module) {
    if (!empty($files[$module]->info['required'])) {
      $required[$module] = $files[$module]->sort;
    }
    else {
      $non_required[$module] = $files[$module]->sort;
    }
  }
  arsort($required);

  $operations = array();
  foreach ($required + $non_required + $social_modules as $module => $weight) {
    $operations[] = array('_social_install_module_batch', array(array($module), $module));
  }

  $batch = array(
    'operations' => $operations,
    'title' => t('Install Open Social modules'),
    'error_message' => t('The installation has encountered an error.'),
  );
  return $batch;
}


/**
 * Implements callback_batch_operation().
 *
 * Performs batch installation of modules.
 */
function _social_install_module_batch($module, $module_name, &$context) {
  set_time_limit(0);
  \Drupal::service('module_installer')->install($module, $dependencies = TRUE);
  $context['results'][] = $module;
  $context['message'] = t('Install %module_name module.', array('%module_name' => $module_name));
}

/**
 * Implements callback_batch_operation().
 *
 * Performs batch uninstallation of modules.
 */
function _social_uninstall_module_batch($module, $module_name, &$context) {
  set_time_limit(0);
  \Drupal::service('module_installer')->uninstall($module, $dependencies = FALSE);
  $context['results'][] = $module;
  $context['message'] = t('Uninstalled %module_name module.', array('%module_name' => $module_name));
}
