<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}
require_once($CFG->libdir.'/formslib.php');

class category_edit_form extends moodleform{

    protected function definition()
    {
        global $CFG, $DB, $OUTPUT;
        $mform =& $this->_form;
        $category = $this->_customdata['category'];
        // Tittle
        $mform->addElement('text', 'name', get_string('fieldnameCate', 'local_documents'), array('placeholder'=>get_string('inputnamecategory','local_documents')));
        $mform->setType('name', PARAM_RAW);
        $mform->addRule('name',null, 'required', null, 'client');

        // Description

        $mform->addElement('editor', 'summary', get_string('fieldDescription', 'local_documents'));
        $mform->setType('summary', PARAM_RAW);

        //Status
        $mform->addElement('selectyesno', 'is_active', get_string('fieldstatus', 'local_documents'));
        $mform->setDefault('is_active','1');

        //Ordering
        $mform->addElement('text','sort_order',get_string('fieldOrder','local_documents'));
        $mform->setType('sort_order',PARAM_INT);
        $mform->setDefault('sort_order',1);

        //ID
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);


        $this->add_action_buttons();

        /*
        // Buttons
        //normally you use add_action_buttons instead of this code
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = &$mform->createElement('reset', 'resetbutton', get_string('revert'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
        */
    }

}




