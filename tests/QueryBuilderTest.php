<?php namespace Govtribe\LaravelKinvey\Tests;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use \DateTime;

class QueryBuilderTest extends LaravelKinveyTestCase {

	/**
	 * Tear down the test environment.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		DB::collection('widgets')->truncate();
	}

	/**
	 * Test get.
	 *
	 * @return void
	 */
	public function testGet()
	{
		$widgets = DB::collection('widgets')->get();
		$this->assertEquals(0, count($widgets));

		DB::collection('widgets')->insert(array('type' => 'small'));

		$widgets = DB::collection('widgets')->get();
		$this->assertEquals(1, count($widgets));
	}

	/**
	 * Test no results.
	 *
	 * @return void
	 */
	public function testNoResults()
	{
		DB::collection('widgets')->insert(array('type' => 'large'));

		$widgets = DB::collection('widgets')->where('type', 'small')->get();

		$this->assertEquals(array(), $widgets);

		$widgets = DB::collection('widgets')->where('type', 'small')->first();
		$this->assertEquals(null, $widgets);

		$widgets = DB::collection('widgets')->where('_id', '51c33d8981fec6813e00000a')->first();
		$this->assertEquals(null, $widgets);
	}

	/**
	 * Test insert.
	 *
	 * @return void
	 */
	public function testInsert()
	{
		// Single insert
		DB::collection('widgets')->insert(array('type' => 'large'));

		$widgets = DB::collection('widgets')->get();
		$this->assertEquals(1, count($widgets));

		$widget = $widgets[0];
		$this->assertEquals('large', $widget['type']);

		DB::collection('widgets')->truncate();

		//Batch insert with custom IDs
		DB::collection('widgets')->insert(array(
			array('_id' => 'foo', 'type' => 'large'),
			array('_id' => 'bar',  'type' => 'small'),
			array('_id' => 'baz', 'type' => 'med')
		));

		$widgets = DB::collection('widgets')->get();
		$this->assertEquals(3, count($widgets));
		$widget = $widgets[0];
		$this->assertEquals('large', $widget['type']);
	}

	/**
	 * Test insert and get ID.
	 *
	 * @return void
	 */
	public function testInsertGetId()
	{
		$id = DB::collection('widgets')->insertGetId(array('type' => 'small'));

		$this->assertTrue(is_string($id));
		$this->assertEquals(24, strlen($id));
	}

	/**
	 * Test find.
	 *
	 * @return void
	 */
	public function testFind()
	{
		$id = DB::collection('widgets')->insertGetId(array('type' => 'small'));

		$widget = DB::collection('widgets')->find($id);

		$this->assertEquals('small', $widget['type']);
	}

	/**
	 * Test find null.
	 *
	 * @return void
	 */
	public function testFindNull()
	{
		$widget = DB::collection('widgets')->find(null);
		$this->assertEquals(null, $widget);
	}

	/**
	 * Test count.
	 *
	 * @return void
	 */
	public function testCount()
	{
		DB::collection('widgets')->insert(array(
			array('type' => 'small'),
			array('type' => 'large')
		));

		$this->assertEquals(2, DB::collection('widgets')->count());
	}

	/**
	 * Test update.
	 *
	 * @return void
	 */
	public function testUpdate()
	{
		DB::collection('widgets')->insert(array(
			array('type' => 'small', 'sku' => 10),
			array('type' => 'large', 'sku' => 5)
		));

		DB::collection('widgets')->where('type', 'small')->update(array('sku' => 12));
		$widgets = DB::collection('widgets')->get();

		$this->assertEquals(12, $widgets[0]['sku']);
		$this->assertEquals(5, $widgets[1]['sku']);
	}

	/**
	 * Test delete.
	 *
	 * @return void
	 */
	public function testDelete()
	{
		DB::collection('widgets')->insert(array(
			array('type' => 'small', 'sku' => 10),
			array('type' => 'large', 'sku' => 5)
		));

		DB::collection('widgets')->where('sku', '<', 4)->delete();
		$this->assertEquals(2, count(DB::collection('widgets')->get()));

		DB::collection('widgets')->where('sku', '<', 9)->delete();
		$this->assertEquals(1, count(DB::collection('widgets')->get()));
	}

