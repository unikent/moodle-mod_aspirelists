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
    	$field = new xmldb_field('category', XMLDB_TYPE_CHAR, '255', XMLDB_INT, XMLDB_NOTNULL, null, 'all', 'introformat');
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

    if ($oldversion < 2016030400) {
        // Define table aspirelists_fimexport_sv to be created.
        $table = new xmldb_table('aspirelists_fimexport_sv');

        // Adding fields to table aspirelists_fimexport_sv.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('username', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table aspirelists_fimexport_sv.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('k_user', XMLDB_KEY_UNIQUE, array('username'));

        // Conditionally launch create table for aspirelists_fimexport_sv.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table aspirelists_fimexport_mv to be created.
        $table = new xmldb_table('aspirelists_fimexport_mv');

        // Adding fields to table aspirelists_fimexport_mv.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('username', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('attribute_name', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null);
        $table->add_field('string_value', XMLDB_TYPE_CHAR, '255', null, null, null, null);

        // Adding keys to table aspirelists_fimexport_mv.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for aspirelists_fimexport_mv.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Aspirelists savepoint reached.
        upgrade_mod_savepoint(true, 2016030400, 'aspirelists');
    }

    if ($oldversion < 2016030600) {

        // Changing type of field string_value on table aspirelists_fimexport_mv to text.
        $table = new xmldb_table('aspirelists_fimexport_mv');
        $field = new xmldb_field('string_value', XMLDB_TYPE_TEXT, null, null, null, null, null, 'attribute_name');

        // Launch change of type for field string_value.
        $dbman->change_field_type($table, $field);

        // Aspirelists savepoint reached.
        upgrade_mod_savepoint(true, 2016030600, 'aspirelists');
    }

    return true;
}
