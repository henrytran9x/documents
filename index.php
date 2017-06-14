<?php

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/locallib.php');
//enable debug krumo
//require_once($CFG->dirroot.'/krumo/class.krumo.php');

//$id = required_param('id', PARAM_INT); // Document ID.

// Check capability document view
require_login();
$context = context_system::instance();
require_capability('local/documents:view',$context);

// Set context
$PAGE->set_context($context);
// Set URL
$PAGE->set_url('/local/mod_documents/index.php');
// Set Layout
$PAGE->set_pagelayout('base');
// Set Title
$PAGE->set_title('Documents');
// Set Navbar
$PAGE->navbar->add('Documents');
// render Header
echo $OUTPUT->header();
?>


<?php echo html_writer::link('index.php?action=add',get_string('adddocument','local_mod_documents')) ?>

<?php
// Render Footer
echo $OUTPUT->footer();