	/**
	 * Test truncate.
	 *
	 * @return void
	 */
	public function testTruncate()
	{
		DB::collection('widgets')->insert(array(
			array('type' => 'small', 'sku' => 10),
			array('type' => 'large', 'sku' => 5)
		));

		DB::collection('widgets')->truncate();
		$this->assertEquals(0, count(DB::collection('widgets')->get()));
	}

	/**
	 * Test embedded document.
	 *
	 * @return void
	 */
	public function testEmbeddedDoc()
	{
		DB::collection('widgets')->insert(array(
			array('type' => 'small', 'sku' => 10, 'dimensions' => array('width' => 4, 'height' => 12)),
			array('type' => 'large', 'sku' => 5, 'dimensions' => array('width' => 5, 'height' => 3)),
		));

		$widgets = DB::collection('widgets')->where('dimensions.width', 4)->get();
		$this->assertEquals(1, count($widgets));
		$this->assertEquals('small', $widgets[0]['type']);
	}

	/**
	 * Test embedded set.
	 *
	 * @return void
	 */
	public function testEmbeddedSet()
	{
		DB::collection('widgets')->insert(array(
			array(
				'type' => 'small',
				'tags' => array('foo', 'bar')
			),
			array(
				'type' => 'large',
				'tags' => array('foo', 'bar', 'baz')
			)
		));

		$widgets = DB::collection('widgets')->where('tags', 'foo')->get();
		$this->assertEquals(2, count($widgets));

		$widgets = DB::collection('widgets')->where('tags', 'baz')->get();
		$this->assertEquals(1, count($widgets));
	}

	/**
	 * Test custom entity IDs.
	 *
	 * @return void
	 */
	public function testCustomId()
	{
		DB::collection('widgets')->insert(array(
			array('type' => 'small', '_id' => 'foo', 'dimensions' => array('width' => 4, 'height' => 12)),
			array('type' => 'large', '_id' => 'bar', 'dimensions' => array('width' => 5, 'height' => 3)),
		));

		$widget = DB::collection('widgets')->find('foo');
		$this->assertEquals('foo', $widget['_id']);

		$widget = DB::collection('widgets')->where('_id', 'bar')->first();
		$this->assertEquals('bar', $widget['_id']);
	}

	/**
	 * Test take.
	 *
	 * @return void
	 */
	public function testTake()
	{
		DB::collection('widgets')->insert(array(
			array('type' => 'med'),
			array('type' => 'med'),
			array('type' => 'small'),
			array('type' => 'large'),
			array('type' => 'large'),
		));

		$widgets = DB::collection('widgets')->take(2)->get();
		$this->assertEquals(2, count($widgets));
		$this->assertEquals('med', $widgets[0]['type']);
	}

	/**
	 * Test skip.
	 *
	 * @return void
	 */
	public function testSkip()
	{
		DB::collection('widgets')->insert(array(
			array('sku' => 0),
			array('sku' => 1),
			array('sku' => 2),
			array('sku' => 3),
		));

		$widgets = DB::collection('widgets')->skip(3)->get();
		$this->assertEquals(1, count($widgets));
		$this->assertEquals(3, $widgets[0]['sku']);
	}

	/**
	 * Test pluck.
	 *
	 * @return void
	 */
	public function testPluck()
	{
		DB::collection('widgets')->insert(array(
			array('type' => 'small', 'sku' => 0),
			array('type' => 'med', 'sku' => 1),
			array('type' => 'med', 'sku' => 2),
			array('type' => 'large', 'sku' => 3),
		));

		$sku = DB::collection('widgets')->where('type', 'large')->pluck('sku');
		$this->assertEquals(3, $sku);
	}

	/**
	 * Test aggregation.
	 *
	 * @return void
	 */
	public function testAggregate()
	{
		DB::collection('widgets')->insert(array(
			array('type' => 'small', 'sku' => 0),
			array('type' => 'med', 'sku' => 1),
			array('type' => 'med', 'sku' => 2),
			array('type' => 'large', 'sku' => 3),
		));

		$this->assertEquals(4, DB::collection('widgets')->count());
		$this->assertEquals(0, DB::collection('widgets')->min('sku'));
		$this->assertEquals(3, DB::collection('widgets')->max('sku'));
		$this->assertEquals(2, DB::collection('widgets')->where('type', 'med')->count());
		$this->assertEquals(2, DB::collection('widgets')->where('type', 'med')->max('sku'));
	}

