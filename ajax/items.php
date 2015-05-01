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

define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../../config.php');

$PAGE->set_context(\context_system::instance());
$PAGE->set_url('/mod/aspirelists/ajax/items.php');

require_login();
require_sesskey();

$id = required_param('id', PARAM_RAW);
list($campus, $id) = explode('/', $id);

$campus = strtolower($campus);
$campus = $campus == 'medway' ? 'medway' : 'canterbury';

$api = new \mod_aspirelists\core\API();
$list = $api->get_list($id, $campus);
$items = $list->get_items();

$data = array();
foreach ($items as $item) {
	$name = $item->get_name();
	if ($name) {
		$data[$item->get_url()] = $name;
	}
}

echo $OUTPUT->header();
echo json_encode($data);
