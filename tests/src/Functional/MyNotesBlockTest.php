<?php

namespace Drupal\Tests\mynotes\Functional;

use Drupal\Tests\block\Traits\BlockCreationTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the Quick Note Block.
 *
 * @group mynotes
 */
class MyNotesBlockTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'block',
    'facets',
    'mynotes',
    'mynotes_entities',
    'search_api',
    'node',
    'views',
    'taxonomy',
    'file',
    'image',
    'menu_ui',
    'path',
  ];

  use BlockCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'bartik';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $admin_user = $this->drupalCreateUser([
      'administer blocks',
      'administer site configuration',
      'access administration pages',
    ]);

    $this->drupalLogin($admin_user);
  }

  /**
   * Test that the block is available.
   */
  public function testBlockAvailability() {
    $this->drupalGet('/admin/structure/block');
    $this->clickLink('Place block');
    $this->assertSession()->pageTextContains('Quick Note block');
    $this->assertSession()->linkByHrefExists('admin/structure/block/add/quick_note_block/', 0);
  }

}