	/**
	 * Test list.
	 *
	 * @return void
	 */
	public function testList()
	{
		DB::collection('widgets')->insert(array(
			array('type' => 'small', 'sku' => 'x'),
			array('type' => 'med', 'sku' => 'y'),
			array('type' => 'large', 'sku' => 'z'),
		));

		$list = DB::collection('widgets')->lists('type');
		$this->assertEquals(array('small', 'med', 'large'), $list);

		$list = DB::collection('widgets')->lists('type', 'sku');
		$this->assertEquals(array('x' => 'small', 'y' => 'med', 'z' => 'large'), $list);
	}

	/**
	 * Test update embedded docs.
	 *
	 * @return void
	 */
	public function testUpdateEmbeddedDocs()
	{
		$id = DB::collection('widgets')->insertGetId(array('type' => 'small', 'dimensions' => array('width' => 4, 'height' => 12)));

		DB::collection('widgets')->where('_id', $id)->update(array('dimensions' => array('width' => 7, 'height' => 3)));

		$widget = DB::collection('widgets')->find($id);
		$this->assertEquals(7, $widget['dimensions']['width']);
		$this->assertEquals(3, $widget['dimensions']['height']);
	}

	/**
	 * Test dates.
	 *
	 * @return void
	 */
	public function testDates()
	{
		DB::collection('widgets')->insert(array('sku' => 0, 'added' => (new Carbon("Jan 1 2009"))->toISO8601String()));
		DB::collection('widgets')->insert(array('sku' => 1, 'added' => (new Carbon("Jan 1 2010"))->toISO8601String()));
		DB::collection('widgets')->insert(array('sku' => 2, 'added' => (new Carbon("Jan 1 2011"))->toISO8601String()));
		DB::collection('widgets')->insert(array('sku' => 3, 'added' => (new Carbon("Jan 1 2012"))->toISO8601String()));

		$widget = DB::collection('widgets')->where('added', (new Carbon("Jan 1 2011"))->toISO8601String())->first();
		$this->assertEquals(2, $widget['sku']);

		$widget = DB::collection('widgets')->where('added', '=',  (new Carbon("Jan 1 2009"))->toISO8601String())->first();
		$this->assertEquals(0, $widget['sku']);

		$start = (new Carbon("Jan 1 2011"))->toISO8601String();
		$stop = (new Carbon("Jan 1 2012"))->toISO8601String();

		$widgets = DB::collection('widgets')->whereBetween('added', array($start, $stop))->get();
		$this->assertEquals(2, count($widgets));
	}

	/**
	 * Test operators.
	 *
	 * @return void
	 */
	public function testOperators()
	{
		DB::collection('widgets')->insert(array(
			array('type' => 'small', 'sku' => 0, 'tags' => array('foo', 'bar', 'baz')),
			array('type' => 'med', 'sku' => 1, 'tags' => array('bar', 'baz')),
			array('type' => 'med', 'sku' => 2, 'amount' => 'zero', 'tags' => array('baz')),
			array('type' => 'large', 'tags' => array('foo', 'baz')),
		));

		$widgets = DB::collection('widgets')->where('sku', 'exists', true)->get();
		$this->assertEquals(3, count($widgets));
		$this->assertEquals(0, $widgets[0]['sku']);

		$widgets = DB::collection('widgets')->where('sku', 'exists', false)->get();
		$this->assertEquals(1, count($widgets));
		$this->assertEquals('large', $widgets[0]['type']);

		$widgets = DB::collection('widgets')->where('amount', 'type', 2)->get();
		$this->assertEquals(1, count($widgets));
		$this->assertEquals('med', $widgets[0]['type']);

		$widgets = DB::collection('widgets')->where('sku', 'mod', array(2, 1))->get();
		$this->assertEquals(1, count($widgets));
		$this->assertEquals('med', $widgets[0]['type']);

		$widgets = DB::collection('widgets')->where('tags', 'all', array('foo', 'baz'))->get();
		$this->assertEquals(2, count($widgets));

		$widgets = DB::collection('widgets')->where('tags', 'size', 3)->get();
		$this->assertEquals(1, count($widgets));

		$widgets = DB::collection('widgets')->where('tags', 'size', 6)->get();
		$this->assertEquals(0, count($widgets));
	}

}
