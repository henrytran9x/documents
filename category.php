<?php
require(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/krumo/class.krumo.php');
require_once('lib.php');
require_once('locallib.php');
require_once('category_form.php');

$action  = optional_param('action',null,PARAM_ALPHA);
require_login();
// Check capability document view
$context = context_system::instance();
require_capability('local/documents:view',$context);

// Set Page Layout
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);
global $USER;
$render = null;

$mform = new category_edit_form();
    // If data submitted, then process and store.
    if($mform->is_cancelled()){
        //Form submit cancel
    }
    else if($data = $mform->get_data()) {
        if($data->id){
            $data->summary = $data->summary['text'];
            if($DB->update_record('mod_category_document',$data))
            redirect($CFG->wwwroot.'/local/documents/category.php','Update '.$data->name.' successfully',2000);
        }
        else{
            $PAGE->set_url('/local/documents/category.php');
            // ADD new record into database
            $data->summary = $data->summary['text'];
            $data->created_date = time();
            $data->created_by   = $USER->id;
            $data->modified_date = time();
            $data->modified_by  = $USER->id;
            if($DB->insert_record('mod_category_document', $data))
            redirect($CFG->wwwroot.'/local/documents/category.php','Created '.$data->name.' successfully',2000);
        }
    }
// Handler check Action
if(!isset($action) && empty($action))
{
    $PAGE->set_url('/local/documents/category.php');
    $PAGE->set_title('List Category');
    $PAGE->set_heading('List Category');
    $PAGE->navbar->add('List Category');
    // Render to template category
    // Get Table List data category document
    $tpldata = array(
        'action_url' => $PAGE->url,
        'search_value' => isset($_POST['search']) && !empty($_POST) ? $_POST['search'] : '',
        'table_category' => get_table_category_document(),
    );
    $render = $OUTPUT->render_from_template('local_documents/category', $tpldata);
   // echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
}
else{
    switch($action)
    {
        case 'add':
            $PAGE->set_url('/local/documents/category.php',array('action' => 'add'));
            $PAGE->set_title(get_string('txtAddCategory','local_documents'));
            $PAGE->set_heading(get_string('txtAddCategory','local_documents'));
            $PAGE->navbar->add(get_string('txtListCategory','local_documents'),'category.php');
            $PAGE->navbar->add(get_string('txtAddCategory','local_documents'));
            ob_start();
            $mform->display();
            $render = ob_get_contents();
            ob_end_clean();
            break;
        case 'edit':
            $id = required_param('id', PARAM_INT);
            $category = $DB->get_record('mod_category_document',array('id'=>$id),'*');
            if($category) {
                $category->summary = array(
                    'text'   => $category->summary,
                    'format' => FORMAT_HTML,
                );
                $mform->set_data($category);
                $PAGE->set_url('/local/document/category.php', array('action' => 'edit', 'id' => $id));
                $PAGE->set_title('Edit ' . $category->name);
                $PAGE->set_heading('Edit ' . $category->name);
                $PAGE->navbar->add(get_string('txtListCategory', 'local_documents'), 'category.php');
                $PAGE->navbar->add('Edit ' . $category->name);
                ob_start();
                $mform->display();
                $render = ob_get_contents();
                ob_end_clean();
            }
            else{
                throw new coding_exception('Error ! The ID <b>'.$id.'</b> not existed in database');
            }
            break;
        case 'delete':
            $cate_id = required_param('id',PARAM_INT);
            $process = optional_param('process',0,PARAM_BOOL);
            $param_url = array('action' => 'delete');
            $param_url['id'] = $cate_id;
            $PAGE->set_url('/local/documents/category.php',$param_url);
            $PAGE->set_title(get_string('txtDeleteCategory','local_documents'));
            $PAGE->set_heading(get_string('txtDeleteCategory','local_documents'));
            $PAGE->navbar->add(get_string('txtListCategory','local_documents'),'category.php');
            $PAGE->navbar->add(get_string('txtDeleteCategory','local_documents'));
            //Check record exsited by cate_id
            if($categories = $DB->get_record('mod_category_document',array('id'=>$cate_id),'*'))
            {
                //If process return 1 , continue go
                if($process) {
                    // Process delete
                     if(delete_category($cate_id) && delete_document(array('category_id' => $cate_id)))
                     redirect($CFG->wwwroot.'/local/documents/category.php','Delete <br/> <ul><li>'.$categories->name.'</li></ul> successfully',2000);
                }
                $list_category = '<ul><li>'.$categories->name.'</li></ul>';

                $message = get_string('msgdeletecategory','local_documents');
                $message.='<br/><br/>'.$list_category;
                 // Check documents existed reference category
                 // So when delete category system auto delete document refernce
                if($documents = $DB->get_records_list('mod_documents','category_id',array($categories->id),null,'id,name')) {
                    $list_document = '<ul>';
                    foreach($documents as $id => $document){
                        $list_document.='<li><strong>'.html_writer::link(new moodle_url('/local/documents/index.php'),$document->name,array('target' => '_blank')).'</strong></li>';
                    }
                    $list_document .= '</ul>';
                    $message.= '<p style="color:red">'.get_string('msgewarning_document_exsited','local_documents').'</p>';
                    $message.= $list_document;
                    $message.= '<br/>'.get_string('msgwarning_delete_document','local_documents');
                }

                $param_url['process'] = true;
                $render = $OUTPUT->confirm($message, new single_button(new moodle_url('/local/documents/category.php', $param_url), count($categories) > 1 ? get_string('deleteall','local_documents') : get_string('delete'), 'get'),'/local/documents/category.php');
            }
            else{
                // This cate_id not existed in data
                throw new coding_exception('Error ! The ID '.$cate_id.' not existed in database');
            }
            break;
    }
}

echo $OUTPUT->header();
    echo isset($render) ? $render : '';
echo $OUTPUT->footer();
