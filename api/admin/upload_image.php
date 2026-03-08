<?php
session_start();

/*******************************************************
 * Only these origins will be allowed to upload images *
 ******************************************************/
$accepted_origins = array("http://localhost:8080", "https://soundtable.fr");

/*********************************************
 * Change this line to set the upload folder *
 ********************************************/
$imageFolder = "../../asset/uploads/";

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['error' => 'Non autorisé']);
    return;
}

reset ($_FILES);
$temp = current($_FILES);
if (is_uploaded_file($temp['tmp_name'])){
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // same-origin requests won't set an origin. If the origin is set, it must be valid.
        if (in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        } else {
            header("HTTP/1.1 403 Origin Denied");
            return;
        }
    }

    /*
      If your script needs to receive cookies, set images_upload_credentials : true in
      the configuration and enable the following two headers.
    */
    // header('Access-Control-Allow-Credentials: true');
    // header('P3P: CP="There is no P3P policy."');

    // Make sure the upload directory exists
    if (!is_dir($imageFolder)) {
        mkdir($imageFolder, 0755, true);
    }

    // Sanitize input
    if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
        header("HTTP/1.1 400 Invalid file name.");
        return;
    }

    // Verify extension
    if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), array("gif", "jpg", "png", "webp", "jpeg"))) {
        header("HTTP/1.1 400 Invalid extension.");
        return;
    }

    // Accept upload if there was no origin, or if it is an accepted origin
    $filetowrite = $imageFolder . $temp['name'];
    
    // Check if file exists, rename if necessary to avoid overwrite
    $i = 1;
    $authFilename = $temp['name'];
    while (file_exists($filetowrite)) {
        $info = pathinfo($temp['name']);
        $authFilename = $info['filename'] . '_' . $i . '.' . $info['extension'];
        $filetowrite = $imageFolder . $authFilename;
        $i++;
    }
    
    move_uploaded_file($temp['tmp_name'], $filetowrite);

    // Respond to the successful upload with JSON.
    // Use a location key to specify the path to the saved image resource.
    // { location : '/your/uploaded/image/file'}
    echo json_encode(array('location' => '/asset/uploads/' . $authFilename));
} else {
    // Notify editor that the upload failed
    header("HTTP/1.1 500 Server Error");
}
?>
