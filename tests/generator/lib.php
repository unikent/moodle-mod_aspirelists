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

/**
 * mod_aspirelists data generator.
 *
 * @package mod_aspirelists
 * @category test
 * @copyright 2014 Jake Blatchford <J.Blatchford@kent.ac.uk>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * mod_aspirelists data generator class.
 *
 * @package mod_aspirelists
 * @category test
 * @copyright 2014 Jake Blatchford <J.Blatchford@kent.ac.uk>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_aspirelists_generator extends testing_module_generator {
    public function create_instance($record = null, array $options = null) {
        $record = (array)$record;
        $defaultsettings = array(
            'name' => 'Test Aspirelist',
            'intro' => 'Introduction',
            'introformat' => 0
        );
        foreach ($defaultsettings as $name => $value) {
            if (!isset($record[$name])) {
                $record[$name] = $value;
            }
        }
        return parent::create_instance((object)$record, (array)$options);
    }
}
