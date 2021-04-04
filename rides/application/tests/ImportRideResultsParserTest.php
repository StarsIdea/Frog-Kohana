<?php

/**
 * @group era
 */
class ImportRideResultsParserTest extends Kohana_Unittest_TestCase {

	public function setUp()
	{
		parent::setUp();

		$abs_path_to_file = Kohana::find_file('tests/fixtures', 'master_2_sheets', 'xls');
		$reader = new Spreadsheet_Excel_Reader();
		$reader->setOutputEncoding('CP1251');
		$reader->read($abs_path_to_file);
		
		$this->parser = new Import_RideResults_Parser($reader, array());
		$this->parser->sheet(0)->parse();
	}

	public function test_get_offset_and_length()
	{
		$array = array(
			9  => array(null, null, 'Ride Veterinarian', '25'),
			10 => array(null, null, 'Ride Distance', '25'),
			// empty
			12 => array(null, 'Placing'),
			13 => array(null, 1, 'Joe Blow', 1337, 'Daisy Lily', 9999, '2:02:29', 220, 400, 720),
			14 => array(null, 2, 'Row Blow', 1338, 'Daisy Lily', 9999, '2:02:29', 220, 400, 720),
			// empty
			16 => array(null, 'DNF', 'Sr User 1'),
			17 => array(null, null, 'Sr User 2'),
			// empty
			20 => array(null, null, 'single row with nothing'),
			// empty
			22 => array(null, 'DNF', 'Jr User 1'), // index 8
			23 => array(null, null, 'Jr User 2'), // index 8
			24 => array(null, null, 'Jr User 3'), // index 8
		);

		$block_indexes = Import_RideResults_Parser::get_block_indexes($array);
		$this->assertEquals(array(array(9, 10), array(12, 14), array(16, 17), array(20, 20), array(22, 24)), $block_indexes);
		$this->assertEquals(array(2, 3), Import_RideResults_Parser::get_offset_and_length($array, "Placing"));
		$this->assertEquals(array(5, 2), Import_RideResults_Parser::get_offset_and_length($array, "DNF"));
		$this->assertEquals(array(8, 3), Import_RideResults_Parser::get_offset_and_length($array, "DNF", 2));
	}

	public function test_parse_ride_with_spreadsheet_mocked()
	{
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
						12 => array(null, 'Placing'),
						13 => array(null, 1, 'Joe Blow', 1337, 'Daisy Lily', 9999, '2:02:29', 220, 400, 720),
						14 => array(null, 2, 'Row Blow', 1338, 'Daisy Lily', 9999, '2:02:29', 220, 400, 720),
					),
				),
			),
			'boundsheets' => array(
				array('name' => '25'),
			),
		);
		$parser = new Import_RideResults_Parser($reader, array());
		$parser->parse();
		$expected = array(
			'date' => 'May 21,2011',
			'name' => '2011 Horseshoe Lake Challenge Day 1',
			'location' => 'Horseshoe Lake, SK',
			'manager' => 'Bob',
			'secretary' => 'Frank',
			'veterinarian' => 'Joe',
			'distance' => '25',
		);
		$this->assertEquals($expected, $parser->get('ride'));
		$this->assertEquals(2, count($parser->get('results_senior')));
	}
	
	public function test_parse_ride()
	{
		$this->assertEquals(array(
			'date' => 'May 21,2011',
			'name' => '2011 Horseshoe Lake Challenge Day 1',
			'location' => 'Horseshoe Lake, SK',
			'manager' => 'Bob',
			'secretary' => 'Frank',
			'veterinarian' => 'Joe',
			'distance' => '25',
		), $this->parser->get('ride'));
	}

	public function test_parse_event()
	{
		$this->assertEquals(array(
			'name' => '25 miles',
			//'num_starters' => '',
			//'num_completions' => '',
			// 'sanctioned' => '',
		), $this->parser->get('event'));
	}
	
	public function test_parse_results_senior()
	{
		$results = $this->parser->get('results_senior');

		$this->assertEquals(array(
			'rider_name' => 'Kerri Jo Stewart',
			'member_id' => 'N/A',
			'equine_name' => 'Darginka',
			'equine_id' => 'N/A',
			'placing' => '1',
			'time' => '02:46:49',
			'weight' => '259',
			'miles' => '25',
			'points' => null,
			'bc' => 1,
			'bc_points' => null,
			'bc_score' => '670',
			'pull' => null,
			'pull_reason' => null,

		), $results[0]);
		$this->assertEquals(25, count($results));
	}

	public function test_parse_dnf_senior()
	{
		$results = $this->parser->get('dnf_senior');
		$this->assertEquals(5, count($results));
		$this->assertEquals(array(
			'rider_name' => 'Lorrance Aplin',
			'member_id' => '1652',
			'equine_name' => NULL,
			'equine_id' => NULL,
			'placing' => NULL,
			'time' => NULL,
			'weight' => NULL,
			'miles' => '25',
			'points' => NULL,
			'bc' => 0,
			'bc_points' => NULL,
			'bc_score' => NULL,
			'pull' => 1,
			'pull_reason' => 'RCY',
		), $results[0]);
	}
	
	public function test_parse_results_junior()
	{
		$results = $this->parser->sheet(0)->get('results_junior');

		$this->assertEquals(array(
			'rider_name' => 'Colton MacLeod',
			'member_id' => '1707',
			'equine_name' => 'Cherokee Spirit',
			'equine_id' => '3439',
			'placing' => '1',
			'time' => '02:54:33',
			'weight' => '202',
			'miles' => '25',
			'points' => null,
			'bc' => 1,
			'bc_points' => null,
			'bc_score' => '690',
			'pull' => null,
			'pull_reason' => null,
		), $results[0]);

		$this->assertEquals('Katlyn Janz', $results[3]['rider_name']);
	}
	
	public function test_parse_junior_dnf_should_return_empty()
	{
		$results = $this->parser->get('dnf_junior');
		$expected = array();
		$this->assertEquals($expected, $results);
	}

	//public function test_validate_member_doesnt_exist(){}

	public function provider_test_get_bounds()
	{
		return array(
			array('C4', array(4,3)),
			array('C44', array(44,3)),
			array('J4', array(4,10)),
		);
	}

	/**
     * @dataProvider provider_test_get_bounds
     */
	public function test_get_bounds($string, $expected)
	{
		$this->assertEquals($expected, Import_RideResults_Parser::get_bounds($string));
	}

}
