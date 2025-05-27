<?php 
#################################################################################
include("config/config.inc.php");
helper('function,admin_function,admin_controller,paginator,exportdata,ajax,admin_session,uploads');
#################################################################################
$module = "users";
$module_heading = "User";
$table = PREFIX."users";
$edit_action = true;
$view_action =true;
$delete_action = true;
$filter_action = true;
$order_by = "id"; // Order by Field
$order = "DESC"; //Order ASC|DESC
$args = array();
$controller = new Controller($table,$module); // Calling Controller File
################################################
########## UPLOADING CONTROLLER CALLING ########
$uploads = new Uploads($table,$module); 
$uploads->dir ='user';
$uploads->allowed_ext ='png,jpeg,jpg,gif';
$uploads->uploadEnable = true;
#################################################
$export = new ExportData($module); // Calling Export Class
$page_size = $controller->pagesize;
if($_REQUEST['ref']){
    $ref = urldecode($_REQUEST['ref']); }else{ $ref = get_url(); 
}
//Allowed Fields with validation rule
$validation_fields = 
[
    'name'          =>      ["required"=>true,"validation"=>"string_validate"],
    'email'         =>      ["required"=>true,"validation"=>"email_validate"],
    'mobile'        =>      ["required"=>true,"validation"=>"mobile_validate"],
    //'user_id'       =>      ["required"=>true,"validation"=>"userid_validate"],
  //  'state'         =>      ["required"=>true,"validation"=>"int_validate"],    
];
//Chgecking which values is unique
$controller->unique_values = array('email','mobile');
#################################################################################
######################  ADD and EDIT Section ########################
if(!empty($_POST) and strtoupper($_SERVER['REQUEST_METHOD'])=='POST'){
    $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    //$controller->set_post_data($_POST);
    if(isset($_POST['type']) and $_POST['type']!=''){
        $_post_arrays = [
            'name'      =>  trim($_POST['name']),
            'email'     =>  trim($_POST['email']),
            'mobile'    =>  trim($_POST['mobile']),
            //'user_id'   =>  trim($_POST['user_id']),
            //'state'     =>  trim($_POST['state']),
            //'city'      =>  trim($_POST['city']),
            //'zip'       =>  trim($_POST['zip']),
            //'address'   =>  trim($_POST['address']),
            'reg_on'    =>  time(),
            'updated_on'=>  time(),
            'status'    =>  1,
        ];

        if($_POST['password']!=''){
            $_post_arrays['password'] =  md5($_POST['password']);
        }

        switch($_POST['type']){
            case 'add':
                if($_REQUEST['ref']){$ref = urldecode($_REQUEST['ref']); }else{ $ref = get_url(); }     
                $validation =  $controller->validate($validation_fields,$_post_arrays);
                if($validation['error']!=true){
                    $duplicate_error = $controller->check_unique_records($_post_arrays);
                    if($duplicate_error['error']==0){
                        $resid = $controller->add_new_record($_post_arrays);
                        if($resid!=''){
                            $uploads->ID = $resid; 
                            $uploads->files_data = $_FILES;
                            if($uploads->uploadEnable==true){
                                $uploads->image ='pic';
                                $uploads->field ='pic';
                                $msg = $uploads->upload_media(true); 
                            }
                            flash($module,"Record Added Sucessfull!",'alert alert-success','check');
                            redirect(ADMIN_URL.'/'.$module.".php?arg=true&action=edit&id=".$resid);  
                        }else{
                            flash($module,"Something Went Wrong while adding record",'alert alert-danger','cross');
                            redirect($ref);  
                        }
                    }else{
                        flash($module,$duplicate_error['msg'],'alert alert-danger','cross');
                    }
                }else{
                    flash($module,$validation['msg'],'alert alert-danger','cross');
                }
            break;
            case 'edit':
                //Updating New Record
                $ID = trim($_POST['record_id']);
                //Checking Validation
                $validation =  $controller->validate($validation_fields,$_post_arrays);
                if($validation['error']!=true){
                    $duplicate_error = $controller->check_unique_records($_post_arrays,$ID);
                    if($duplicate_error['error']==0){
                        if($controller->update_record($ID,$_post_arrays)){
                            ######## Uploading Images  ############
                            $uploads->ID = $ID; 
                            $uploads->files_data = $_FILES;
                            if($uploads->uploadEnable==true){
                                $uploads->image ='pic';
                                $uploads->field ='pic';
                                $msg = $uploads->upload_media(true); 
                                #####################################
                                $uploads->image ='thumbnail';
                                $uploads->field ='thumbnail';
                                $msg = $uploads->upload_media(true); 
                            }
                            ######## Uploading Edns Here ###########
                            flash($module,"Record Updated Sucessfull!",'alert alert-success','check');
                            redirect($ref); 
                        }else{
                            flash($module,"Something Went Wrong While updating your record",'alert alert-danger','cross');
                        }
                    }else{
                        flash($module,$duplicate_error['msg'],'alert alert-danger','cross');
                    }
                }else{
                    flash($module,$validation['msg'],'alert alert-danger','cross');
                }
            break;
            case 'change_status':
                //Updating Selected Record Status
                $controller->change_record_status($_POST);
            break;
        }
    }
}

