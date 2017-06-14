<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

require_once($CFG->libdir.'/formslib.php');

class documents_edit_form extends moodleform{

    protected function definition()
    {
        global $CFG,$DB;

        $mform = &$this->_form;
        $entry = $this->_customdata['entry'];
        // Tittle
        $mform->addElement('text', 'name', get_string('fieldName', 'local_documents'), array('placeholder'=>get_string('inputnamedocument','local_documents')));
        $mform->setType('name', PARAM_RAW);
        $mform->addRule('name',null, 'required', null, 'client');

        //Category
        /*
            Get All category where condition
            is_active = 1
            call column: 'id','name'
        */

        $categories = $DB->get_records_list('mod_category_document','is_active',array(1),null,'id,name');
        foreach($categories as $id => $cate){
            $options[$id] = $cate->name;
        }

        $mform->addElement('selectwithlink', 'category_id',get_string('fieldCategory', 'local_documents'), isset($options) ? $options : array(), null,
            array('link' => new moodle_url('/local/documents/category.php',array('action'=>'add')), 'label' => get_string('categorycreate','local_documents')));
        $mform->setType('category_id',PARAM_INT);
        $mform->addRule('category_id',null,'required',null,'client');

        //File Image
        $mform->addElement('filemanager', 'picture_filemanager', get_string('filedImage','local_documents'), null,
            array(
                'subdirs' => false,
                'maxbytes'=> 3145728, // 3MB
                'areamaxbytes' => 3145728, //3MB
                'maxfiles' => 1,
                'accepted_types' => array('.png','.jpg')
            )
        );
       // $mform->addRule('picture',null,'required',null,'client');

        //URL Document
        $mform->addElement('text','url',get_string('fieldUrl','local_documents'),array('placeholder' => get_string('inputurldocument','local_documents')));
        $mform->setType('url',PARAM_RAW);

        // $mform->addHelpButton('fieldUrl',);

        // File Documents
        $mform->addElement('filemanager','file_filemanager',get_string('filedocument','local_documents'),null,
            array(
                'subdirs' => false,
                'maxbytes'=> 10485760 , // 10MB
                'areamaxbytes' => 10485760 , //10MB
                'maxfiles' => 1,
                'accepted_types' => array('.pdf','.txt','.doc','.docx','.png','.jpg','.xls','.xlsx')
            )
        );
       // $mform->addRule('file',null,'required','client');

        //Description
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
        if(isset($entry) && isset($entry->id)){
            $mform->addElement('hidden', 'id');
            $mform->setType('id', PARAM_INT);
            $mform->setDefault('id',$entry->id);
        }

        //Captcha
        //$mform->addElement('recaptcha', 'fieldCaptcha');
        $this->add_action_buttons();

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->setDefault('action', '');

        // Buttons
        //normally you use add_action_buttons instead of this code
        /*
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = &$mform->createElement('reset', 'resetbutton', get_string('revert'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
        */
    }

}