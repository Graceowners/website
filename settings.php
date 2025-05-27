<?php 
#################################################################################
include("config/config.inc.php");
helper('function,auth,admin_function,admin_controller,paginator,exportdata,ajax,admin_session,uploads');
#################################################################################
$auth = new Auth;
$module = "settings";
$module_heading = "Settings";
$table = PREFIX."admin";
if(is_allowed_access($module,'e')){ $edit_action = true;}else{ $edit_action = false;}
if(is_allowed_access($module,'v')){ $view_action =true;}else{ $view_action =false;}
if(is_allowed_access($module,'d')){ $delete_action =true;}else{ $delete_action =false;}
$filter_action = true;
$order_by = "id"; // Order by Field
$order = "DESC"; //Order ASC|DESC
$args = array();
$controller = new Controller($table,$module); // Calling Controller File

################################################
########## UPLOADING CONTROLLER CALLING ########
$uploads = new Uploads($table,$module); 
$uploads->dir ='category';
$uploads->allowed_ext ='png,jpeg,jpg,gif';
$uploads->uploadEnable = true;
#################################################
$export = new ExportData($module); // Calling Export Class
$page_size = $controller->pagesize;
if($_REQUEST['ref']){
    $ref = urldecode($_REQUEST['ref']); }else{ $ref = get_url(); 
}
$ID = $_SESSION['ADMIN_ID'];
//Allowed Fields with validation rule
$validation_fields = 
[
    'title'          =>      ["required"=>true,"validation"=>"string_validate"], 
   // 'event_date'     =>      ["required"=>false,"validation"=>"event_date"],  
   // 'event_time'     =>      ["required"=>false,"validation"=>"event_time"],   
];
//Chgecking which values is unique
$controller->unique_values = array('slug');
#################################################################################
######################  ADD and EDIT Section ########################
if(!empty($_POST) and strtoupper($_SERVER['REQUEST_METHOD'])=='POST'){
    
       if(!is_allowed_access($module,'e')){
             flash($module,"You are not allowed to perform this action",'alert alert-danger');
             redirect(get_url());
      }
    
    if($_REQUEST['ref']){$ref = urldecode($_REQUEST['ref']); }else{ $ref = get_url(); } 
    
    if(isset($_POST['password']) and isset($_POST['cpassword'])){
        if($_POST['password']==$_POST['cpassword']){
           $_update_arrays = [ 'password'  =>  $auth->encodePassword($_POST['password']), ];
           $controller->update_record($ID,$_update_arrays);
           flash($module,"Password Updated Sucessfull!",'alert alert-success','check');
           redirect($ref); 
        }else{
            flash($module,"Password and Confirm Password does not matched ",'alert alert-danger','cross');
           redirect($ref) ;
        }
       
    }

}

#################################################################################
if(!empty($_POST['exp_field'])){
    $controller->set_sql_fields($_POST['exp_field']);
}

################### List Fields to display ##################
$Listing_columns = array
(
   // array('Thumbnail','pic','image'),
    array('Title','title'),
    array('Price','price'),
);
##################### SEARCH MODULE ########################
//Operators =  // %s% | %s| s% | = | != | > | < |FIND_IN_SET | IN  ;    
if(isset($_REQUEST['keyword']) and $_REQUEST['keyword']!=''){
    $ks = clean_string($_REQUEST['keyword']);
    $args[] =  array('field' => 'title','operator'=>'%s%','value'=> $ks);
}
/*
if(isset($_REQUEST['status']) and $_REQUEST['status']!=''){
    $k = clean_string($_REQUEST['status']);
    $args[] =   array('field' => 'status','operator'=>'=','value'=> $k);
    $status = $k==1?'Active':'inactive';
}
*/



//Calling inlcludes files 

        $qry = "SELECT * from `".$table."` WHERE `id` = '".$ID."'";
        $Record = $db->getSingleRow($qry );
        $file = 'views/settings.edit.php';


//Include files
include("views/common/header.php");
flash($module);
include_once($file);
include("views/common/footer.php");
