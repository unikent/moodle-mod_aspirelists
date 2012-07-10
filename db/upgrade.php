<?php

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

    // Moodle v2.1.0 release upgrade line
    // Put any upgrade step following this

    // Moodle v2.2.0 release upgrade line
    // Put any upgrade step following this

    return true;
}