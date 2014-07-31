<?php

class restore_aspirelists_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

    $paths = array();
    $paths[] = new restore_path_element('aspirelists', '/activity/aspirelists');

    // Return the paths wrapped into standard activity structure
    return $this->prepare_activity_structure($paths);
    }

    protected function process_aspirelists($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->category = 'all';

        // insert the aspirelists record
        $newitemid = $DB->insert_record('aspirelists', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function after_execute() {
        // Add url related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_aspirelists', 'intro', null);
    }
}