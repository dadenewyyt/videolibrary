<?php
/**
 * Created by PhpStorm.
 * User: DEVELOPER4
 * Date: 11/25/14
 * Time: 3:09 PM
 */

require('config/config.php');
require('service/UploadService.php');

//First check if the configuration for post variable is set
$filed = USER_FILE_UPLOAD_FIELD_NAME ; //you can replace this with anything you want on config or runtime
 if (isset($filed) && !empty($filed) ) {

    //THEN CHECK POST that its sending the  UPLOAD FILE SUCCESSFULLY IF NOT STOP UPLOAD
       if( isset( $_FILES[$filed]) && !empty($_FILES[$filed])) {

             $uploadService = New UploadService();

             $uploadService->upload_file_now($filed);
       }
}

echo ('Configuration constant USER_FILE_UPLOAD_FIELD_NAME can\t be found. Please assign this manually or on configuration file.');
//TODO:CHECK IF conversation
//$dbs->do_upload($_FILES["file"]);