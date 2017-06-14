<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/filelib.php');

class document_entry implements renderable{

    public $id ;
    public $name;
    public $summary = '';
    public $is_active = 1;
    public $created_date;
    public $created_by;
    public $modified_date;
    public $format = 1;
    public $modified_by;
    public $sort_order;
    public $category_id;
    public $url = '';
    public $file;
    public $picture;

    /** @var StdClass Data needed to render the entry */
    public $renderable;

    function __construct($id=null, $params=null, $form=null)
    {
        global $DB,$PAGE,$CFG;
        if(!empty($id)){
            $object = $DB->get_record('mod_documents', array('id' => $id));
            foreach($object as $var => $value){
                $this->$var = $value;
            }
        }else if (!empty($params) && (is_array($params) || is_object($params))) {
            foreach ($params as $var => $val) {
                $this->$var = $val;
            }
        }
        if(isset($params->summary)) {
            $this->summary = clean_text($params->summary['text'], isset($params->summary['format']) ? $params->summary['format'] : $this->format);
        }
        $this->form = $form;
    }

    public function add(){
        global $CFG, $USER, $DB;
        unset($this->id);
        $this->summary        = clean_text($this->summary, $this->format);
        $this->created_by     = (empty($this->created_by)) ? $USER->id : $this->created_by;
        $this->created_date   = (empty($this->created_date)) ? time() : $this->created_date;
        $this->modified_by    = (empty($this->modified_by)) ? $USER->id : $this->modified_by;
        $this->modified_date  = time();
        // Insert the new document entry.
        $this->id = $DB->insert_record('mod_documents', $this);
    }
    public function options_picture(){
        return array(
            'subdirs' => false,
            'maxbytes'=> 3145728, // 3MB
            'areamaxbytes' => 3145728, //3MB
            'maxfiles' => 1,
            'accepted_types' => array('.png','.jpg')
        );
    }

    public function options_file(){
        return array(
            'subdirs' => false,
            'maxbytes'=> 10485760 , // 10MB
            'areamaxbytes' => 10485760 , //10MB
            'maxfiles' => 1,
            'accepted_types' => array('.pdf','.txt','.doc','.docx','.png','.jpg','.xls','.xlsx')
        );
    }

    public function edit($params=array(), $form=null, array $attachmentoptions_picture = null,array $attachmentoptions_file = null){
        global $CFG, $DB;

        $sitecontext = context_system::instance();
        $entry = $this;

        $this->form = $form;

        foreach ($params as $var => $val) {
            $entry->$var = $val;
        }


        $entry->summary = isset($params->summary['text']) ? clean_text($params->summary['text'],$params->summary['format']) : $this->summary;
        $entry->modified_date = time();
        $entry = file_postupdate_standard_filemanager(
            $entry,
            'picture',
            isset($attachmentoptions_picture) ? $attachmentoptions_picture : $this->options_picture(),
            $sitecontext,
            'local_documents',
            'picture',
            $entry->id);

        $entry = file_postupdate_standard_filemanager(
            $entry,
            'file',
            isset($attachmentoptions_file) ? $attachmentoptions_file : $this->options_file(),
            $sitecontext,
            'local_documents',
            'attachment',
            $entry->id);
        // Update record.
        $DB->update_record('mod_documents', $entry);
    }

    public function delete(){
        global $DB;
        if($DB->delete_records('mod_documents', array('id' => $this->id))) {
            //remove file
            $this->delete_attachments();
            return true;
        }
    }

    /**
     * Deletes all the user files in the attachments area for an entry
     *
     * @return void
     */
    public function delete_attachments($contextid = 1) {
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'mod_documents', 'attachment', $this->id);
        $fs->delete_area_files($contextid, 'mod_documents', 'picture', $this->id);

    }

}