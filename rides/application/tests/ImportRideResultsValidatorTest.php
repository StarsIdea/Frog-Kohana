<?php

/**
 * @group era
 * @group database
 */
class ImportRideResultsValidatorTest extends Kohana_Unittest_TestCase {

	public function setUp()
	{
		parent::setUp();

		DB::query(NULL, 'DELETE FROM imports WHERE 1;')->execute();
		DB::query(NULL, 'TRUNCATE TABLE `event_results`;')->execute();

		$reader = (object)array(
			'sheets' => array(
				0 => array(
					'cells' => array(
						4  => array(null, null, 'Ride Date', 'May 21,2011', null, null, 'Number of Starters:', 13),
						5  => array(null, null, 'Ride Name', 'Horseshoe Lake Challenge Day 1', null, null, 'Number of Completions', 12),
						6  => array(null, null, 'Ride Location', 'Horseshoe Lake, SK'),
						7  => array(null, null, 'Ride Manager', 'Bob', null, null, 'BC Horse', 'Darginka', 'Sr'),
						8  => array(null, null, 'Ride Secretary', 'Frank', null, null, 'BC Score', 670, 'Sr'),
						9  => array(null, null, 'Ride Veterinarian', 'Joe'),
						10 => array(null, null, 'Ride Distance', '25'),
						// empty
						12 => array(null, 'Placing'),
						13 => array(null, 1, 'Joe Blow', 9991, 'Daisy Lily', 9999, '2:02:29', 220, 400, 720),
						14 => array(null, 2, 'Row Blow', 9992, 'Daisy Lily', 9999, '2:02:29', 220, 400, 720),
					),
				),
			),
			'boundsheets' => array(
				array('name' => '25'),
			),
		);

		$parser = new Import_RideResults_Parser($reader, array());
		$parser->parse();

		//var_dump('RESULTS SR',$parser->valid(), $parser->get('results_senior')); die();

		$this->mapper = new Import_RideResults_Validator($parser);
		$this->mapper->check();
	}

	public function test_errors_has_missing_members()
	{
		$expected = array(
			'Sheet "25": Missing members 9991, 9992'
		);
		$this->assertEquals($expected, $this->mapper->errors);
	}

}
