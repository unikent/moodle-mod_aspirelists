<?php 

defined('MOODLE_INTERNAL') || die();


/**
 * Streamingvideo conversion handler. This resource handler is called by moodle1_mod_resource_handler
 */
class moodle1_mod_aspirelists_handler extends moodle1_mod_handler {

	/** @var moodle1_file_manager instance */
    protected $fileman = null;

    public function get_paths() {
        return array(
            new convert_path('aspirelists', '/MOODLE_BACKUP/COURSE/MODULES/MOD/ASPIRELISTS',
                    array(
                        'renamefields' => array(
                            'summary' => 'intro',
                        ),
                        'newfields' => array(
                            'introformat' => FORMAT_MOODLE,
                        ),
                    )
            )
    
        );
    }

}