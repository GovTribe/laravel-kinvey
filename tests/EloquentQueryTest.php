<?php namespace Govtribe\LaravelKinvey\Tests;

use GovTribe\LaravelKinvey\Facades\Kinvey;

class EloquentQueryTest extends LaravelKinveyTestCase {

	/**
	 * Setup the test environment.10
	 *
	 * @return array
	 */
	public function setup()
	{
		parent::setup();

		$testModels = array(
			array('model' => 'FooBar', 'sku' => 10, 'title' => 'admin'),
			array('model' => 'FooBaz', 'sku' => 8, 'title' => 'admin'),
			array('model' => 'BazBar', 'sku' => 9, 'title' => 'Widget'),
			array('model' => 'FooBaz', 'sku' => 20, 'title' => 'Widget'),
			array('model' => 'BarFoo', 'sku' => 18, 'title' => 'Widget'),
			array('model' => 'FooBar', 'sku' => 10, 'title' => 'Widget'),
			array('model' => 'FooBaz', 'sku' => 8, 'title' => 'Widget'),
			array('model' => 'BazBar', 'sku' => 10, 'title' => 'admin'),
			array('model' => 'FooBaz', 'sku' => null, 'title' => null),
		);

		foreach ($testModels as $item)
		{
			$widget = new Widget();
			$widget->setRawAttributes($item);
			$widget->save();
		}
	}

	/**
	 * Tear down the test environment.
	 *
	 * @return array
	 */
	public function tearDown()
	{
		Widget::truncate();
	}

	/**
	 * Test where.
	 *
	 * @return void
	 */
	public function testWhere()
	{
		$widgets = Widget::where('sku', 10)->get();
		$this->assertEquals(3, count($widgets));

		$widgets = Widget::where('sku', '=', 10)->get();
		$this->assertEquals(3, count($widgets));

		$widgets = Widget::where('sku', '>=', 10)->get();
		$this->assertEquals(5, count($widgets));

		$widgets = Widget::where('sku', '<=', 8)->get();
		$this->assertEquals(2, count($widgets));

		$widgets = Widget::where('sku', '!=', 10)->get();
		$this->assertEquals(6, count($widgets));

		$widgets = Widget::where('sku', '<>', 10)->get();
		$this->assertEquals(6, count($widgets));
	}

	/**
	 * Test multiple wheres.
	 *
	 * @return void
	 */
	public function testAndWhere()
	{
		$widgets = Widget::where('sku', 10)->where('title', 'admin')->get();
		$this->assertEquals(2, count($widgets));

		$widgets = Widget::where('sku', '>=', 10)->where('title', 'Widget')->get();
		$this->assertEquals(3, count($widgets));
	}

	/**
	 * Test select.
	 *
	 * @return void
	 */
	public function testSelect()
	{
		$widget = Widget::select('model')->first();

		$this->assertEquals('FooBar', $widget->model);
		$this->assertEquals(null, $widget->sku);

		$widget = Widget::select('model', 'title')->first();

		$this->assertEquals('FooBar', $widget->model);
		$this->assertEquals('admin', $widget->title);
		$this->assertEquals(null, $widget->sku);

		$widget = Widget::get(array('model'))->first();

		$this->assertEquals('FooBar', $widget->model);
		$this->assertEquals(null, $widget->sku);
	}

	/**
	 * Test orWhere.
	 *
	 * @return void
	 */
	public function testOrWhere()
	{
		$widgets = Widget::where('sku', 9)->orWhere('title', 'admin')->get();
		$this->assertEquals(4, count($widgets));

		$widgets = Widget::where('sku', 9)->orWhere('sku', 18)->get();
		$this->assertEquals(2, count($widgets));
	}

	/**
	 * Test whereBetween.
	 *
	 * @return void
	 */
	public function testWhereBetween()
	{
		$widgets = Widget::whereBetween('sku', array(18, 20))->get();
		$this->assertEquals(2, count($widgets));

		$widgets = Widget::whereBetween('sku', array(9, 18))->get();
		$this->assertEquals(5, count($widgets));
	}

	/**
	 * Test in.
	 *
	 * @return void
	 */
	public function testIn()
	{
		$widgets = Widget::whereIn('sku', array(9, 18))->get();
		$this->assertEquals(2, count($widgets));

		$widgets = Widget::whereIn('sku', array(8, 10, 9))->get();
		$this->assertEquals(6, count($widgets));

		$widgets = Widget::whereNotIn('sku', array(8, 10))->get();
		$this->assertEquals(4, count($widgets));

		$widgets = Widget::whereNotNull('sku')
		             ->whereNotIn('sku', array(8, 10))->get();
		$this->assertEquals(3, count($widgets));
	}

	/**
	 * Test whereNull.
	 *
	 * @return void
	 */
	public function testWhereNull()
	{
		$widgets = Widget::whereNull('sku')->get();
		$this->assertEquals(1, count($widgets));
	}

	/**
	 * Test whereNotNull.
	 *
	 * @return void
	 */
	public function testWhereNotNull()
	{
		$widgets = Widget::whereNotNull('sku')->get();
		$this->assertEquals(8, count($widgets));
	}

	/**
	 * Test orderBy.
	 *
	 * @return void
	 */
	public function testOrder()
	{
		$widget = Widget::whereNotNull('sku')->orderBy('sku', 'asc')->first();
		$this->assertEquals(8, $widget->sku);

		$widget = Widget::whereNotNull('sku')->orderBy('sku', 'desc')->first();
		$this->assertEquals(20, $widget->sku);
	}

	/**
	 * Test increment.
	 *
	 * @return void
	 */
	public function testIncrements()
	{
		$widget = Widget::where('sku', 20)->first();

		$widget->increment('sku');
		$this->assertEquals(21, $widget->sku);
		$widget->decrement('sku');
		$this->assertEquals(20, $widget->sku);
	}

	/**
	 * Test subqueries.
	 *
	 * @return void
	 */
	public function testSubquery()
	{
		$widgets = Widget::where('title', 'admin')->orWhere(function($query)
		{
			$query->where('model', 'FooBaz')
				->orWhere('model', 'Foobar');
		})
		->get();

		$this->assertEquals(6, count($widgets));

		$widgets = Widget::where('title', 'Widget')->where(function($query)
			{
				$query->where('sku', 10)
					->orWhere('model', 'like', 'Foo%');
			})
		->get();
		$this->assertEquals(3, count($widgets));

		$widgets = Widget::where('sku', 10)->orWhere(function($query)
		{
			$query->where('title', 'admin')
				->orWhere('model', 'Error');
		})
		->get();

		$this->assertEquals(4, count($widgets));
	}

	/**
	 * Test whereRaw.
	 *
	 * @return void
	 */
	public function whereRaw()
	{
		$where = array('sku' => array('$gt' => 30, '$lt' => 40));
		$widgets = Widget::whereRaw($where)->get();

		$this->assertEquals(6, count($widgets));

		$where1 = array('sku' => array('$gt' => 30, '$lte' => 10));
		$where2 = array('sku' => array('$gt' => 10, '$lt' => 40));
		$widgets = Widget::whereRaw($where1)->orWhereRaw($where2)->get();

		$this->assertEquals(6, count($widgets));
	}

}