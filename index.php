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



// Set Page Layout
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);
$PAGE->set_url('/local/documents/index.php');
$PAGE->set_title(get_string('txtdocument','local_documents'));
$PAGE->set_heading(get_string('txtdocument','local_documents'));
$PAGE->navbar->add(get_string('txtdocument','local_documents'));



$tpldata = array(
    'action_url' => $PAGE->url,
    'search_value' => isset($_POST['search']) && !empty($_POST) ? $_POST['search'] : '',
    'table_documents' => get_table_documents(),
);
$render = $OUTPUT->render_from_template('local_documents/documents', $tpldata);
    echo $OUTPUT->header();
        // Render Form
        if (has_capability('local/document:overcontrol', $context)) {
            echo isset($render) ? $render : '';
            //Check permisson allow see "Add" and "View" Category
            echo html_writer::link(new moodle_url('/local/documents/edit.php', array('action' => 'add')), get_string('adddocument', 'local_documents'), array('class' => 'btn btn-blue'));
            echo html_writer::link(new moodle_url('/local/documents/category.php'), get_string('listdocument', 'local_documents'), array('class' => 'pull-right btn btn-blue'));
        }
    echo $OUTPUT->footer();