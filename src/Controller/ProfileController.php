<?php

/**
 * @file
 * Contains \Drupal\profile\Controller\ProfileController.
 */

namespace Drupal\profile\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\profile\Entity\ProfileTypeInterface;
use Drupal\profile\Entity\Profile;
use Drupal\user\UserInterface;

/**
 * Returns responses for ProfileController routes.
 */
class ProfileController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Provides the profile submission form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   * @param \Drupal\profile\Entity\ProfileTypeInterface $profile_type
   *   The profile type entity for the profile.
   *
   * @return array
   *   A profile submission form.
   */
  public function addProfile(RouteMatchInterface $route_match, UserInterface $user, ProfileTypeInterface $profile_type) {

    $profile = $this->entityTypeManager()->getStorage('profile')->create([
      'uid' => $user->id(),
      'type' => $profile_type->id(),
    ]);

    return $this->entityFormBuilder()->getForm($profile, 'add', ['uid' => $user->id(), 'created' => REQUEST_TIME]);
  }

  /**
   * Provides the profile edit form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   * @param \Drupal\profile\Entity\ProfileInterface $profile
   *   The profile entity to edit.
   *
   * @return array
   *   The profile edit form.
   */
  public function editProfile(RouteMatchInterface $route_match, UserInterface $user, ProfileInterface $profile) {
    return $this->entityFormBuilder()->getForm($profile, 'edit');
  }

  /**
   * Provides profile delete form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   * @param \Drupal\profile\Entity\ProfileTypeInterface $profile_type
   *   The profile type entity for the profile.
   * @param int $id
   *   The id of the profile to delete.
   *
   * @return array
   *   Returns form array.
   */
  public function deleteProfile(RouteMatchInterface $route_match, UserInterface $user, ProfileTypeInterface $profile_type, $id) {
    return $this->entityFormBuilder()->getForm(Profile::load($id), 'delete');
  }

  /**
   * The _title_callback for the entity.profile.add_form route.
   *
   * @param \Drupal\profile\Entity\ProfileTypeInterface $profile_type
   *   The current profile type.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(ProfileTypeInterface $profile_type) {
    // @todo: edit profile uses this form too?
    return $this->t('Create @label', ['@label' => $profile_type->label()]);
  }

  /**
   * Provides profile create form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   * @param \Drupal\profile\Entity\ProfileTypeInterface $profile_type
   *   The profile type entity for the profile.
   *
   * @return array
   *    Returns form array.
   */
  public function userProfileForm(RouteMatchInterface $route_match, UserInterface $user, ProfileTypeInterface $profile_type) {
    /** @var \Drupal\profile\Entity\ProfileType $profile_type */

    /** @var \Drupal\profile\Entity\ProfileInterface|bool $active_profile */
    $active_profile = $this->entityTypeManager()->getStorage('profile')
                           ->loadByUser($user, $profile_type->id());

    // If the profile type does not support multiple, only display an add form
    // if there are no entities, or an edit for the current.
    if (!$profile_type->getMultiple()) {

      // If there is an active profile, provide edit form.
      if ($active_profile) {
        return $this->editProfile($route_match, $user, $active_profile);
      }

      // Else show the add form.
      return $this->addProfile($route_match, $user, $profile_type);
    }
    // Display active, inactive, and link to create a profile.
    else {
      $build = [];

      $active_profiles = $this->entityTypeManager()->getStorage('profile')
                                ->loadMultipleByUser($user, $profile_type->id(), PROFILE_ACTIVE);
      $inactive_profiles = $this->entityTypeManager()->getStorage('profile')
                                ->loadMultipleByUser($user, $profile_type->id(), PROFILE_NOT_ACTIVE);

      // If there is no active profile or inactive, display add form.
      if (!$active_profile && !$inactive_profiles) {
        return $this->addProfile($route_match, $user, $profile_type);
      }

      $build['add_profile'] = Link::createFromRoute(
        $this->t('Add new @type', ['@type' => $profile_type->label()]),
        "entity.profile.type.{$profile_type->id()}.user_profile_form.add",
        ['user' => \Drupal::currentUser()->id(), 'profile_type' => $profile_type->id()])
        ->toRenderable();

      // Render the active profiles.
      $build['active_profiles'] = [
        '#theme' => 'profile_items',
        '#profile_items' => $active_profiles,
        '#title' => $this->t('Active @type', ['@type' => $profile_type->label()]),
      ];

      // List all inactive profiles.
      $build['inactive_profiles'] = [
        '#theme' => 'profile_items',
        '#profile_items' => $inactive_profiles,
        '#title' => $this->t('Inactive @type', ['@type' => $profile_type->label()]),
      ];

      return $build;
    }
  }

}
