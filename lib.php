<?php

/**
 * @return string
 * @throws coding_exception
 *
 * function get_table_category_document()
 * Get data category document show Table
 */

function get_table_category_document(){
    global $DB;

    // Param URL
    $search = optional_param('search',null,PARAM_RAW);
    // Query all Category
    $where = '';
    $param = array();
    $query = 'SELECT * FROM {mod_category_document} cate ';
    /**
     *  Need Review check search by character Vietnames
     */
    if(isset($search) && !empty($search)) {
         $where[] = $DB->sql_like('cate.name', ':search');
         $param['search'] = '%'.$search.'%';
    }
    if(is_array($where)){
        $where = ' WHERE '.implode('AND',$where);
    }
    $result = $DB->get_records_sql($query.$where,$param);
    $table = new html_table();
    $table->head = array(
        '',
        html_writer::empty_tag('input', array('type' => 'checkbox', 'name' => 'checkAll')),
        get_string('cln_name','local_documents'),
        get_string('cln_created','local_documents'),
        get_string('cln_author','local_documents'),
        get_string('cln_description','local_documents'),
        get_string('txtdocument','local_documents'),
        get_string('cln_action','local_documents'),
    );
    if($result) {
        $x = 1;
        foreach($result as $key => $value){
            // Get fields (firstname,lastname) by user id ;
            $user = $DB->get_record('user',array('id' => $value->created_by),'firstname,lastname');
            if($user){ $fullname = $user->firstname.' '.$user->lastname;}
            $count_document = $DB->count_records('mod_documents',array('category_id' => $value->id));
            $row = new html_table_row(array(
                $x,
                html_writer::empty_tag('input', array('type' => 'checkbox','class' => 'checkID','name' => 'ids[]','value' => $value->id)),
                $value->name,
                !empty($value->created_date) ? date('d-m-Y',$value->created_date) : '',
                isset($fullname) ? $fullname : '',
                !empty($value->summary) ? truncateStringWords($value->summary,50) : '....', // Truncate String
                ($count_document > 0)  ? html_writer::link(new moodle_url('/local/documents/index.php',array('category'=>$value->id)), $count_document,array('target' => '_blank')) : '0',
                html_writer::link(new moodle_url('/local/documents/category.php',array('action' => 'edit','id' => $value->id)),get_string('str_edit','local_documents')).' | '.html_writer::link(new moodle_url('/local/documents/category.php',array('action'=>'delete','id' => $value->id)),get_string('str_delete','local_documents'))
            ));
            $x++;
            // Set record row table
            $table->data[] = $row;
        }
    }
    else{
        // If result empty show word 'No Found data'
        $cell = new html_table_cell('<p class="text-center">'.get_string('txtnofounddata','local_documents').'</p>');
        $cell->colspan = 7;
        $table->data[] = new html_table_row(array($cell));

    }
    return html_writer::table($table);
}


function get_table_documents(){
    global $DB;

    // Param URL
    $search = optional_param('search',null,PARAM_RAW);
    $category = optional_param('category',null,PARAM_INT);
    // Query all Category
    $where = '';
    $param = array();
    $query = 'SELECT * FROM {mod_documents} doc ';
    /**
     *  Need Review check search by character Vietnames
     */
    if(isset($search) && !empty($search)) {
        $where[] = $DB->sql_like('doc.name', ':search');
        $param['search'] = '%'.$search.'%';
    }
    if(isset($category) && !empty($category)){
        $where[] = $DB->sql_like('doc.category_id',':category');
        $param['category'] = $category;
    }
    if(is_array($where)){
        $where = ' WHERE '.implode('AND',$where);
    }
    $result = $DB->get_records_sql($query.$where,$param);
    $table = new html_table();
    $table->head = array(
        '',
        html_writer::empty_tag('input', array('type' => 'checkbox', 'name' => 'checkAll')),
        get_string('cln_name','local_documents'),
        get_string('cln_created','local_documents'),
        get_string('cln_category','local_documents'),
        get_string('cln_author','local_documents'),
        get_string('cln_description','local_documents'),
        get_string('cln_action','local_documents')
    );
    if($result) {
        $x = 1;
        foreach($result as $key => $value){
            // Get fields (firstname,lastname) by user id ;
            $user = $DB->get_record('user',array('id' => $value->created_by),'firstname,lastname');
            if($user){ $fullname = $user->firstname.' '.$user->lastname;}
            $cate_name = $DB->get_record('mod_category_document',array('id' => $value->category_id),'name');
            $row = new html_table_row(array(
                $x,
                html_writer::empty_tag('input', array('type' => 'checkbox','class' => 'checkID','name' => 'ids[]','value' => $value->id)),
                $value->name,
                !empty($value->created_date) ? date('d-m-Y',$value->created_date) : '',
                isset($cate_name) ? $cate_name->name : '',
                isset($fullname) ? $fullname : '',
                !empty($value->summary) ? truncateStringWords($value->summary,50) : '....', // Truncate String
                html_writer::link(new moodle_url('/local/documents/edit.php',array('action' => 'edit','id' => $value->id)),get_string('str_edit','local_documents')).' | '.html_writer::link(new moodle_url('/local/documents/edit.php',array('action'=>'delete','id' => $value->id)),get_string('str_delete','local_documents'))
            ));
            $x++;
            // Set record row table
            $table->data[] = $row;
        }
    }
    else{
        // If result empty show word 'No Found data'
        $cell = new html_table_cell('<p class="text-center">'.get_string('txtnofounddata','local_documents').'</p>');
        $cell->colspan = 7;
        $table->data[] = new html_table_row(array($cell));

    }
    return html_writer::table($table);
}




/**
 * function delete_category()
 * @param $cate_id
 * Delete record category by ID
 */

function delete_category($id){
    global $DB;
    return $DB->delete_records('mod_category_document',array('id' => $id));
}

function delete_document(array $conditions = null){
    global $DB;
    return $DB->delete_records('mod_documents',$conditions);
}


function get_data_fields_by_condition($table,$condition = array(),$fields = array())
{
    global $DB;
    return $DB->get_record($table,$condition,is_array($fields) ? implode(',',$fields) : '*');
}

function truncateStringWords($str, $maxlen,$replace = '...') {
    if (strlen($str) <= $maxlen) return $str;
    $newstr = substr($str, 0, $maxlen);
    if (substr($newstr, -1, 1) != ' ') $newstr = substr($newstr, 0, strrpos($newstr, " "));

    return $newstr.$replace;
}