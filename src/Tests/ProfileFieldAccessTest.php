<?php

/**
 * @file
 * Contains \Drupal\profile\Tests\ProfileFieldAccessTest.
 */

namespace Drupal\profile\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\profile\Entity\ProfileType;

/**
 * Tests profile field access functionality.
 *
 * @group profile
 */
class ProfileFieldAccessTest extends ProfileTestBase {

  private $web_user;
  private $other_user;

  function setUp() {
    parent::setUp();

    $this->admin_user = $this->drupalCreateUser([
      'access user profiles',
      'administer profile types',
      'administer profile fields',
      'administer profile display',
      'bypass profile access',
    ]);

    $user_permissions = [
      'access user profiles',
      "add own {$this->type->id()} profile",
      "edit own {$this->type->id()} profile",
      "view any {$this->type->id()} profile",
    ];

    $this->web_user = $this->drupalCreateUser($user_permissions);
    $this->other_user = $this->drupalCreateUser($user_permissions);
  }

  /**
   * Tests private profile field access.
   */
  function testPrivateField() {
    $this->drupalLogin($this->admin_user);

    // Create a private profile field.
    $edit = [
      'new_storage_type' => 'string',
      'label' => 'Secret',
      'field_name' => 'secret',
    ];
    $this->drupalPostForm("admin/config/people/profiles/types/manage/{$this->type->id()}/fields/add-field", $edit, t('Save and continue'));

    // @todo #2628108, #2599010.
    /*
    $edit = [
      'field[settings][profile_private]' => 1,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save field settings'));
    $this->drupalPostForm(NULL, [], t('Save settings'));

    // Fill in a field value.
    $this->drupalLogin($this->web_user);
    $uid = $this->web_user->id();
    $secret = $this->randomMachineName();
    $edit = [
      'field_secret[0][value]' => $secret,
    ];
    $this->drupalPostForm("user/$uid/edit/profile/{$this->type->id()}", $edit, t('Save'));

    // User cache page need to be cleared to see new profile.
    // TODO: We shouldn't have to clear all cache to display this.
    drupal_flush_all_caches();

    // Verify that the private field value appears for the profile owner.
    $this->drupalGet("user/$uid");
    $this->assertText($secret);

    // Verify that the private field value appears for the administrator.
    $this->drupalLogin($this->admin_user);
    $this->drupalGet("user/$uid");
    $this->assertText($secret);

    // Verify that the private field value does not appear for other users.
    $this->drupalLogin($this->other_user);
    $this->drupalGet("user/$uid");
    $this->assertNoText($secret);
    */
  }

}
