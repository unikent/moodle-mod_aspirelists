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

defined('MOODLE_INTERNAL') || die;

function xmldb_aspirelists_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2012062001) {

    	$table = new xmldb_table('aspirelists');
    	$field = new xmldb_field('category', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 'all', 'introformat');
		$dbman->add_field($table, $field);

		upgrade_mod_savepoint(true, 2012071001, 'aspirelists');
    }

    if ($oldversion < 2015041300) {
        // Define field item to be added to aspirelists.
        $table = new xmldb_table('aspirelists');
        $field = new xmldb_field('item', XMLDB_TYPE_TEXT, null, null, null, null, null, 'name');

        // Conditionally launch add field item.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Aspirelists savepoint reached.
        upgrade_mod_savepoint(true, 2015041300, 'aspirelists');
    }

    return true;
}