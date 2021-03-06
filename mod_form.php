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

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_aspirelists_mod_form extends moodleform_mod
{
    /**
     *
     */
    public function definition() {
        global $CFG, $COURSE, $PAGE;

        $PAGE->requires->jquery();
        $PAGE->requires->js('/mod/aspirelists/mod_form.js');

        $config = get_config('aspirelists');

        $mform =& $this->_form;

        // -------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array(
            'size' => '48'
        ));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        // -------------------------------------------------------

        // Build API object.
        $api = new \mod_aspirelists\core\API();

        // Extract the shortnames.
        $matches = $api->extract_shortcodes($COURSE->shortname);

        // Grab categories for each shortname.
        $options = array();
        foreach ($matches as $shortname) {
            // Grab lists.
            $lists = $api->get_lists($shortname);

            // Build options.
            foreach ($lists as $list) {
                $campus = $list->get_campus();

                $categories = $list->get_categories();
                foreach ($categories as $category) {
                    $options = $this->build_category_options($options, $shortname, $category);
                }
            }
        }

        $mform->addElement('selectgroups', 'category', 'Category (optional)', $options, array(
            'size' => 15
        ));

        $category = optional_param('category', false, PARAM_RAW);
        if ($category) {
            $options = static::get_item_options($category);
            $mform->addElement('select', 'item', 'Item (optional)', $options, array(
                'size' => min(count($options), 5)
            ));
        } else {
            $mform->addElement('select', 'item', 'Item (optional)', array('invalid' => 'Select a category'), array(
                'size' => 5
            ));
        }

        $mform->registerNoSubmitButton('updateitems');
        $mform->addElement('submit', 'updateitems', 'Update Available Items');

        // Add standard buttons, common to all modules.
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Returns an array of options for categories.
     */
    private function build_category_options($existing, $shortname, $category, $depth = 1) {
        $id = $category->get_id();
        $campus = $category->get_campus();

        if (!isset($existing[$campus])) {
            $existing[$campus] = array();
        }

        // Build display name.
        $displayname = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth);
        $displayname .= $category->get_name();

        // Conditionally add the new item to the array.
        if (!isset($existing[$campus]["{$campus}/$id"])) {
            $existing[$campus]["{$campus}/$id"] = $displayname;
        }

        // Loop any children.
        foreach ($category->get_parents() as $parent) {
            $existing = $this->build_category_options($existing, $shortname, $parent, $depth + 1);
        }

        return $existing;
    }

    /**
     * Load options for a select when we have a category.
     */
    public static function get_item_options($category) {
        list($campus, $id) = explode('/', $category);

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

        return $data;
    }
}
