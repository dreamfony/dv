<?php

namespace Drupal\dmt_core;

use Drupal\Core\Session\AccountInterface;

class PersonaAccountUtility {

  /**
   * {@inheritdoc}
   */
  public static function fromUser(AccountInterface $account) {
      return $account->personas->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public static function rolesFromUserPersonas(AccountInterface $account) {
    $personas = PersonaAccountUtility::fromUser($account);
    /* @var \Drupal\personas\PersonaInterface[] $personas */
    return array_values(array_reduce($personas, function ($roles, $persona) {
      $roles = array_merge($roles, $persona->getRoles());
      return $roles;
    }, []));
  }

  /**
   * {@inheritdoc}
   */
  public static function hasPersona(AccountInterface $account, $persona) {
  if(isset($account->personas)) {
    $personas = static::fromUser($account);
    return in_array($persona, static::personaNames($personas));
  }
  return FALSE;
  }

  /**
   * Returns a list of persona ids from a list of persona entities.
   *
   * @param \Drupal\personas\PersonaInterface[] $personas
   *   The list of personas from which to get IDs.
   *
   * @return string[]
   *   The list of persona IDs.
   */
  public static function personaNames($personas) {
    return array_map(function ($persona) {
      return $persona->id();
    }, $personas);
  }

}
