<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

/**
 * Tests
 */
class mod_aspirelists_parser_tests extends \advanced_testcase
{
    /**
     * Test the parser.
     */
    public function test_parser() {
        global $CFG, $DB;

        $this->resetAfterTest();

        $json = file_get_contents(dirname(__FILE__) . "/fixtures/api.json");

        $parser = new \mod_aspirelists\core\parser('http://resourcelists.kent.ac.uk/', $json);

        $this->assertEquals(array(
            '3',
            '53304cb6f3d4d'
        ), $parser->get_timeperiods());

        $this->assertEquals(array(
            '31F89776-6BEA-1DD2-E4C6-C3CA796D173D',
            '39399749-E118-07A7-456E-80F55700F2C0'
        ), $parser->get_all_lists());

        $list = $parser->get_list('31F89776-6BEA-1DD2-E4C6-C3CA796D173D');
        $this->assertEquals('3', $list->get_time_period());

        $list = $parser->get_list('39399749-E118-07A7-456E-80F55700F2C0');
        $this->assertEquals('53304cb6f3d4d', $list->get_time_period());

        $this->assertEquals(array(
            '31F89776-6BEA-1DD2-E4C6-C3CA796D173D'
        ), $parser->get_lists('3'));
    }
}