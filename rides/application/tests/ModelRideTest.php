<?php

/**
 * Test case for Model_Ride
 *
 * @group era
 */
class Model_RideTest extends Kohana_Unittest_TestCase {

	public function provider_valid_date()
	{
		return array(
			array('May 21/11', false),
			array('May 21/2011', false),
			array('May 21, 2011', true),
			array('May 21,2011', true),
			array('05/11/2011', true),
			array('2011-05-21', true),
			array('2011-21-05', false),
		);
	}

	/**
	 * @dataProvider provider_valid_date
	 */
	public function test_valid_date($date, $expected)
	{
		$actual = Model_Ride::valid_date($date);
		$this->assertEquals($expected, $actual);
	}

}