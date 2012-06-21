<?php

class restore_streamingvideo_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

    $paths = array();
    $paths[] = new restore_path_element('streamingvideo', '/activity/streamingvideo');

    // Return the paths wrapped into standard activity structure
    return $this->prepare_activity_structure($paths);
    }

    protected function process_streamingvideo($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the streamingvideo record
        $newitemid = $DB->insert_record('streamingvideo', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function after_execute() {
        // Add url related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_streamingvideo', 'intro', null);
    }
}