<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @group kongo
 * @group kongo.core
 * @group kongo.core.nav
 */
class UnitTest_Nav extends PHPUnit_Framework_Testcase {

	public function setUp()
	{
		Nav::$current_uri = 'products/boxpads';
	}

	public function tearDown()
	{
		Nav::reset();
	}

	public function test_factory()
	{
		$nav = Nav::factory();
		$this->assertTrue($nav instanceof Nav);
	}

	public function test_send_current_uri_through_instance_method()
	{
		$primary = Nav::instance();
		$this->assertEquals(Nav::$current_uri, 'products/boxpads');
	}

	public function test_add()
	{
		$primary = Nav::instance()
						->add('Home', NULL, Nav::factory()
								->add('Products', 'products', Nav::factory()
										->add('Wellcasing', 'products/wellcasing')
										->add('Box Pads', 'products/boxpads', Nav::factory()
												->add('News', 'news'))))
						->add('Showroom', 'showroom');

		$this->assertEquals($primary->items[0]->title, 'Home');
		$this->assertEquals($primary->items[0]->nav->items[0]->nav->items[0]->title, 'Wellcasing');
	}

	public function test_active_path()
	{
		$primary = Nav::instance()
						->add('Home', NULL, Nav::factory()
								->add('Products', 'products', Nav::factory()
										->add('Wellcasing', 'products/wellcasing')
										->add('Box Pads', 'products/boxpads')))
						->add('Showroom', 'showroom');

		$primary->render();

		$this->assertTrue($primary->items[0]->active);
		$this->assertTrue($primary->items[0]->nav->items[0]->active);
		$this->assertTrue($primary->items[0]->nav->items[0]->nav->items[1]->active);
		$this->assertTrue($primary->items[0]->nav->items[0]->nav->items[1]->current);

		$primary->reset();

		Nav::$current_uri = 'showroom';
		$primary = Nav::instance()
						->add('Home', NULL, Nav::factory()
								->add('Products', 'products', Nav::factory()
										->add('Wellcasing', 'products/wellcasing')
										->add('Box Pads', 'products/boxpads')))
						->add('Showroom', 'showroom');

		$this->assertTrue($primary->items[1]->active);
		$this->assertTrue($primary->items[1]->current);
		$this->assertFalse($primary->items[0]->active);
	}

	public function test_render()
	{
		$primary = Nav::instance()
						->add('Home', NULL, Nav::factory()
								->add('Products', 'products', Nav::factory()
										->add('Wellcasing', 'products/wellcasing')
										->add('Box Pads', 'products/boxpads')))
						->add('Showroom', 'showroom');

		$this->assertEquals(
				'<ul class="level-1"><li class="parent active">Home<ul class="level-2"><li class="parent active"><a href="/kongo/products">Products</a><ul class="level-3"><li><a href="/kongo/products/wellcasing">Wellcasing</a></li><li class="active current"><a href="/kongo/products/boxpads">Box Pads</a></li></ul></li></ul></li><li><a href="/kongo/showroom">Showroom</a></li></ul>',
				$primary->render()
		);
	}

	public function test_render_subchild()
	{
		$primary = Nav::instance()
						->add('Home', NULL, Nav::factory()
								->add('Products', 'products', Nav::factory()
										->add('Wellcasing', 'products/wellcasing')
										->add('Box Pads', 'products/boxpads')))
						->add('Showroom', 'showroom');

		$this->assertEquals(
				'<ul class="level-1"><li class="parent active"><a href="/kongo/products">Products</a><ul class="level-2"><li><a href="/kongo/products/wellcasing">Wellcasing</a></li><li class="active current"><a href="/kongo/products/boxpads">Box Pads</a></li></ul></li></ul>',
				$primary->items[0]->nav->render()
		);
	}

