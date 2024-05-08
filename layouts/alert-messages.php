<?php 
if(isset($data['status']) && $data['status'] == true){
    ?><div class="alert alert-success text-center h5" role="alert"><?php echo $data['message']; ?></div><?php 
}
else if(isset($data['status']) && $data['status'] == false){
    ?><div class="alert alert-danger text-center h5" role="alert"><?php echo $data['message']; ?></div><?php 
} 
?>