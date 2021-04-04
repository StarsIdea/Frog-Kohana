<?php

/**
 * @group era
 * @group database
 */
class ImportRideResultsMapperTest extends Unittest_Database_TestCase {

	public function setUp()
	{
		parent::setUp();

		// setup members present in master_2_sheets spreadsheet
		$members = array(
			1786, 1862, 1199, 1867, 1837, 1412, 1002, 1580, 1869, 1870, 1727, 1846, 1776, 1757, 1817, 1871, 1652, 1855, 1256, 1707, 1788, 1651, 1758,
			1772, 1773, 1012, 1624, 1676, 1600, 1563, 1200, 1570, 1007, 1604
		);

		foreach($members as $id) {
			$member = new Model_Member();
			$member->id = $id;
			$member->first_name = "test";
			$member->last_name = "test";
			$member->save();
		}

		$abs_path_to_file = Kohana::find_file('tests/fixtures', 'master_2_sheets', 'xls');
		$reader = new Spreadsheet_Excel_Reader();
		$reader->setOutputEncoding('CP1251');
		$reader->read($abs_path_to_file);
		
		$parser = new Import_RideResults_Parser($reader, array());
		$parser->parse();

		$file = array('tmp_name' => $abs_path_to_file, 'size'=> 1000, 'name' => 'Test file', 'type' => 'xls');
		$this->mapper = new Import_RideResults_Mapper($parser, $file);
		$this->result = $this->mapper->save();
	}

	public function test_save_successful_overall()
	{
		$this->assertTrue($this->result);
	}

	public function test_import_saved()
	{
		$this->assertTrue((bool)DB::select()->from('imports')->where('name','=','Test file')->execute()->count());
	}

	public function test_ride_saved()
	{
		$this->assertTrue((bool)DB::select()->from('rides')->where('name','=','2011 Horseshoe Lake Challenge Day 1')->execute()->count());
	}

	public function test_25_miles_senior_results_saved()
	{
		$this->assertEquals(25, DB::select()->from('event_results')
			->where('event_type_id', '=', 1)
			->where('pull', 'IS', NULL)
			->execute()->count());
	}

	public function test_25_miles_senior_dnf_saved()
	{
		$this->assertEquals(5, DB::select()->from('event_results')
			->where('event_type_id', '=', 1)
			->where('pull', '=', 1)
			->execute()->count());
	}

	public function test_25_miles_junior_results_saved()
	{
		$this->assertEquals(4, DB::select()->from('event_results')
			->where('event_type_id', '=', 2)
			->where('pull', 'IS', NULL)
			->execute()->count());
	}

	public function test_25_miles_junior_dnf_saved()
	{
		$this->assertEquals(0, DB::select()->from('event_results')
			->where('event_type_id', '=', 2)
			->where('pull', '=', 1)
			->execute()->count());
	}

	public function test_50_miles_senior_results_saved()
	{
		$this->assertEquals(10, DB::select()->from('event_results')
			->where('event_type_id', '=', 3)
			->where('pull', 'IS', NULL)
			->execute()->count());
	}

	public function test_50_miles_jr_results_saved()
	{
		$this->assertEquals(1, DB::select()->from('event_results')
			->where('event_type_id', '=', 4)
			->where('pull', 'IS', NULL)
			->execute()->count());
	}

}