#################################################################################
if(!empty($_POST['exp_field'])){
    $controller->set_sql_fields($_POST['exp_field']);
}

if(isset($_REQUEST['id']) and $_REQUEST['id']!=''){
    $ID = get_Id('id');
}
################### List Fields to display ##################
$Listing_columns = array
(
    array('Pic','pic','image'),
    array('Mobile','mobile'),
    array('Name','name'),
);
##################### SEARCH MODULE ########################
//Operators =  // %s% | %s| s% | = | != | > | < |FIND_IN_SET | IN  ;    
if(isset($_REQUEST['keyword']) and $_REQUEST['keyword']!=''){
    $ks = clean_string($_REQUEST['keyword']);
    $args[] =  array('field' => 'name,email,user_id','operator'=>'%s%','value'=> $ks);
}
if(isset($_REQUEST['mobile']) and $_REQUEST['mobile']!=''){
    $ks = clean_string($_REQUEST['mobile']);
    $args[] =  array('field' => 'mobile','operator'=>'=','value'=> $ks);
}
if(isset($_REQUEST['status']) and $_REQUEST['status']!=''){
    $k = clean_string($_REQUEST['status']);
    $args[] =   array('field' => 'status','operator'=>'=','value'=> $k);
    $status = $k==1?'Active':'inactive';
}


if(isset($_REQUEST['action']) and $_REQUEST['action']=='delete'){
    if($db->deleteRecord($table,$ID)){
        if($_REQUEST['ref']){$ref = urldecode($_REQUEST['ref']); }else{ $ref = get_url(); }
        flash($module,"Record #".$ID." Deleted Successfully",'alert alert-success');
        redirect($ref);
    }
}



//Calling inlcludes files 
switch($_REQUEST['action']){
    case 'edit':
        $qry = "SELECT * from `".$table."` WHERE `id` = '".$ID."'";
        $Record = $db->getSingleRow($qry );
        $file = 'views/form/'.$module.'.add.php';
    break;
    case 'add':
        $file = 'views/form/'.$module.'.add.php';
    break;
    case 'delete':
        $file = 'views/form/'.$module.'.add.php';
    break;
    default:
        $controller->set_where($args);
        $controller->order_by($order_by,$order);
        //echo $controller->get_sql();exit;
        $limit      = ( isset( $_GET['limit'] ) ) ? $_GET['limit'] : $page_size;
        $page       = ( isset( $_GET['page'] ) ) ? $_GET['page'] : 1;
        $links      = ( isset( $_GET['links'] ) ) ? $_GET['links'] : 7;
        $query      = $controller->get_sql();
        $Paginator  = new Paginator( $query );
        $data    = $Paginator->getData( $limit, $page );
        $records = $data->data;
        $file = 'views/list/'.$module.'.list.php';
    break;
}

//Export All Record;
if(isset($_POST['export_all'])){
    $export->set_sql($query);
    $export->export_data();
    flash($module,"Record Exported Successfully",'alert alert-success');
}
//Export Custom Filed Data
if(isset($_POST['export_custom']) and !empty($_POST['exp_field'])){
    $export->set_sql($query);
    $export->export_data($_POST['exp_field']);
    flash($module,"Record Exported Successfully",'alert alert-success');  
}

//Include files
include("views/common/header.php");
flash($module);
include_once($file);
include("views/common/footer.php");
