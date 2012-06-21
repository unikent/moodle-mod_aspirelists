<?php

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_aspirelists_mod_form extends moodleform_mod {

	function definition() {
        global $CFG, $OUTPUT;

        $mform =& $this->_form;

        $config = get_config('streamingvideo');

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $this->add_intro_editor();

        //-------------------------------------------------------

        // add standard buttons, common to all modules
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
        return;
    }
}