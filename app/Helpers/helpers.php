<?php

function APIsuccess($msg = 'success',$data = array(),$current_page = '',$total_page = '',$total_records = '',$global_array = array())
{
    if($current_page != '' && $total_page != '' && $total_records != '')
    {
        if(empty($data) || count($data) <= 0 ){
            echo json_encode(array("status" => true, 
                               "message" => $msg, 
                               "current_page" => $current_page, 
                               "total_page" => $total_page, 
                               "total_records" => $total_records, 
                               "global" => $global_array), 
                        JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }else{
            echo json_encode(array("status" => true, 
                               "message" => $msg, 
                               "current_page" => $current_page, 
                               "total_page" => $total_page, 
                               "total_records" => $total_records, 
                               "global" => $global_array,
                               "data" => $data), 
                        JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }       
        exit;
    }
    else if($total_records != '')
    {
        echo json_encode(array("status"  => true,
                               "message" => $msg,
                               "total_records" => $total_records, 
                               "data" => $data), 
                        JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);exit;
    }
    else
    {
        echo json_encode(array("status"  => true,
                               "message" => $msg, 
                               "data" => $data), 
                        JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );exit;
    }
}

function APIerror($msg = "", $data = array())
{
    echo json_encode(array("status" => false, "message" => $msg , "data" => array() ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function created()
{
    return date("Y-m-d H:i:s");
}

function getDateFormat($date = "")
{
    $newDate = date("d-m-Y H:i:s", strtotime($date));
    return $newDate;
}



?>
