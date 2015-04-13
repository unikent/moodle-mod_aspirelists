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
        
        if (method_exists($this, 'standard_intro_elements')) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // -------------------------------------------------------

        $options = array();
        $options['Meta']['all'] = 'All Lists';

        // Build API object.
        $api = new \mod_aspirelists\core\API();

        // Extract the shortnames.
        $matches = $api->extract_shortcodes($COURSE->shortname);

        // Grab categories for each shortname.
        foreach ($matches as $shortname) {
            // Grab lists.
            $lists = $api->get_lists($shortname);

            // Build options.
            foreach ($lists as $list) {
                $campus = $list->get_campus();

                $categories = $list->get_categories();
                foreach ($categories as $category) {
                    $categoryoptions = $this->get_category_options($shortname, $category);
                    $options = array_merge_recursive($options, $categoryoptions);
                }
            }
        }

        $mform->addElement('selectgroups', 'category', 'Category', $options, array(
            'size' => 20
        ));

        $mform->addElement('select', 'item', 'Item (optional)', array('invalid' => 'Select a category to view options'), array(
            'size' => 20
        ));

        // Add standard buttons, common to all modules.
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
        return;
    }

    /**
     * Returns an array of options for categories.
     */
    private function get_category_options($shortname, $category, $depth = 1) {
        $id = $category->get_id();
        $campus = $category->get_campus();

        if (!isset($options[$campus])) {
            $options[$campus] = array();
        }

        $displayname = str_repeat('--', $depth) . " {$shortname}: ";
        $displayname .= $category->get_name();

        $options[$campus]["{$campus}/$id"] = $displayname;

        foreach ($category->get_parents() as $parent) {
            $options = array_merge_recursive($options, $this->get_category_options($shortname, $parent, $depth + 1));
        }

        return $options;
    }
}
