<?php namespace Govtribe\LaravelKinvey\Tests;

use GovTribe\LaravelKinvey\Facades\Kinvey;

class ModelTest extends LaravelKinveyTestCase {

	/**
	 * Setup the test environment.10
	 *
	 * @return array
	 */
	public function setup()
	{
		parent::setup();

		$testModels = array(
			array('model' => 'FooBar', 'sku' => 10, 'type' => 'small'),
			array('model' => 'FooBaz', 'sku' => 8, 'type' => 'small'),
			array('model' => 'BazBar', 'sku' => 9, 'type' => 'med'),
			array('model' => 'FooBaz', 'sku' => 20, 'type' => 'med'),
			array('model' => 'BarFoo', 'sku' => 18, 'type' => 'med'),
			array('model' => 'FooBar', 'sku' => 10, 'type' => 'med'),
			array('model' => 'FooBaz', 'sku' => 8, 'type' => 'med'),
			array('model' => 'BazBar', 'sku' => 10, 'type' => 'small'),
			array('model' => 'FooBaz', 'sku' => null, 'type' => null),
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
		Warehouse::truncate();
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
		$widgets = Widget::where('sku', 10)->where('type', 'small')->get();
		$this->assertEquals(2, count($widgets));

		$widgets = Widget::where('sku', '>=', 10)->where('type', 'med')->get();
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

		$widget = Widget::select('model', 'type')->first();

		$this->assertEquals('FooBar', $widget->model);
		$this->assertEquals('small', $widget->type);
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
		$widgets = Widget::where('sku', 9)->orWhere('type', 'small')->get();
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
		$widgets = Widget::where('type', 'small')->orWhere(function($query)
		{
			$query->where('model', 'FooBaz')
				->orWhere('model', 'Foobar');
		})
		->get();

		$this->assertEquals(6, count($widgets));

		$widgets = Widget::where('type', 'med')->where(function($query)
			{
				$query->where('sku', 10)
					->orWhere('model', 'like', 'Foo%');
			})
		->get();
		$this->assertEquals(3, count($widgets));

		$widgets = Widget::where('sku', 10)->orWhere(function($query)
		{
			$query->where('type', 'small')
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

	/**
	 * Insert model.
	 *
	 * @return void
	 */
	public function testInsertModel()
	{
		$widget = new Widget();
		$widget->type = 'small';
		$widget->sku = 99;
		$widget->save();

		$this->assertTrue(isset($widget->_id));
		$this->assertNotEquals('', (string) $widget->_id);
		$this->assertNotEquals(0, strlen((string) $widget->_id));
		$this->assertInstanceOf('Carbon\Carbon', $widget->created_at);
		$this->assertInstanceOf('Carbon\Carbon', $widget->updated_at);
		$this->assertEquals('small', $widget->type);
		$this->assertEquals(99, $widget->sku);
	}

	/**
	 * New model.
	 *
	 * @return void
	 */
	public function testNewModel()
	{
		$widget = new Widget;
		$this->assertInstanceOf('Govtribe\LaravelKinvey\Tests\Widget', $widget);
		$this->assertEquals(false, $widget->exists);
		$this->assertEquals('widgets', $widget->getTable());
		$this->assertEquals('_id', $widget->getKeyName());
		$this->assertEquals('widgets._id', $widget->getQualifiedKeyName());
	}

	/**
	 * Create model.
	 *
	 * @return void
	 */
	public function testCreateModel()
	{
		$widget = Widget::create(array('sku' => 42, 'model' => 'BarFooBaz'));
		$this->assertInstanceOf('Govtribe\LaravelKinvey\Tests\Widget', $widget);
		$this->assertEquals(true, $widget->exists);
		$this->assertEquals(42, $widget->sku);

		$exists = Widget::where('sku', 42)->first();
		$this->assertEquals($exists, $widget);
	}

	/**
	 * Update model.
	 *
	 * @return void
	 */
	public function testUpdateModel()
	{
		$widget = new Widget();
		$widget->type = 'small';
		$widget->sku = 99;
		$widget->save();

		(sleep(1));
		$updatedWidget = Widget::find($widget->_id);
		$updatedWidget->foo = 'bar';
		$updatedWidget->save();

		$updatedWidget = Widget::find($widget->_id);
		$updatedAttributes = $updatedWidget->getAttributes();

		$this->assertArrayHasKey('foo', $updatedAttributes, 'Updated widget has new key');
		$this->assertEquals('bar', $updatedAttributes['foo'], 'New widget has correct value');

		$this->assertArrayHasKey('sku', $updatedAttributes, 'Updated widget has original attribute key');
		$this->assertEquals(99, $updatedAttributes['sku'], 'Original attribute has correct value');

		$this->assertArrayHasKey('type', $updatedAttributes, 'Updated widget has original attribute key');
		$this->assertEquals('small', $updatedAttributes['type'], 'Original attribute has correct value');

		$this->assertNotEquals($widget->updated_at, $updatedWidget->updated_at, 'Updated widget has new updated_at timestamp');

		$updatedWidget->update(array('sku' => 100));

		$this->assertEquals(100, $updatedWidget->sku);

		$updatedAttributes = $updatedWidget->getAttributes();
		$this->assertArrayHasKey('type', $updatedAttributes, 'Updated widget has original attribute key');
		$this->assertEquals('small', $updatedAttributes['type'], 'Original attribute has correct value');

	}

	/**
	 * Find model.
	 *
	 * @return void
	 */
	public function testFindModel()
	{
		$widget = Widget::first();

		$this->assertInstanceOf('Govtribe\LaravelKinvey\Tests\Widget', $widget);
		$this->assertEquals(true, $widget->exists);

		$this->assertEquals('small', $widget->type);
		$this->assertEquals(10, $widget->sku);

	}

	/**
	 * Delete model.
	 *
	 * @return void
	 */
	public function testDeleteModel()
	{
		Widget::truncate();

		$widget = new Widget();
		$widget->type = 'small';
		$widget->sku = 99;
		$widget->save();

		$this->assertEquals(true, $widget->exists);
		$this->assertEquals(1, Widget::count());

		$widget->delete();

		$this->assertEquals(0, Widget::count());
	}

	/**
	 * Retrieve all models.
	 *
	 * @return void
	 */
	public function testRetrieveAllModels()
	{
		$all = Widget::all();

		$this->assertEquals(9, count($all));
		$this->assertEquals('FooBar', $all[0]->model);
		$this->assertEquals('FooBaz', $all[1]->model);
	}

	/**
	 * Get model.
	 *
	 * @return void
	 */
	public function testGetModel()
	{
		$widgets = Widget::get();
		$this->assertEquals(9, count($widgets));
		$this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $widgets);
		$this->assertInstanceOf('Govtribe\LaravelKinvey\Tests\Widget', $widgets[0]);
	}

	/**
	 * Get first model.
	 *
	 * @return void
	 */
	public function testGetFirstModel()
	{
		$widget = Widget::get()->first();
		$this->assertInstanceOf('Govtribe\LaravelKinvey\Tests\Widget', $widget);
		$this->assertEquals('FooBar', $widget->model);
	}

	/**
	 * Get empty results.
	 *
	 * @return void
	 */
	public function testNoModels()
	{
		$widgets = Widget::where('model', 'FooBazBar')->get();
		$this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $widgets);
		$this->assertEquals(0, $widgets->count());

		$widget = Widget::find('52b06322694139b01fdb0d31');
		$this->assertEquals(null, $widget);
	}

	/**
	 * Find or fail.
	 *
	 * @return void
	 */
	public function testFindOrFailModel()
	{
		$this->setExpectedException('Illuminate\Database\Eloquent\ModelNotFoundException');
		$widget = Widget::findOrFail('52b06322694139b01fdb0d31');
	}

	/**
	 * Destroy model.
	 *
	 * @return void
	 */
	public function testDestroyModel()
	{
		Widget::truncate();

		$widget = new Widget();
		$widget->sku = 42;
		$widget->model = 'small';
		$widget->save();

		Widget::destroy($widget->_id);
		$this->assertEquals(0, Widget::count());
	}

	/**
	 * Touch model.
	 *
	 * @return void
	 */
	public function testTouchModel()
	{
		$widget = new Widget();
		$widget->sku = 42;
		$widget->model = 'small';
		$widget->save();

		$old = $widget->updated_at;

		sleep(1);

		$widget->touch();

		$check = Widget::find($widget->_id);

		$this->assertNotEquals($old, $check->updated_at);
	}

	/**
	 * Test scope.
	 *
	 * @return void
	 */
	public function testModelScope()
	{
		Widget::truncate();

		Widget::insert(array(
			array('model' => 'large', 'sku' => 19),
			array('model' => 'small', 'sku' => 14)
		));

		$large = Widget::large()->get();
		$this->assertEquals(1, $large->count());
	}

	/**
	 * Test unset.
	 *
	 * @return void
	 */
	public function testUnset()
	{
		$widget = Widget::create(array('model' => 'small', 'badKey' => 'foo'));

		$widget->offsetUnset('badKey');
		$widget->save();

		$this->assertFalse(isset($widget->badKey));
		$check = Widget::find($widget->_id);
		$this->assertFalse(isset($check->badKey));
	}

	/**
	 * Test dates.
	 *
	 * @return void
	 */
	public function testDates()
	{
		$widget = Widget::create(array('model' => 'large', 'sku' => 19, 'expires' => new \DateTime('2012/1/1')));
		$this->assertInstanceOf('Carbon\Carbon', $widget->expires);

		$check = Widget::find($widget->_id);
		$this->assertInstanceOf('Carbon\Carbon', $check->expires);
		$this->assertEquals($widget->expires, $check->expires);

		$widget = Widget::where('expires', '>', new \DateTime('2011/1/1'))->first();
		$this->assertEquals(19, $widget->sku);
	}
}