
<?php
    
    if ($_GET['methodName']?? null){
        
        if (isset($_GET['methodName'])) {
            $methodName=$_GET['methodName'];
            if ($methodName=='checkIfFirstTaskIsCalendar'){
                $caseTypeId=$_GET['caseTypeId'];
                $response=call_service_check_first_task_calendar ($caseTypeId);
                if ($response==='true'){
                    return "1";
                }else{
                    return "0";
                }
            }
        }
    }else{
        $methodName='';
    }
    
    
    function construct_api_url($controllerName){
        $api_protocol='http://';
        $api_server_name= getenv('CASEMANAGER_API');
        $api_port_num=':8080';
       
        $url=''.$api_protocol.''.$api_server_name.''.$api_port_num.''.$controllerName.'';
        return $url;
    }

    //this will invoke backend saveLog call
    function call_service_save_activityLog ($input_action_id, $input_context, $input_primaryId, $input_actionByUserId ){
        // Zhiling added below lines - call saveLog
        //$url = 'http://108.175.7.107:8080/sovratec/activitylogs/saveLog';

        $controllerName='/sovratec/activitylogs/saveLog';
        // call construct to get complete end point
        $url=construct_api_url($controllerName);
       
        // Set the input parameters
        $id = 0;
        $actionId=$input_action_id;
        $context = $input_context;
        $primaryId = $input_primaryId;
        $actionByUserId=$input_actionByUserId;

        // Set the authentication credentials
        $username= getenv('CASEMANAGER_API_USR');
        $password =  getenv('CASEMANAGER_API_PWD');

        // Create a new cURL resource
        $ch = curl_init();

        // Set the cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'id' => $id,
            'actionId' => $actionId,
            'context' => $context,
            'primaryId'=> $primaryId,
            'actionByUserId'=> $actionByUserId,

        ]));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
       
        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        } 
            
            //echo curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close( $ch );   
            // You can do something with $response if needed
            
            return $response;
    }

    // caseId - optional, if you don't know caseId, just put 0.  rest of parameters are required
    function call_service_update_task_status ($input_caseId, $input_taskId, $input_statusId, $input_updatedByUserId ){
        // Set the UAT API endpoint URL
        //$updateTaskStatus_url = 'http://108.175.7.107:8080/sovratec/casemanager/updateTaskStatus';
        $controllerName='/sovratec/casemanager/updateTaskStatus';
        $updateTaskStatus_url=construct_api_url($controllerName);
        // Set the input parameters
        $caseId = $input_caseId;
        $taskId=$input_taskId;
        $statusId = $input_statusId;
        $updatedByUserId = $input_updatedByUserId;
        

        // Set the authentication credentials
        $username= getenv('CASEMANAGER_API_USR');
        $password =  getenv('CASEMANAGER_API_PWD');

        // Create a new cURL resource
        $ch = curl_init();

        // Set the cURL options
        curl_setopt($ch, CURLOPT_URL, $updateTaskStatus_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            
            'caseId' => $caseId,
            'taskId' => $taskId,
            'statusId'=> $statusId,
            'updatedByUserId'=> $updatedByUserId,

        ]));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
       
        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        } 
            
            //echo curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close( $ch );   
            // You can do something with $response if needed
            return $response;
    }

    function call_service_check_first_task_calendar ($input_case_type_id){
       
        $controllerName='/sovratec/casemanager/checkFirstTaskCalendar';
        // call construct to get complete end point
        $url=construct_api_url($controllerName);
        
        // Set the input parameters
        $caseTypeId = $input_case_type_id;
       
        // Set the authentication credentials
        $username= getenv('CASEMANAGER_API_USR');
        $password =  getenv('CASEMANAGER_API_PWD');

        // Create a new cURL resource
        $ch = curl_init();

        // Set the cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'caseTypeId' => $caseTypeId,

        ]));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
       
        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        } 
            
            //echo curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close( $ch );   
            // You can do something with $response if needed
            
            return $response;
    }


    
    function call_service_customer_checkin ($input_lastName, $input_lastFourSSN ){
        // Set the UAT API endpoint URL
        
        $controllerName='/sovratec/casemanager/customerCheckIn';
        
        $updateTaskStatus_url=construct_api_url($controllerName);
        // Set the input parameters
        $lastName = $input_lastName;
        $lastFourSSN=$input_lastFourSSN;
       // $statusId = $input_taskStatusId;
       // $updatedByUserId = $input_updatedByUserId;
        

        // Set the authentication credentials
        $username= getenv('CASEMANAGER_API_USR');
        $password =  getenv('CASEMANAGER_API_PWD');

        // Create a new cURL resource
        $ch = curl_init();

        // Set the cURL options
        curl_setopt($ch, CURLOPT_URL, $updateTaskStatus_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            
            'lastName' => $lastName,
            'lastFourSSN' => $lastFourSSN,
            

        ]));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
       
        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        } 
            
            //echo curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close( $ch );   
            // You can do something with $response if needed
            return $response;
    }

?>    
       

