<?php 
#################################################################################
include("config/config.inc.php");
helper('function,admin_function,admin_controller,paginator,exportdata,ajax,admin_session,uploads');
#################################################################################
if(isset($_POST)){
global $db;

// Process the form data
foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
    
    $image_name = $_FILES['images']['name'][$key];
    $image_type = $_POST['imageTypes'][$key];
    $alt_text = $_POST['altTexts'][$key];
    $block = $_POST['block'];
    $block_id = $_POST['block_id'];
    $type = 'gallery';
    $device = $image_type;
    $status = 1;

    $url = time()."_".$image_name;
    $alt = $alt_text;
    $block_id = $block_id;
    $type = $type;
    $device = $device;
    $status = $status;
    $time= time();

    $sql = "INSERT INTO `tour_media` (`url`, `alt`, `block`, `block_id`, `type`, `device`, `status`,`posted_on`) VALUES ('".$url."', '".$alt."', '".$block."', '".$block_id."', '".$type."', '".$device."', '".$status."','".$time."')";
    $db->query($sql);
    // Move uploaded image to desired location
    @move_uploaded_file($tmp_name, 'admin/media/cars/' . $url);
    if($device=='mobile'){
        $thumbnailWidth = 80; // Custom thumbnail width
        $thumbnailHeight = 80; // Custom thumbnail height
    }else{
        $thumbnailWidth = 120; // Custom thumbnail width
        $thumbnailHeight = 80; // Custom thumbnail height
    }
    $thumbnailDestination = 'media/gallery/thumbnail/' . 'thumb_'.$url; // Path to save the thumbnail
    
    createThumbnail('media/gallery/' . $url, $thumbnailDestination, $thumbnailWidth, $thumbnailHeight);
}

  echo json_encode(['success' => true]);
}