	public function test_nav_interfaces()
	{
		$primary = Nav::instance()
						->add('Home', NULL, Nav::factory()
								->add('Products', 'products', Nav::factory()
										->add('Wellcasing', 'products/wellcasing')
										->add('Box Pads', 'products/boxpads')))
						->add('Showroom', 'showroom');

		$this->assertEquals('Products', $primary->current()->nav->current()->title);
		$this->assertEquals(2, $primary->count());
		$this->assertEquals(2, $primary->current()->nav->current()->nav->count());
		$this->assertEquals(2, $primary[0]->nav[0]->nav->count());
		$this->assertEquals(2, $primary->current()->nav->current()->count());
		$this->assertTrue(isset($primary[0]));
		$primary->next();
		$this->assertEquals('Showroom', $primary->current()->title);
		$primary->next();
		$this->assertFalse($primary->valid());
		$primary->rewind();
		$this->assertTrue($primary->valid());
		unset($primary[0]);
		$this->assertFalse(isset($primary[0]));
	}

	public function test_set_items_as_array_through_factory_for_when_loading_nav_from_config_file()
	{
		$primary = Nav::instance('primary', array(
					array('title' => 'Home', 'nav' => array(
							array('title' => 'Products', 'url' => 'products', 'nav' => array(
									array('title' => 'Wellcasing', 'url' => 'products/wellcasing'),
									array('title' => 'Box Pads', 'url' => 'products/boxpads'),
									array('title' => 'ACWM', 'url' => 'products/acwm'),
							)),
					)),
					array('title' => 'Showroom', 'url' => 'showroom', 'nav' => array(
							array('title' => 'Gallery', 'url' => 'showroom/gallery'),
							array('title' => 'Directions', 'url' => 'showroom/directions'),
					)),
				));

		$this->assertEquals('Home', $primary->current()->title);
		$this->assertEquals('Products', $primary->current()->nav->current()->title);
		$this->assertEquals('Showroom', $primary->next()->current()->title);
	}

	public function test_new_primary_nav_instance()
	{
		Nav::$current_uri = 'products';
		$primary = Nav::instance('primary')
						->add('Home', NULL, Nav::factory()
								->add('Products', 'products'));

		$footer = Nav::instance('footer')
						->add('Contact', 'contact-us')
						->add('Directions', 'directions');

		$this->assertEquals('Products', $primary->current()->nav->current()->title);
		$this->assertEquals('Contact', $footer->current()->title);
	}

	public function test_current_nav_item_chooses_best_available_nav_item_as_current_when_request_uri_doesnt_match_any_nav_items()
	{
		Nav::$current_uri = 'admin/cms/pages/edit/1';
		$primary = Nav::instance()
						->add('Pages', 'admin/cms/pages');

		$this->assertTrue($primary->current()->current);
		$this->assertTrue($primary->current()->active);
	}

	public function test_current_nav_item_chooses_appropriate_active_item_when_other_items_are_possible_best_match_candidates()
	{
		Nav::$current_uri = 'admin/cms/pages/edit/1';
		$primary = Nav::instance()
						->add('Pages', 'admin/cms/pages', Nav::factory()
								->add('Edit', 'admin/cms/pages/edit/1'));

		$primary->render();

		$this->assertTrue($primary[0]->nav[0]->current);
		$this->assertTrue($primary[0]->nav[0]->active);
		$this->assertFalse($primary[0]->current);
		$this->assertTrue($primary[0]->active);
	}
	
	public function test_render_as_breadcrumb()
	{
		$primary = Nav::instance()
			->add('Home', '/', Nav::factory()
					->add('Products', 'products', Nav::factory()
							->add('Wellcasing', 'products/wellcasing')
							->add('Box Pads', 'products/boxpads')))
			->add('Showroom', 'showroom');
		
		// Home > Products > Box Pads
		$expected = '<ul><li><a href="/kongo/">Home</a><li><a href="/kongo/products">Products</a><li>Box Pads</li></li></li></ul>';
		$actual = $primary->breadcrumbs();
		$this->assertEquals($expected, $actual);
	}

}