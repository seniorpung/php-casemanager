<?php
require_once "layouts/config.php";

global $link;
include 'class.crud.php';

include 'ecrypt.php';

$crudObj = new CRUD();
$crudObj->mysqli = $link;

if(isset($_GET['case_attachment_id']) && !empty($_GET['case_attachment_id'])){
    $case_attachment_id = ___decryption_openssl($_GET['case_attachment_id']);
    $case_attachment_id = str_replace('CaseManagerSalt', '', $case_attachment_id);
    $case_attachments = $crudObj->FindRow('case_attachments', array('original_filename', 'file_ext', 'file_name'), array('case_attachment_id='.$case_attachment_id));

    $new_filename = $case_attachments['original_filename'];

    if(isset($case_attachments['original_filename']) && !empty($case_attachments['file_name'])) {
        if(file_exists(ROOT_PATH.'/uploads/case-attachements/'.$case_attachments['file_name'])) {
            $file_url = ROOT_URL.'/uploads/case-attachements/'.$case_attachments['file_name'];  
            header('Content-Type: application/octet-stream');  
            header("Content-Transfer-Encoding: utf-8");   
            header("Content-disposition: attachment; filename=\"".$new_filename."\"");
            readfile($file_url); 
            exit;
        }
    }
}
?>