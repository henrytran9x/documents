<?php

require(__DIR__.'/../../config.php');
//enable debug krumo
require_once($CFG->dirroot.'/krumo/class.krumo.php');

//require Lib
require_once('lib.php');
require_once('locallib.php');
require_once('edit_form.php');
require_once('category_form.php');
require_once('classes/document.php');

require_login();

// Check capability document view
$context = context_system::instance();
require_capability('local/documents:view',$context);

global $USER;

//Param
$action = required_param('action',PARAM_ALPHA);
$id     = optional_param('id', 0, PARAM_INT);


// Set Page Layout
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);
$PAGE->set_url('/local/documents/edit.php',array('action' => $action,'id'=>$id));

$entry  = new stdClass();
$entry->id = null;

$returnurl = new moodle_url('/local/documents/index.php');

// If action is add, we ignore $id to avoid any further problems.
if (!empty($id) && $action == 'add') {
    $id = null;
}
if ($id) {
    if (!$entry = new document_entry($id)) {
        print_error('wrongentryid', 'local_documents');
    }
    //Update summary
    $entry->summary  = clean_text($entry->summary, $entry->format);

    $entry = file_prepare_standard_filemanager(
        $entry,
        'picture',
        $entry->options_picture(),
        $context,
        'local_documents',
        'picture',
        $entry->id);
    $entry = file_prepare_standard_filemanager(
        $entry,
        'file',
        $entry->options_picture(),
        $context,
        'local_documents',
        'attachment',
        $entry->id);

}
$doc_editform  = new documents_edit_form(null,compact('entry'));
$render  = null;

$entry->action = $action;
// Set defaults.
$doc_editform->set_data($entry);

// Process form call
if($doc_editform->is_cancelled()){
   redirect($returnurl);
}
// If data submitted, then process and store.
else if($data = $doc_editform->get_data()) {
    switch ($action) {
        case 'add':
            $doc_entry = new document_entry(null, $data, $doc_editform);
            $doc_entry->add();
            $doc_entry->edit($data,$doc_editform);
            $message = 'Created <strong>'.$doc_entry->name.'</strong> successfully';
            break;
        case 'edit':
            if (empty($entry->id)) {
                throw new coding_exception('Error ! The ID <b>'.$id.'</b> not existed in database');
            }

            $entry->edit($data,$doc_editform);
            $message = 'Update <strong>'.$entry->name.'</strong> successfully';
            break;
    }
    redirect($returnurl,$message);
}

// Display GUI
if(isset($action) && !empty($action)){

    switch($action){
        case 'add':
            $PAGE->set_title(get_string('txtadddocument','local_documents'));
            $PAGE->set_heading(get_string('txtadddocument','local_documents'));
            $PAGE->navbar->add(get_string('txtdocument','local_documents'),'/local/documents');
            $PAGE->navbar->add(get_string('txtadddocument','local_documents'));
            //Call Form
            ob_start();
            $doc_editform->display();
            $render = ob_get_contents();
            ob_end_clean();
            break;
        case 'edit':
            $id = required_param('id', PARAM_INT);
            if($entry->id) {
                $PAGE->set_title('Edit ' . $entry->name);
                $PAGE->set_heading('Edit ' . $entry->name);
                $PAGE->navbar->add(get_string('listdocument', 'local_documents'), 'index.php');
                $PAGE->navbar->add('Edit ' . $entry->name);
                $entry->summary = array(
                    'text'   => $entry->summary,
                    'format' => FORMAT_HTML,
                );

                $doc_editform->set_data($entry);
                ob_start();
                $doc_editform->display();
                $render = ob_get_contents();
                ob_end_clean();
            }
            else{
                throw new coding_exception('Error ! The ID <b>'.$id.'</b> not existed in database');
            }

            break;
        case 'delete':
            $id = required_param('id',PARAM_INT);
            $process = optional_param('process',0,PARAM_BOOL);
            $param_url = array('action' => 'delete');
            $param_url['id'] = $id;
            $PAGE->set_title(get_string('txtDeleteDocument','local_documents'));
            $PAGE->set_heading(get_string('txtDeleteDocument','local_documents'));
            $PAGE->navbar->add(get_string('listdocument','local_documents'),'index.php');
            $PAGE->navbar->add(get_string('txtDeleteDocument','local_documents'));
            //Check record exsited by cate_id
            if($entry->id)
            {
                //If process return 1 , continue go
                if($process) {
                    // Process delete
                    if($entry->delete());
                        redirect($CFG->wwwroot.'/local/documents/index.php','Delete successfully <br/> <ul><li>'.$entry->name.'</li></ul> ',2000);
                }
                $list_document = '<ul><li>'.$entry->name.'</li></ul>';
                $message = get_string('msgdeletedocument','local_documents').'<br/><br/>'.$list_document;
                $param_url['process'] = true;
                $render = $OUTPUT->confirm($message, new single_button(new moodle_url('/local/documents/edit.php', $param_url), count($entry) > 1 ? get_string('deleteall','local_documents') : get_string('delete'), 'get'),'/local/documents/index.php');
            }
            else{
                // This cate_id not existed in data
                throw new coding_exception('Error ! The ID '.$id.' not existed in database');
            }
            break;
    }
}
else{
    // Render to template category
    // Get Table List data category document
    $tpldata = array(
        'action_url' => $PAGE->url,
        'search_value' => isset($_POST['search']) && !empty($_POST) ? $_POST['search'] : '',
        'table_documents' => get_table_documents(),
    );
    $render = $OUTPUT->render_from_template('local_documents/documents', $tpldata);
    echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
}


echo $OUTPUT->header();
    echo isset($render) ? $render : '';
echo $OUTPUT->footer();