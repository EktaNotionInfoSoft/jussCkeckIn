<?php 
namespace App\Http\Controllers;


use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Helper\Helper;

use DB;

class ApiController extends Controller
{

    public function get_setting(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {

            $res = DB::table('tbl_setting')->select('*')->get();

            $return_array = array(
                "site_name"  => $res[0]->value,
                "site_logo"  => url('uploads/image/'.$res[1]->value),
            );

            APISuccess("Success",$return_array);
        } 
        else
        {
            APIError("Token missing.");
        }  
    }

    public function login(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->email) AND $request->email != "" AND isset($request->password) AND $request->password != "")
            {

                $checkUser = DB::table('tbl_users')->where("email",$request->email)->where("password",$request->password)->count();

                if($checkUser > 0)
                {
                    $checkEmailStatus = DB::table('tbl_users')->where("is_verify_email","y")->where("email",$request->email)->count();

                    if($checkEmailStatus > 0)
                    {

                        $checkStatus = DB::table('tbl_users')->where("status","a")->where("email",$request->email)->count();

                        if($checkStatus > 0)
                        {
                            $auth_token = md5(time());

                            $update_array = array(
                                "auth_token"   => $auth_token,
                            );
                            DB::table('tbl_users')->where('email', $request->email)->update($update_array);  


                            $res = DB::table('tbl_users')->select('*')->where("email",$request->email)->first();

                            $return_array = array(
                                "user_id"    => $res->id,
                                "auth_token" => $auth_token, 
                                "user_type"  => $res->user_type,
                                "first_name" => $res->first_name,
                                "last_name"  => $res->last_name,
                                "email"      => $res->email,
                                "status"     => $res->status,
                                "created"    => getDateFormat($res->created),
                            );

                            APISuccess("Login successfully",$return_array);
                        }
                        else
                        {
                            APIError("Your account has been deactivated.");
                        }
                    }
                    else
                    {
                        APIError("Email not verified.");
                    }
                }
                else
                {
                    APIError("Username and password wrong.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }    
    }
    
    public function register(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {

            if(isset($request->first_name) AND $request->first_name != "" AND
               isset($request->last_name) AND $request->last_name != "" AND
               isset($request->email) AND $request->email != "" AND
               isset($request->user_type) AND $request->user_type != "" AND
               isset($request->password) AND $request->password != "")
            {
                $checkEmail = DB::table('tbl_users')->where("email",$request->email)->count();

                if($checkEmail <= 0)
                {

                    $insert_array = array(
                        "first_name"      => $request->first_name,
                        "last_name"       => $request->last_name,
                        "email"           => $request->email,
                        "password"        => $request->password,
                        "is_verify_email" => "y",
                        "status"          => "a",
                        "user_type"       => $request->user_type, 
                        "auth_token"      => "",
                        "created"         => created(),  

                    );
                    $id = DB::table('tbl_users')->insertGetId($insert_array);
                    
                    $insert_array['user_id'] = $id;

                    unset($insert_array['password']);

                    APISuccess("User registared successfully",$insert_array);
                }
                else
                {
                    APIError("Email already registared.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function change_password(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->email) AND $request->email != "")
            {

                $checkEmail = DB::table('tbl_users')->where("email",$request->email)->count();

                if($checkEmail > 0)
                {
                   
                   APISuccess("New password send to your registared email address."); 

                }                    
                else
                {
                    APIError("This email not registared.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }    
    }
    
    public function logout(Request $request)
    {
        
        $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();
        
        if($checkUser > 0)
        {
            $auth_token = md5(time());

            $update_array = array(
                "auth_token"   => $auth_token,
            );
            DB::table('tbl_users')->where('id', $request->user_id)->update($update_array);

            APISuccess("Logout successfully.");
        } 
        else 
        {
            APIError("User not found.");
        }
    }

    public function check_auth(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->auth_token) AND $request->auth_token != "" AND isset($request->user_id) AND $request->user_id != "")
            {

                $checkUser = DB::table('tbl_users')->where("auth_token",$request->auth_token)->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $res = DB::table('tbl_users')->select('*')->where("id",$request->user_id)->first();

                    $return_array = array(
                        "user_id"    => $res->id,
                        "auth_token" => $res->auth_token, 
                        "user_type"  => $res->user_type,
                        "first_name" => $res->first_name,
                        "last_name"  => $res->last_name,
                        "email"      => $res->email,
                        "status"     => $res->status,
                        "created"    => getDateFormat($res->created),
                    );

                    APISuccess("Authentication successfully",$return_array);
                }        
                else
                {
                    APIError("Authentication fail.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }    
    }
    
    public function add_task(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {

            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->task_title) AND $request->task_title != "" AND
               isset($request->task_description) AND $request->task_description != "" AND
               isset($request->task_timeline) AND $request->task_timeline != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $insert_array = array(
                        "user_id"            => $request->user_id,
                        "task_title"         => $request->task_title,
                        "task_description"   => $request->task_description,
                        "task_timeline"      => $request->task_timeline,
                        "task_date"          => $request->task_date,
                        "status"             => "p",
                        "created"            => created(),  
                    );
                    $id = DB::table('tbl_task')->insertGetId($insert_array);
                    
                    APISuccess("Task added successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    
    public function get_task_list(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "")
            {
                $res = DB::table('tbl_task')->select('*')->where("user_id",$request->user_id)->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $return_array[] = array(
                            "task_id"          => $value->id,
                            "user_id"          => $value->user_id,
                            "task_title"       => $value->task_title,
                            "task_description" => $value->task_description,
                            "task_timeline"    => $value->task_timeline,
                            "task_date"        => getDateFormat($value->task_date),
                            "task_status"      => $value->status,
                            "created"          => getDateFormat($value->created)
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Task not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    
    public function get_task_details(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND 
               isset($request->task_id) AND $request->task_id != "")
            {
                $res = DB::table('tbl_task')->select('*')->where("id",$request->task_id)->first();
                
                if(!empty($res))
                {
                    $return_array = array(
                            "task_id"          => $res->id,
                            "user_id"          => $res->user_id,
                            "task_title"       => $res->task_title,
                            "task_description" => $res->task_description,
                            "task_timeline"    => $res->task_timeline,       
                            "task_date"        => getDateFormat($res->task_date),                     
                            "task_status"      => $res->status,
                            "created"          => getDateFormat($res->created)
                        );   
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Task not found.");
                }
            }
            else
            {
                APIError("parameter missing.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }

    public function delete_task(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {

            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->task_id) AND $request->task_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkTask = DB::table('tbl_task')->where("id",$request->task_id)->count();

                    if($checkTask > 0)
                    {
                        DB::table('tbl_task')->where('id', '=', $request->task_id )->delete();
                        APISuccess("Task deleted successfully.");    
                    }
                    else
                    {
                        APIError("Task id not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function edit_task(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {

            if(isset($request->user_id) AND $request->user_id != "" AND
                isset($request->task_id) AND $request->task_id != "" AND
               isset($request->task_title) AND $request->task_title != "" AND
               isset($request->task_description) AND $request->task_description != "" AND
               isset($request->task_timeline) AND $request->task_timeline != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkTask = DB::table('tbl_task')->where("id",$request->task_id)->count();

                    if($checkTask > 0)
                    {

                        $update_array = array(
                            "task_title"         => $request->task_title,
                            "task_description"   => $request->task_description,
                            "task_timeline"      => $request->task_timeline,
                            "task_date"          => $request->task_date,
                            "created"            => created(),  
                        );

                        DB::table('tbl_task')->where('id', $request->task_id)->update($update_array);  

                        APISuccess("Task edited successfully.");
                    }
                    else
                    {
                        APIError("Task id not found.");     
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function change_task_status(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {

            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->task_id) AND $request->task_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkTask = DB::table('tbl_task')->where("id",$request->task_id)->count();

                    if($checkTask > 0)
                    {
                        
                        $update_array = array(
                            "status"         => $request->task_status,
                        );

                        DB::table('tbl_task')->where('id', $request->task_id)->update($update_array);  

                        APISuccess("Task status changed successfully.");    
                    }
                    else
                    {
                        APIError("Task id not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function add_guest_facility(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {

            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->title) AND $request->title != "" AND
               isset($request->description) AND $request->description != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {

                    $fileName = "guest_facility_".time().'.'.$request->image->extension();  
   
                    $request->image->move(public_path('uploads/facility'), $fileName);


                    $insert_array = array(
                        "user_id"       => $request->user_id,
                        "title"         => $request->title,
                        "description"   => $request->description,
                        "facility_type" => $request->facility_type,
                        "image"         => $fileName, 
                        "created"       => created(),  
                    );
                    
                    $id = DB::table('tbl_guest_facility')->insertGetId($insert_array);
                    
                    if ($request->facility_type == 'paid') 
                    {
                        $insert_amount = array(
                            "amount" => $request->amount,
                        );

                       DB::table('tbl_guest_facility')->where('id',$id)->update($insert_amount);    
                    }

                    APISuccess("Guest facility added successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function get_guest_facility(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "")
            {
                $res = DB::table('tbl_guest_facility')->select('*')->where("user_id",$request->user_id)->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $return_array[] = array(
                            "id"               => $value->id,
                            "user_id"          => $value->user_id,
                            "title"            => $value->title,
                            "description"      => $value->description,
                            "image"            => url('uploads/facility/'.$value->image),
                            "upload_id"        => url('uploads/uploadId/'.$value->upload_id),
                            "status"           => $value->status,
                            "created"          => getDateFormat($value->created)
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Guest facility not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    
    public function delete_guest_facility(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->guest_facility_id) AND $request->guest_facility_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkTask = DB::table('tbl_guest_facility')->where("id",$request->guest_facility_id)->count();

                    if($checkTask > 0)
                    {
                        DB::table('tbl_guest_facility')->where('id', '=', $request->guest_facility_id )->delete();
                        APISuccess("Guest facility deleted successfully.");    
                    }
                    else
                    {
                        APIError("Guest facility id not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }    

    public function guest_facility_details(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND 
               isset($request->guest_facility_id) AND $request->guest_facility_id != "")
            {
                $res = DB::table('tbl_guest_facility')->select('*')->where("id",$request->guest_facility_id)->first();
                
                if(!empty($res))
                {
                    $return_array = array(
                            "id"               => $res->id,
                            "user_id"          => $res->user_id,
                            "title"            => $res->title,
                            "description"      => $res->description,
                            "image"            => url('uploads/facility/'.$res->image),
                            "status"           => $res->status,
                            "created"          => getDateFormat($res->created)
                    );   
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Guest facility record not found.");
                }
            }
            else
            {
                APIError("parameter missing.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }

    public function edit_guest_facility(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            $fileName = "";
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->guest_facility_id) AND $request->guest_facility_id != "" AND
               isset($request->title) AND $request->title != "" AND
               isset($request->description) AND $request->description != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkGuest = DB::table('tbl_guest_facility')->where("id",$request->guest_facility_id)->count();

                    if($checkGuest > 0)
                    {

                        if(isset($request->image))
                        {
                            $fileName = "guest_facility_".time().'.'.$request->image->extension();  
                            $request->image->move(public_path('uploads/facility'), $fileName);
                        }

                        if(isset($request->upload_id))
                        {
                            $fileName = "guest_facility_".time().'.'.$request->image->extension();  
                            $request->image->move(public_path('uploads/facility'), $fileName);
                        }

                        if (isset($request->facility_type) == 'paid') 
                        {
                            $amount = $request->amount;
                        }
                        else
                        {
                            $amount = '0';
                        }

                        $update_array = array(
                            "title"         => $request->title,
                            "description"   => $request->description,
                            "facility_type" => $request->facility_type,
                            "amount"        => $amount,
                        );

                        if($fileName != "")
                        {
                            $update_array['image'] = $fileName;
                        }

                        DB::table('tbl_guest_facility')->where('id', $request->guest_facility_id)->update($update_array);  

                        APISuccess("Guest facility edited successfully.");
                    }
                    else
                    {
                        APIError("Guest id not found.");     
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function add_property(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {

            if(isset($request->user_id) AND $request->user_id != "" AND
                isset($request->property_type) AND $request->property_type != "" AND
                isset($request->property_name) AND $request->property_name != "" AND 
                isset($request->location) AND $request->location != "" AND
                isset($request->total_room) AND $request->total_room != "" AND
                isset($request->guest_facility_id) AND $request->guest_facility_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $fileName = "property_".time().'.'.$request->image->extension();  
                    $request->image->move(public_path('uploads/property'), $fileName);

                    $uploadId = "uploadId_".time().'.'.$request->upload_id->extension();  
                    $request->image->move(public_path('uploads/uploadId'), $uploadId);

                    $insert_array = array(
                        "user_id"            => $request->user_id,
                        "property_type"      => $request->property_type,
                        "property_name"      => $request->property_name,
                        "location"           => $request->location, 
                        "total_room"         => $request->total_room, 
                        "guest_facility_id"  => $request->guest_facility_id,
                        "image"              => $fileName,  
                        "upload_id"          => $uploadId,   
                        "created"            => created(),  
                    );
                    
                    $id = DB::table('tbl_property')->insertGetId($insert_array);
                    
                    APISuccess("Property added successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function get_my_property(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "")
            {
                $res = DB::table('tbl_property')->select('*')->where("user_id",$request->user_id)->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $property_type_res = DB::table('tbl_property_type')->select('*')->where("id",$value->property_type)->first();

                        $guest_facility_res = DB::table('tbl_guest_facility')->select('*')->where("id",$value->guest_facility_id)->first();
                        
                        if(!empty($guest_facility_res))
                        {
                            $gust_name = $guest_facility_res->title;
                        } else {
                            $gust_name = "";
                        }
                        
                        if(!empty($property_type_res))
                        {
                            $pType_name = $property_type_res->name;
                        } else {
                            $pType_name = "";
                        }
                        
                        if(!empty($value->upload_id))
                        {
                            $uploadId = url('uploads/uploadId/'.$value->upload_id;
                        } else {
                            $uploadId = "";
                        }
                        
                        $return_array[] = array(
                            "id"                   => $value->id,
                            "user_id"              => $value->user_id,
                            "property_type_id"     => $value->property_type,
                            "property_type_name"   => $pType_name,
                            "property_name"        => $value->property_name,
                            "location"             => $value->location,
                            "total_room"           => $value->total_room,
                            "guest_facility_id"    => $value->guest_facility_id,
                            "guest_facility_name"  => $gust_name,
                            "image"                => url('uploads/property/'.$value->image),
                            "upload_id"            => url('uploads/uploadId/'.$value->upload_id),
                            "status"               => $value->status,
                            "created"              => getDateFormat($value->created)
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Property not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }

    public function delete_property(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->property_id) AND $request->property_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkTask = DB::table('tbl_property')->where("id",$request->property_id)->count();

                    if($checkTask > 0)
                    {
                        DB::table('tbl_property')->where('id', '=', $request->property_id )->delete();
                        APISuccess("Property deleted successfully.");    
                    }
                    else
                    {
                        APIError("Property id not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    } 

    public function get_property_type(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "")
            {
                $res = DB::table('tbl_property_type')->select('*')->where("status","a")->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $return_array[] = array(
                            "id"      => $value->id,
                            "name"    => $value->name,
                            "status"  => $value->status,
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Property type not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }    

    public function get_property_details(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND isset($request->property_id) AND $request->property_id != "")
            {
                $res = DB::table('tbl_property')->select('*')->where("id",$request->property_id)->first();
                
                if(!empty($res))
                {
                    $property_type_res = DB::table('tbl_property_type')->select('*')->where("id",$res->property_type)->first();
                    
                    $guest_facility_res = DB::table('tbl_guest_facility')->select('*')->where("id",$res->guest_facility_id)->first();
                    
                    if(!empty($guest_facility_res))
                    {
                        $gust_name = $guest_facility_res->title;
                    } else {
                        $gust_name = "";
                    }
                    
                    if(!empty($property_type_res))
                    {
                        $pType_name = $property_type_res->name;
                    } else {
                        $pType_name = "";
                    }
                    
                    
                    $return_array[] = array(
                        "id"                   => $res->id,
                        "user_id"              => $res->user_id,
                        "property_type_id"     => $res->property_type,
                        "property_type_name"   => $pType_name,
                        "property_name"        => $res->property_name,
                        "location"             => $res->location,
                        "total_room"           => $res->total_room,
                        "guest_facility_id"    => $res->guest_facility_id,
                        "guest_facility_name"  => $gust_name,
                        "image"                => url('uploads/property/'.$res->image),
                        "upload_id"            => url('uploads/uploadId/'.$res->upload_id),
                        "status"               => $res->status,
                        "created"              => getDateFormat($res->created)
                    );   
                    
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Property not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }

    public function edit_property(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {

            if(isset($request->user_id) AND $request->user_id != "" AND
                isset($request->property_id) AND $request->property_id != "" AND
                isset($request->property_type) AND $request->property_type != "" AND
                isset($request->property_name) AND $request->property_name != "" AND 
                isset($request->location) AND $request->location != "" AND
                isset($request->total_room) AND $request->total_room != "" AND
                isset($request->guest_facility_id) AND $request->guest_facility_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {   
                    $uploadId = "uploadId_".time().'.'.$request->upload_id->extension();  
                    $request->upload_id->move(public_path('uploads/uploadId'), $uploadId);

                    $update_array = array(
                        "property_type"      => $request->property_type,
                        "property_name"      => $request->property_name,
                        "location"           => $request->location, 
                        "total_room"         => $request->total_room, 
                        "guest_facility_id"  => $request->guest_facility_id,
                        "created"            => created(),  
                    );
                    
                    if($fileName != "")
                    {
                        $update_array['upload_id'] = $fileName;
                    }
                        
                    DB::table('tbl_property')->where('id', $request->property_id)->update($update_array);

                    APISuccess("Property edited successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    
    public function add_module(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->module_name) AND $request->module_name != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $insert_array = array(
                        "user_id"            => $request->user_id,
                        "module_name"         => $request->module_name,
                        "status"             => "a",
                        "created"            => created(),  
                    );
                    $id = DB::table('tbl_module')->insertGetId($insert_array);
                    
                    APISuccess("Module added successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function get_module_name(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "")
            {
                $res = DB::table('tbl_module')->select('*')->where("status","a")->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $return_array[] = array(
                            "module_id"        => $value->id,
                            "user_id"          => $value->user_id,
                            "module_name"      => $value->module_name,
                            "status"           => $value->status,
                            "created"          => getDateFormat($value->created)
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Module not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
 
    public function delete_module(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->module_id) AND $request->module_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkTask = DB::table('tbl_module')->where("id",$request->module_id)->count();

                    if($checkTask > 0)
                    {
                        DB::table('tbl_module')->where('id', '=', $request->module_id )->delete();
                        APISuccess("Module deleted successfully.");    
                    }
                    else
                    {
                        APIError("Module id not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function get_permission_list(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "")
            {
                $res = DB::table('tbl_permission')->select('*')->where("status","a")->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $return_array[] = array(
                            "permission_id"    => $value->id,
                            "name"             => $value->name,
                            "status"           => $value->status,
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Permission not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }

    public function add_role(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->role_name) AND $request->role_name != "" AND
               isset($request->module_id) AND $request->module_id != "" AND
               isset($request->permission_id) AND $request->permission_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $insert_array = array(
                        "user_id"            => $request->user_id,
                        "role_name"          => $request->role_name,
                        "module_id"          => $request->module_id,
                        "permission_id"      => $request->permission_id, 
                        "status"             => "a",
                        "created"            => created(),  
                    );
                    $id = DB::table('tbl_role')->insertGetId($insert_array);
                    
                    APISuccess("Role added successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    public function edit_role(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->role_id) AND $request->role_id != "" AND
               isset($request->role_name) AND $request->role_name != "" AND
               isset($request->module_id) AND $request->module_id != "" AND
               isset($request->permission_id) AND $request->permission_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $update_array = array(
                        "role_name"          => $request->role_name,
                        "module_id"          => $request->module_id,
                        "permission_id"      => $request->permission_id, 
                    );
                    
                    DB::table('tbl_role')->where('id', $request->role_id)->update($update_array);
                    
                    APISuccess("Role edited successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    
    public function delete_role(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->role_id) AND $request->role_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkTask = DB::table('tbl_role')->where("id",$request->role_id)->count();

                    if($checkTask > 0)
                    {
                        DB::table('tbl_role')->where('id', '=', $request->role_id )->delete();
                        APISuccess("Role deleted successfully.");    
                    }
                    else
                    {
                        APIError("Role id not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function get_role_name(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "")
            {
                $res = DB::table('tbl_role')->select('*')->where("user_id",$request->user_id)->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        
                        $moduleRes = DB::table('tbl_module')->select('*')->where("id",$value->module_id)->first();
                        
                        if(!empty($res))
                        {
                            $module_name = $moduleRes->module_name;
                        } else {
                            $module_name = "";
                        }
                        
                        $permissionArray = explode(',',$value->permission_id);
                        $permission_array_res = "";
                        foreach ($permissionArray as $key => $value11) 
                        {
                            $permissionRes = DB::table('tbl_permission')->select('*')->where("id",$value11)->first();
                            $permission_array_res.= $permissionRes->name.",";
                        }
                        $return_array[] = array(
                            "role_id"        => $value->id,
                            "user_id"          => $value->user_id,
                            "role_name"        => $value->role_name,
                            "module_id"        => $value->module_id,
                            "module_name"      => $module_name,
                            "permission"       => rtrim($permission_array_res, ','),
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Role not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    
    
    public function get_role_details(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND isset($request->role_id) AND $request->role_id != "")
            {
                
                $res = DB::table('tbl_role')->select('*')->where("id",$request->role_id)->first();
                
                if(!empty($res))
                {
                        $moduleRes = DB::table('tbl_module')->select('*')->where("id",$res->module_id)->first();
                        
                        if(!empty($res))
                        {
                            $module_name = $moduleRes->module_name;
                        } else {
                            $module_name = "";
                        }
                        
                        $permissionArray = explode(',',$res->permission_id);
                        $permission_array_res = "";
                        foreach ($permissionArray as $key => $value11) 
                        {
                            $permissionRes = DB::table('tbl_permission')->select('*')->where("id",$value11)->first();
                            $permission_array_res.= $permissionRes->name.",";
                        }
                        
                        $return_array[] = array(
                            "role_id"          => $res->id,
                            "user_id"          => $res->user_id,
                            "role_name"        => $res->role_name,
                            "module_id"        => $res->module_id,
                            "module_name"      => $module_name,
                            "permission_id"    => $res->permission_id,
                            "permission"       => rtrim($permission_array_res, ','),
                        );   
                    
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Role not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }

    public function add_department(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->name) AND $request->name != "" AND
               isset($request->description) AND $request->description != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $insert_array = array(
                        "user_id"            => $request->user_id,
                        "name"               => $request->name,
                        "description"        => $request->description,
                        "status"             => "a",
                        "created"            => created(),  
                    );
                    $id = DB::table('tbl_department')->insertGetId($insert_array);
                    
                    APISuccess("Department added successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function get_department_list(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "")
            {
                $res = DB::table('tbl_department')->select('*')->where("user_id",$request->user_id)->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $return_array[] = array(
                            "department_id"    => $value->id,
                            "user_id"          => $value->user_id,
                            "name"             => $value->name,
                            "description"      => $value->description,
                            "status"           => $value->status,
                            "created"          => getDateFormat($value->created)
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Department not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }

    public function delete_department(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->department_id) AND $request->department_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkTask = DB::table('tbl_department')->where("id",$request->department_id)->count();

                    if($checkTask > 0)
                    {
                        DB::table('tbl_department')->where('id', '=', $request->department_id )->delete();
                        APISuccess("Department deleted successfully.");    
                    }
                    else
                    {
                        APIError("Department id not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function edit_department(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
                isset($request->department_id) AND $request->department_id != "" AND
               isset($request->name) AND $request->name != "" AND
               isset($request->description) AND $request->description != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $update_array = array(
                        "name"               => $request->name,
                        "description"        => $request->description,
                        "created"            => created(),  
                    );
                    DB::table('tbl_department')->where('id', $request->department_id)->update($update_array);
                    
                    APISuccess("Department edited successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function add_employee(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->name) AND $request->name != "" AND
               isset($request->email) AND $request->email != "" AND 
               isset($request->contact_no) AND $request->contact_no != "" AND
               isset($request->department_id) AND $request->department_id != "" AND
               isset($request->role_id) AND $request->role_id != "" AND
               isset($request->property_id) AND $request->property_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $insert_array = array(
                        "user_id"            => $request->user_id,
                        "name"               => $request->name,
                        "email"              => $request->email,
                        "contact_no"         => $request->contact_no,
                        "department_id"      => $request->department_id,
                        "role_id"            => $request->role_id,
                        "property_id"        => $request->property_id,
                        "task_id"            => "0", 
                        "status"             => "a",
                        "created"            => created(),  
                    );
                    $id = DB::table('tbl_employee')->insertGetId($insert_array);
                    
                    APISuccess("Employee added successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    public function edit_employee(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->employee_id) AND $request->employee_id != "" AND
               isset($request->name) AND $request->name != "" AND
               isset($request->email) AND $request->email != "" AND 
               isset($request->contact_no) AND $request->contact_no != "" AND
               isset($request->department_id) AND $request->department_id != "" AND
               isset($request->role_id) AND $request->role_id != "" AND
               isset($request->property_id) AND $request->property_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $update_array = array(
                        "name"               => $request->name,
                        "email"              => $request->email,
                        "contact_no"         => $request->contact_no,
                        "department_id"      => $request->department_id,
                        "role_id"            => $request->role_id,
                        "property_id"        => $request->property_id,
                    );
                    DB::table('tbl_employee')->where('id', $request->employee_id)->update($update_array);
                    
                    APISuccess("Employee edited successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function delete_employee(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->employee_id) AND $request->employee_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkTask = DB::table('tbl_department')->where("id",$request->employee_id)->count();

                    if($checkTask > 0)
                    {
                        DB::table('tbl_employee')->where('id', '=', $request->employee_id )->delete();
                        APISuccess("Employee deleted successfully.");    
                    }
                    else
                    {
                        APIError("Employee id not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function get_employee_list(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "")
            {
                $res = DB::table('tbl_employee')->select('*')->where("user_id",$request->user_id)->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $deptRes = DB::table('tbl_department')->select('*')->where("id",$value->department_id)->first();

                        $roleRes = DB::table('tbl_role')->select('*')->where("id",$value->role_id)->first();

                        $propertyRes = DB::table('tbl_property')->select('*')->where("id",$value->property_id)->first();
                        
                        if(!empty($deptRes))
                        {
                            $dept_name = $deptRes->name;
                        } else {
                            $dept_name = "";
                        }
                        
                        if(!empty($roleRes))
                        {
                            $role_name = $roleRes->role_name;
                        } else {
                            $role_name = "";
                        }
                        
                        if(!empty($propertyRes))
                        {
                            $property_name = $propertyRes->property_name;
                        } else {
                            $property_name = "";
                        }
                        
                        $return_array[] = array(
                            "id"                   => $value->id,
                            "user_id"              => $value->user_id,
                            "name"                 => $value->name,
                            "email"                => $value->email,
                            "contact_no"           => $value->contact_no,
                            "department_id"        => $value->department_id,
                            "department_name"      => $dept_name,
                            "role_id"              => $value->role_id,
                            "role_name"            => $role_name,
                            "property_id"          => $value->property_id,
                            "property_name"        => $property_name,
                            "status"               => $value->status,
                            "created"              => getDateFormat($value->created)
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Employee not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }

    public function assign_department(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->employee_id) AND $request->employee_id != "" AND
               isset($request->department_id) AND $request->department_id) 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkDept = DB::table('tbl_department')->where("id",$request->department_id)->count();

                    if($checkDept > 0)
                    {
                        $checkEmp = DB::table('tbl_employee')->where("id",$request->employee_id)->count();

                        if($checkEmp > 0)
                        {
                            $update_array = array(
                                "department_id" => $request->department_id,
                            );
                            DB::table('tbl_employee')->where('id', $request->employee_id)->update($update_array);
                            
                            APISuccess("Department assigned successfully.");
                        }
                        else
                        {
                            APIError("Employee not found.");
                        }
                    }
                    else
                    {
                        APIError("Department not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function assign_role(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->employee_id) AND $request->employee_id != "" AND
               isset($request->department_id) AND $request->department_id != "" AND 
               isset($request->role_id) AND $request->role_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkDept = DB::table('tbl_department')->where("id",$request->department_id)->count();

                    if($checkDept > 0)
                    {
                        $checkEmp = DB::table('tbl_employee')->where("id",$request->employee_id)->count();

                        if($checkEmp > 0)
                        {
                            $update_array = array(
                                "role_id" => $request->role_id,
                            );
                            DB::table('tbl_employee')->where('id', $request->employee_id)->update($update_array);
                            
                            APISuccess("Role assigned successfully.");
                        }
                        else
                        {
                            APIError("Employee not found.");
                        }
                    }
                    else
                    {
                        APIError("Department not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function assign_task(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->employee_id) AND $request->employee_id != "" AND
               isset($request->department_id) AND $request->department_id != "" AND 
               isset($request->task_id) AND $request->task_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkDept = DB::table('tbl_department')->where("id",$request->department_id)->count();

                    if($checkDept > 0)
                    {
                        $checkEmp = DB::table('tbl_employee')->where("id",$request->employee_id)->count();

                        if($checkEmp > 0)
                        {
                            $update_array = array(
                                "task_id" => $request->task_id,
                            );
                            DB::table('tbl_employee')->where('id', $request->employee_id)->update($update_array);
                            
                            APISuccess("Task assigned successfully.");
                        }
                        else
                        {
                            APIError("Employee not found.");
                        }
                    }
                    else
                    {
                        APIError("Department not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function add_room_service(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->room_title) AND $request->room_title != "" AND
               isset($request->room_price) AND $request->room_price != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $insert_array = array(
                        "user_id"            => $request->user_id,
                        "room_title"         => $request->room_title,
                        "room_price"         => $request->room_price,
                        "status"             => "a",
                        "created"            => created(),  
                    );
                    $id = DB::table('tbl_room_service')->insertGetId($insert_array);
                    
                    APISuccess("Room service added successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function edit_room_service(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->room_id) AND $request->room_id != "" AND 
               isset($request->room_title) AND $request->room_title != "" AND
               isset($request->room_price) AND $request->room_price != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkRoom = DB::table('tbl_room_service')->where("id",$request->room_id)->count();

                    if($checkRoom > 0)
                    {

                        $update_array = array(
                            "room_title"         => $request->room_title,
                            "room_price"         => $request->room_price,
                            "created"            => created(),  
                        );
                        DB::table('tbl_room_service')->where('id', $request->room_id)->update($update_array);
                        
                        APISuccess("Room service edited successfully.");
                    }
                    else
                    {
                        APIError("Room not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    } 

    public function get_room_list(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "")
            {
                $res = DB::table('tbl_room_service')->select('*')->where("user_id",$request->user_id)->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $return_array[] = array(
                            "id"                   => $value->id,
                            "user_id"              => $value->user_id,
                            "room_title"           => $value->room_title,
                            "room_price"           => $value->room_price,
                            "status"               => $value->status,
                            "created"              => getDateFormat($value->created)
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Room not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }

    public function delete_room_service(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->room_id) AND $request->room_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkRoom = DB::table('tbl_room_service')->where("id",$request->room_id)->count();

                    if($checkRoom > 0)
                    {
                        DB::table('tbl_room_service')->where('id', '=', $request->room_id )->delete();
                        APISuccess("Room service deleted successfully.");    
                    }
                    else
                    {
                        APIError("Room service id not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function change_room_status(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->room_id) AND $request->room_id != "" AND
               isset($request->status) AND ($request->status == "a" OR $request->status == "d")) 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkRoom = DB::table('tbl_room_service')->where("id",$request->room_id)->count();

                    if($checkRoom > 0)
                    {
                        $update_array = array(
                            "status" => $request->status,
                        );
                        DB::table('tbl_room_service')->where('id', $request->room_id)->update($update_array);
                        
                        APISuccess("Room status has been successfully changed.");
                    }
                    else
                    {
                        APIError("Room service not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function add_travel_agent(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {

            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->agent_name) AND $request->agent_name != "" AND
               isset($request->agency_id) AND $request->agency_id != "" AND 
               isset($request->travel_name) AND $request->travel_name != "" ) 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $fileName = "agent_travel_".time().'.'.$request->image->extension();  
                    $request->image->move(public_path('uploads/travel'), $fileName);
                    
                    $insert_array = array(
                        "user_id"       => $request->user_id,
                        "agent_name"    => $request->agent_name,
                        "agency_id"     => $request->agency_id,
                        "travel_name"   => $request->travel_name,
                        "image"         => $fileName, 
                        "status"        => "a",
                        "created"       => created(),  
                    );
                    
                    $id = DB::table('tbl_travel_agent')->insertGetId($insert_array);
                    
                    APISuccess("Travel agent added successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    
    public function edit_travel_agent(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            $fileName = "";
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->agent_id) AND $request->agent_id != "" AND
               isset($request->agent_name) AND $request->agent_name != "" AND
               isset($request->agency_id) AND $request->agency_id != "" AND 
               isset($request->travel_name) AND $request->travel_name != "" ) 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $travelcheck = DB::table('tbl_travel_agent')->where("id",$request->agent_id)->count();

                    if($travelcheck > 0)
                    {
                        if(isset($request->image))
                        {
                            $fileName = "agent_travel_".time().'.'.$request->image->extension();  
                            $request->image->move(public_path('uploads/travel'), $fileName);
                        }

                        $update_array = array(
                            "agent_name"   => $request->agent_name,
                            "agency_id"    => $request->agency_id,
                            "travel_name"  => $request->travel_name,  
                        );

                        if($fileName != "")
                        {
                            $update_array['image'] = $fileName;
                        }

                        DB::table('tbl_travel_agent')->where('id', $request->agent_id)->update($update_array);  

                        APISuccess("Travel agent edited successfully.");
                    }
                    else
                    {
                        APIError("Travel agent id not found.");     
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function get_travel_agent_list(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "")
            {
                $res = DB::table('tbl_travel_agent')->select('*')->where("user_id",$request->user_id)->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $agencyRes = DB::table('tbl_agency')->where("id",$value->agency_id)->first();    
                        
                        $return_array[] = array(
                            "agent_id"         => $value->id,
                            "user_id"          => $value->user_id,
                            "agent_name"       => $value->agent_name,
                            "agency_id"        => $value->agency_id,
                            "agency_name"      => (!empty($agencyRes)) ? $agencyRes->name : "",
                            "travel_name"      => $value->travel_name,
                            "image"            => url('uploads/travel/'.$value->image),
                            "status"           => $value->status,
                            "created"          => getDateFormat($value->created)
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Travel agent not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    
    public function get_travel_agent_details(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND isset($request->agent_id) AND $request->agent_id != "" )
            {
                $res = DB::table('tbl_travel_agent')->select('*')->where("id",$request->agent_id)->first();
                
                if(!empty($res))
                {
                    $agencyRes = DB::table('tbl_agency')->where("id",$res->agency_id)->first();    
                    
                    $return_array[] = array(
                        "agent_id"         => $res->id,
                        "user_id"          => $res->user_id,
                        "agent_name"       => $res->agent_name,
                        "agency_id"        => $res->agency_id,
                        "agency_name"      => (!empty($agencyRes)) ? $agencyRes->name : "",
                        "travel_name"      => $res->travel_name,
                        "image"            => url('uploads/travel/'.$res->image),
                        "status"           => $res->status,
                        "created"          => getDateFormat($res->created)
                    );   
                
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Travel agent not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }    
    
    public function delete_travel_agent(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->agent_id) AND $request->agent_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkAgent = DB::table('tbl_travel_agent')->where("id",$request->agent_id)->count();

                    if($checkAgent > 0)
                    {
                        DB::table('tbl_travel_agent')->where('id', '=', $request->agent_id )->delete();
                        APISuccess("Travel agent deleted successfully.");    
                    }
                    else
                    {
                        APIError("Agent id not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function create_coupon_code(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->coupon_code) AND $request->coupon_code != "" AND 
               isset($request->discount) AND $request->discount != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $insert_array = array(
                        "user_id"            => $request->user_id,
                        "coupon_code"        => $request->coupon_code,
                        "discount"           => $request->discount,
                        "discount_type"      => $request->discount_type,
                        "status"             => "a",
                        "created"            => created(),  
                    );

                    $id = DB::table('tbl_coupon_code')->insertGetId($insert_array);
                    
                    // if ($request->discount_type == 'percentage') 
                    // {
                    //     $insert_type = array(
                    //         "percentage" => $request->percentage
                    //     );
                    // }
                    // else
                    // {
                    //     $insert_type = array(
                    //         "amount" => $request->amount
                    //     );
                    // }

                    // DB::table('tbl_coupon_code')->where('id',$id)->update($insert_type);

                    APISuccess("Coupon code added successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function edit_coupon_code(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            $fileName = "";
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->coupon_code_id) AND $request->coupon_code_id != "" AND
               isset($request->coupon_code) AND $request->coupon_code != "" AND
               isset($request->discount) AND $request->discount != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkCoupon = DB::table('tbl_coupon_code')->where("id",$request->coupon_code_id)->count();

                    if($checkCoupon > 0)
                    {
                        $update_array = array(
                            "coupon_code"   => $request->coupon_code,
                            "discount"  => $request->discount,
                        );
                        
                        DB::table('tbl_coupon_code')->where('id', $request->coupon_code_id)->update($update_array);  

                        APISuccess("Coupon code edited successfully.");
                    }
                    else
                    {
                        APIError("Coupon code id not found.");     
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function get_coupon_code_list(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "")
            {
                $res = DB::table('tbl_coupon_code')->select('*')->where("user_id",$request->user_id)->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $return_array[] = array(
                            "coupon_code_id"   => $value->id,
                            "user_id"          => $value->user_id,
                            "coupon_code"      => $value->coupon_code,
                            "discount"         => $value->discount,
                            "status"           => $value->status,
                            "created"          => getDateFormat($value->created)
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Coupon code not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    
    public function get_coupon_code_details(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND isset($request->coupon_code_id) AND $request->coupon_code_id != "" )
            {
                $res = DB::table('tbl_coupon_code')->select('*')->where("id",$request->coupon_code_id)->first();
                
                if(!empty($res))
                {
                        $return_array[] = array(
                            "coupon_code_id"   => $res->id,
                            "user_id"          => $res->user_id,
                            "coupon_code"      => $res->coupon_code,
                            "discount"         => $res->discount,
                            "status"           => $res->status,
                            "created"          => getDateFormat($res->created)
                        );   
                    
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Coupon code not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }

    public function delete_coupon_code(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->coupon_code_id) AND $request->coupon_code_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkAgent = DB::table('tbl_coupon_code')->where("id",$request->coupon_code_id)->count();

                    if($checkAgent > 0)
                    {
                        DB::table('tbl_coupon_code')->where('id', '=', $request->coupon_code_id )->delete();
                        APISuccess("Coupon code deleted successfully.");    
                    }
                    else
                    {
                        APIError("Coupon code id not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function add_restaurant(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {

            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->name) AND $request->name != "" AND
               isset($request->total_room) AND $request->total_room != "" AND 
               isset($request->total_swimming_pool) AND $request->total_swimming_pool != "" ) 
            { 
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $fileName = "restaurant__".time().'.'.$request->image->extension();  
                    $request->image->move(public_path('uploads/restaurant'), $fileName);
                    
                    $insert_array = array(
                        "user_id"               => $request->user_id,
                        "name"                  => $request->name,
                        "total_room"            => $request->total_room,
                        "total_swimming_pool"   => $request->total_swimming_pool,
                        "image"                 => $fileName, 
                        "status"                => "a",
                        "created"               => created(),  
                    );
                    
                    $id = DB::table('tbl_restaurant')->insertGetId($insert_array);
                    
                    APISuccess("Restaurant added successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function edit_restaurant(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            $fileName = "";
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->restaurant_id) AND $request->restaurant_id != "" AND
               isset($request->name) AND $request->name != "" AND
               isset($request->total_room) AND $request->total_room != "" AND 
               isset($request->total_swimming_pool) AND $request->total_swimming_pool != "" ) 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkRestaurant = DB::table('tbl_restaurant')->where("id",$request->restaurant_id)->count();

                    if($checkRestaurant > 0)
                    {
                        if(isset($request->image))
                        {
                            $fileName = "restaurant__".time().'.'.$request->image->extension();  
                            $request->image->move(public_path('uploads/restaurant'), $fileName);
                        }

                        $update_array = array(
                            "name"                 => $request->name,
                            "total_room"           => $request->total_room,
                            "total_swimming_pool"  => $request->total_swimming_pool,  
                        );

                        if($fileName != "")
                        {
                            $update_array['image'] = $fileName;
                        }

                        DB::table('tbl_restaurant')->where('id', $request->restaurant_id)->update($update_array);  

                        APISuccess("Restaurant edited successfully.");
                    }
                    else
                    {
                        APIError("Restaurant id not found.");     
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    
    public function get_restaurant_list(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "")
            {
                $res = DB::table('tbl_restaurant')->select('*')->where("user_id",$request->user_id)->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $return_array[] = array(
                            "restaurant_id"       => $value->id,
                            "user_id"             => $value->user_id,
                            "name"                => $value->name,
                            "total_room"          => $value->total_room,
                            "total_swimming_pool" => $value->total_swimming_pool,
                            "image"               => url('uploads/restaurant/'.$value->image),
                            "status"              => $value->status,
                            "created"             => getDateFormat($value->created)
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Restaurant not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }

    public function delete_restaurant(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->restaurant_id) AND $request->restaurant_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkRestaurant = DB::table('tbl_restaurant')->where("id",$request->restaurant_id)->count();

                    if($checkRestaurant > 0)
                    {
                        DB::table('tbl_restaurant')->where('id', '=', $request->restaurant_id )->delete();
                        APISuccess("Restaurant deleted successfully.");    
                    }
                    else
                    {
                        APIError("Restaurant id not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    
    public function get_homepage_banner(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            $res = DB::table('tbl_banner')->select('*')->where("status","a")->get();
            
            if(count($res) > 0)
            {
                foreach ($res as $key => $value) 
                {
                    $return_array[] = array(
                        "banner_id"           => $value->id,
                        "title"               => $value->title,
                        "description"         => $value->description,
                        "image"               => url('uploads/banner/'.$value->image),
                        "status"              => $value->status,
                    );   
                }
                APISuccess("success",$return_array);
            }
            else
            {
                APIError("Banner not found.");
            }
            
            
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    public function get_about_us_page(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            $res = DB::table('tbl_cms')->select('*')->where("slug","about_us")->first();
            
            if(!empty($res))
            {
                $return_array[] = array(
                        "id"                  => $res->id,
                        "title"               => $res->title,
                        "description"         => $res->description,
                        "status"              => $res->status,
                );   
                
                APISuccess("success",$return_array);
            }
            else
            {
                APIError("About us not found.");
            }
            
            
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    
    public function get_privacy_policy_page(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            $res = DB::table('tbl_cms')->select('*')->where("slug","privacy_policy")->first();
            
            if(!empty($res))
            {
                $return_array[] = array(
                        "id"                  => $res->id,
                        "title"               => $res->title,
                        "description"         => $res->description,
                        "status"              => $res->status,
                );   
                
                APISuccess("success",$return_array);
            }
            else
            {
                APIError("Privacy policy not found.");
            }
            
            
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    
    public function get_term_condition_page(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            $res = DB::table('tbl_cms')->select('*')->where("slug","term_condition")->first();
            
            if(!empty($res))
            {
                $return_array[] = array(
                        "id"                  => $res->id,
                        "title"               => $res->title,
                        "description"         => $res->description,
                        "status"              => $res->status,
                );   
                
                APISuccess("success",$return_array);
            }
            else
            {
                APIError("Term and condition not found.");
            }
            
            
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    
    public function submit_contact_us(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->first_name) AND $request->first_name != "" AND
               isset($request->last_name) AND $request->last_name != "" AND
               isset($request->email) AND $request->email != "" AND
               isset($request->message) AND $request->message != "") 
            {
                $insert_array = array(
                    "first_name"      => $request->first_name,
                    "last_name"       => $request->last_name,
                    "email"           => $request->email,
                    "message"         => $request->message,
                    "created"         => created(),  
                );
                $id = DB::table('tbl_contact_us')->insertGetId($insert_array);
                
                APISuccess("Contact form submited successfully.");
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    
    public function get_testimonials(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            $res = DB::table('tbl_testimonials')->select('*')->where("status","a")->get();
            
            if(count($res) > 0)
            {
                foreach ($res as $key => $value) 
                {
                    $return_array[] = array(
                        "id"                  => $value->id,
                        "title"               => $value->title,
                        "description"         => $value->description,
                        "image"               => url('uploads/testimonials/'.$value->image),
                        "status"              => $value->status,
                    );   
                }
                APISuccess("success",$return_array);
            }
            else
            {
                APIError("Testimonials not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    
    public function add_agency(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->name) AND $request->name != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $insert_array = array(
                        "user_id"            => $request->user_id,
                        "name"               => $request->name,
                        "status"             => "a",
                        "created"            => created(),  
                    );
                    $id = DB::table('tbl_agency')->insertGetId($insert_array);
                    
                    APISuccess("Agency added successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    
    public function edit_agency(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->agency_id) AND $request->agency_id != "" AND
               isset($request->name) AND $request->name != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $update_array = array(
                        "name"               => $request->name,
                        "created"            => created(),  
                    );
                    DB::table('tbl_agency')->where('id', $request->agency_id)->update($update_array);
                    
                    APISuccess("Agency edited successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    
    public function get_agency_list(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "")
            {
                $res = DB::table('tbl_agency')->select('*')->where("user_id",$request->user_id)->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $return_array[] = array(
                            "agency_id"    => $value->id,
                            "user_id"          => $value->user_id,
                            "name"             => $value->name,
                            "status"           => $value->status,
                            "created"          => getDateFormat($value->created)
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Agency not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    
    public function get_agency_details(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND isset($request->agency_id) AND $request->agency_id != "" )
            {
                $res = DB::table('tbl_agency')->select('*')->where("id",$request->agency_id)->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $return_array[] = array(
                            "agency_id"    => $value->id,
                            "user_id"          => $value->user_id,
                            "name"             => $value->name,
                            "status"           => $value->status,
                            "created"          => getDateFormat($value->created)
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Agency not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    
    public function delete_agency(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->agency_id) AND $request->agency_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkTask = DB::table('tbl_agency')->where("id",$request->agency_id)->count();

                    if($checkTask > 0)
                    {
                        DB::table('tbl_agency')->where('id', '=', $request->agency_id )->delete();
                        APISuccess("Agency deleted successfully.");    
                    }
                    else
                    {
                        APIError("Agency id not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    
    public function add_agent_commission(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND 
               isset($request->agency_id) AND $request->agency_id != "" AND 
               isset($request->agent_id) AND $request->agent_id != "" AND 
               isset($request->commission_per) AND $request->commission_per != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $insert_array = array(
                        "user_id"            => $request->user_id,
                        "agency_id"          => $request->agency_id,
                        "agent_id"           => $request->agent_id,
                        "commission_per"     => $request->commission_per,
                        "status"             => "a",
                        "created"            => created(),  
                    );
                    $id = DB::table('tbl_agent_commission')->insertGetId($insert_array);
                    
                    APISuccess("Commission added successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    
    public function edit_agent_commission(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND 
               isset($request->commission_id) AND $request->commission_id != "" AND
               isset($request->agency_id) AND $request->agency_id != "" AND 
               isset($request->agent_id) AND $request->agent_id != "" AND 
               isset($request->commission_per) AND $request->commission_per != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $update_array = array(
                        "agency_id"          => $request->agency_id,
                        "agent_id"           => $request->agent_id,
                        "commission_per"     => $request->commission_per,
                    );
                    DB::table('tbl_agent_commission')->where('id', $request->commission_id)->update($update_array);
                    
                    APISuccess("Commission edited successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    
    public function get_agent_commission_list(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "")
            {
                $res = DB::table('tbl_agent_commission')->select('*')->where("user_id",$request->user_id)->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $agencyRes = DB::table('tbl_agency')->where("id",$value->agency_id)->first();
                        $agentRes = DB::table('tbl_travel_agent')->where("id",$value->agent_id)->first();
                        
                        $return_array[] = array(
                            "commission_id"    => $value->id,
                            "user_id"          => $value->user_id,
                            "agency_id"        => $value->agency_id,
                            "agency_name"      => (!empty($agencyRes)) ? $agencyRes->name : "",
                            "agent_id"         => $value->agent_id,
                            "agent_name"       => (!empty($agentRes)) ? $agentRes->agent_name : "",
                            "commission_per"   => $value->commission_per,
                            "status"           => $value->status,
                            "created"          => getDateFormat($value->created)
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Agent commission not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    
    public function get_agent_commission_details(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND isset($request->commission_id) AND $request->commission_id != "" )
            {
                $res = DB::table('tbl_agent_commission')->select('*')->where("id",$request->commission_id)->first();
                
                if(!empty($res) > 0)
                {
                     $agencyRes = DB::table('tbl_agency')->where("id",$res->agency_id)->first();
                        $agentRes = DB::table('tbl_travel_agent')->where("id",$res->agent_id)->first();
                    
                    $return_array[] = array(
                        "commission_id"    => $res->id,
                        "user_id"          => $res->user_id,
                        "agency_id"        => $res->agency_id,
                        "agency_name"      => (!empty($agencyRes)) ? $agencyRes->name : "",
                        "agent_id"         => $res->agent_id,
                        "agent_name"       => (!empty($agentRes)) ? $agentRes->agent_name : "",
                        "commission_per"   => $res->commission_per,
                        "status"           => $res->status,
                        "created"          => getDateFormat($res->created)
                    );   
                    
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Agent commission not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    
    public function delete_agent_commission(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND
               isset($request->commission_id) AND $request->commission_id != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $checkTask = DB::table('tbl_agent_commission')->where("id",$request->commission_id)->count();

                    if($checkTask > 0)
                    {
                        DB::table('tbl_agent_commission')->where('id', '=', $request->commission_id )->delete();
                        APISuccess("Agent commission deleted successfully.");    
                    }
                    else
                    {
                        APIError("Commission id not found.");
                    }
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    
    public function get_agent_of_agency(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND isset($request->agency_id) AND $request->agency_id != "")
            {
                $res = DB::table('tbl_travel_agent')->select('*')->where("agency_id",$request->agency_id)->get();
                
                if(count($res) > 0)
                {
                    foreach ($res as $key => $value) 
                    {
                        $return_array[] = array(
                            "agent_id"         => $value->id,
                            "agent_name"       => $value->agent_name,
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Agency Agent not found.");
                }
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    
    public function add_booking(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {

            if(isset($request->user_id) AND $request->user_id != "" AND
                isset($request->property_id) AND $request->property_id != "" AND
                isset($request->total_room) AND $request->total_room != "" AND
                isset($request->room_number) AND $request->room_number != "" AND
                isset($request->adult_guest) AND $request->adult_guest != "" AND
                isset($request->children) AND $request->children != "" AND
                isset($request->from_date) AND $request->from_date != "" AND
                isset($request->to_date) AND $request->to_date != "") 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {
                    $insert_array = array(
                        "user_id"          => $request->user_id,
                        "property_id"      => $request->property_id,
                        "total_room"       => $request->total_room,
                        "room_number"      => $request->room_number,
                        "adult_guest"      => $request->adult_guest,
                        "children"         => $request->children,
                        "from_date"        => $request->from_date,
                        "to_date"          => $request->to_date,
                        "status"             => "a",
                        "created"            => created(),  

                    );
                    $id = DB::table('tbl_booking')->insertGetId($insert_array);
                    
                    $members = json_decode($request->members, true);


                    if(isset($members['guest_name'] ))
                    {   
                        foreach($members["guest_name"] as $key => $value)
                        {   
                            $insert_array = array(
                                "booking_id" => $id,
                                "guest_name" => $members["guest_name"]$value,
                                "age"        => $members["age"][$key],
                            );
                            
                           DB::table('tbl_booking_guest')->insertGetId($insert_array);
                            
                        }
                    }
                        
                    APISuccess("Booking added successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }
    
    public function check_in(Request $request)
    {
        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {

            if(isset($request->user_id)         AND $request->guest_name     != "" AND
                isset($request->guest_name)     AND $request->guest_name     != "" AND
                isset($request->guest_id_proof) AND $request->guest_id_proof != "" AND
                isset($request->room_no)        AND $request->room_no        != "" AND
                isset($request->check_out_date) AND $request->check_out_date != "" ) 
            {
                $checkUser = DB::table('tbl_users')->where("id",$request->user_id)->count();

                if($checkUser > 0)
                {   
                    if(isset($request->guest_name))
                    {   
                        foreach($request->guest_name as $key => $value)
                        {   
                            $guestId_Proof = "guestId_".time().'.'.$request->guest_id_proof,[$key]->extension();  
                            $request->guest_id_proof,[$key]->move(public_path('uploads/checkIn'), $guestId_Proof);

                            $insert_array = array(
                                "guest_name"       => $request->guest_name,[$key],
                                "guest_id_proof"   => $guestId_Proof,
                                "room_no"          => $request->room_no,
                                "check_out_date"   => $request->check_out_date,
                                "status"           => "a",
                                "created"          => created(),  
                            );

                            $id = DB::table('tbl_check_in')->insertGetId($insert_array);
                        }
                    }
 
                    APISuccess("Check In successfully.");
                }
                else
                {
                    APIError("User not found.");
                }
            }
            else
            {
                APIError("Value missing.");
            }
        }
        else
        {
            APIError("Token Invalid");
        }
    }

    public function get_assigned_room(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->hotel_id) AND $request->hotel_id != "" ) 
            {
                $roomRes = DB::table('tbl_check_in')->select('*')->where("hotel_id",$request->hotel_id)->get();
                
                if(count($roomRes) > 0)
                {
                    foreach ($roomRes as $key => $value) 
                    {
                        $return_array[] = array(
                            "room_no"          => $value->room_no,
                        );   
                    }
                    APISuccess("success",$return_array);
                }
                else
                {
                    APIError("Room not found.");
                }
            }
            else
            {
                APIError("Missing Value");
            }   
        } 
        else
        {
            APIError("Token missing.");
        }  
    }

    public function check_out_guest_details(Request $request)
    {   
        $return_array = array();

        if (isset($request->token) AND $request->token == env('API_TOKEN')) 
        {   
            $guestListRes = DB::table("tbl_check_in")->select(DB::raw('*'))
              ->whereRaw('Date(check_out_date) = CURDATE()')
              ->where("status",'a')
              ->get();
            
            if(count($guestListRes) > 0)
            {
                foreach ($guestListRes as $key => $value) 
                {
                    $return_array[] = array(
                        "check_in_Id"   => $value->id,
                        "customer_name" => $value->guest_name,
                        "booking_date"  => date('d-m-Y', strtotime($value->created)),
                        "room_no"       => $value->room_no,
                    );   
                }
                APISuccess("success",$return_array);
            }
            else
            {
                APIError("Nothing to check out");
            }
        }
        else
        {
            APIError("Token missing");
        }
    }

    public function check_out(Request $request)
    {
        if (isset($request->token) AND $request->token == env('API_TOKEN')) 
        {
            if (isset($request->checkIn_id) AND $request->checkIn_id !='') 
            {
                $update_array = array(
                    "status" => 'd',
                );

                DB::table('tbl_check_in')->where('id', $request->checkIn_id)->update($update_array);

                $roomRes = DB::table('tbl_check_in')->select('*')->where("id",$request->checkIn_id)->get();

                foreach ($roomRes as $key => $value) 
                {
                    $update_room = array(
                        "assigned_rooom" => 'n',
                        "available_room" => 'y'
                    );

                    DB::table('tbl_rooms')->where(array('hotel_id',$value->hotel_id,'room_no', $value->room_no))->update($update_room);
                }
                
                APISuccess("Check Out successfully");
            }
            else
            {
                APIError("No Record found");
            }
        }
        else
        {
            APIError("Token missing");
        }
    }

    public function get_check_in_details(Request $request)
    {   
        $return_array = array();

        if (isset($request->token) AND $request->token == env('API_TOKEN')) 
        {
            if (isset($request->hotel_id) AND $request->hotel_id !='') 
            {
                if (isset($request->action) AND $request->action == 'today') 
                {
                    $today = date("Y-m-d");
                    
                    $bookingRes = DB::table('tbl_check_in')->select('*')->where(array('hotel_id' =>$request->hotel_id,'status' => 'a','check_in_date' => $today ))->get();

                    foreach ($bookingRes as $key => $value) 
                    {
                        $return_array[] = array(
                            "check_in_Id"    => $value->id,
                            "customer_name"  => $value->guest_name,
                            "booking_date"   => date('d-m-Y', strtotime($value->created)),
                            "booking_time"   => date('h:i:s', strtotime($value->created)),
                            "check_out_date" => date('d-m-Y', strtotime($value->check_out_date)),
                            "room_no"        => $value->room_no,
                        );   
                    }
                }
                
                if (isset($request->action) AND $request->action == 'week') 
                {
                    $today = date("Y-m-d");
                    
                    $guestListRes = DB::table("tbl_check_in")->select(DB::raw('*'))
                    ->whereRaw('Date(check_in_date) > DATE_SUB(DATE(NOW()), INTERVAL 1 WEEK)')
                    ->where("status",'a')
                    ->get();

                    foreach ($bookingRes as $key => $value) 
                    {
                        $return_array[] = array(
                            "check_in_Id"    => $value->id,
                            "customer_name"  => $value->guest_name,
                            "booking_date"   => date('d-m-Y', strtotime($value->created)),
                            "booking_time"   => date('h:i:s', strtotime($value->created)),
                            "check_out_date" => date('d-m-Y', strtotime($value->check_out_date)),
                            "room_no"        => $value->room_no,
                        );   
                    }
                }

                if (isset($request->action) AND $request->action == 'month') 
                {
                    $today = date("Y-m-d");
                    
                    $bookingRes = DB::table('tbl_check_in')->select('*')
                    ->where(array('hotel_id' =>$request->hotel_id,'status' => 'a'))
                    ->whereMonth('check_in_date', date('m'))
                    ->whereYear('check_in_date', date('Y'))
                    ->get();

                    foreach ($bookingRes as $key => $value) 
                    {
                        $return_array[] = array(
                            "check_in_Id"    => $value->id,
                            "customer_name"  => $value->guest_name,
                            "booking_date"   => date('d-m-Y', strtotime($value->created)),
                            "booking_time"   => date('h:i:s', strtotime($value->created)),
                            "check_out_date" => date('d-m-Y', strtotime($value->check_out_date)),
                            "room_no"        => $value->room_no,
                        );   
                    }
                }

                APISuccess("Check Out successfully",$return_array);
            }
            else
            {
                APIError("No Record found");
            }
        }
        else
        {
            APIError("Token missing");
        }
    }

    public function get_check_list_details(Request $request)
    {   
        $return_array = array();

        if (isset($request->token) AND $request->token == env('API_TOKEN')) 
        {
            if (isset($request->hotel_id) AND $request->hotel_id !='') 
            {
                $checkinRes = DB::table('tbl_check_in')->select('*')->where(array('hotel_id' =>$request->hotel_id))->get();

                foreach ($checkinRes as $key => $value) 
                {
                    $return_array[] = array(
                        "check_in_Id"    => $value->id,
                        "guest_name"     => $value->guest_name,
                        "guest_id_proof" => url('uploads/checkIn/'.$value->guest_id_proof),
                    );   
                }

                APISuccess("Check List Details",$return_array);
            }
            else
            {
                APIError("No Record found");
            }
        }
        else
        {
            APIError("Token missing");
        }
    }

    public function get_booking_details(Request $request)
    {   
        $return_array = array();

        if (isset($request->token) AND $request->token == env('API_TOKEN')) 
        {
            if (isset($request->hotel_id) AND $request->hotel_id !='') 
            {
                $checkinRes = DB::table('tbl_check_in')->select('*')->where(array('hotel_id' =>$request->hotel_id))->get();

                foreach ($checkinRes as $key => $value) 
                {   
                    $guestRes = DB::table('tbl_check_in_guest')->select('*')->where("checkin_id",$value->id)->first();

                    $totalGuest = DB::table('tbl_check_in_guest')->where("checkin_id",$value->id)->count();

                    $return_array[] = array(
                        "check_in_Id"    => $value->id,
                        "booking_date"   => $value->booking_date,
                        "customer_name"  => $guestRes->guest_name,
                        "total_peoples"  => $totalGuest,
                    );   
                }

                APISuccess("Check List Details",$return_array);
            }
            else
            {
                APIError("No Record found");
            }
        }
        else
        {
            APIError("Token missing");
        }
    }

    public function onwer_dashboard(Request $request)
    {
        $return_array = array();

        if(isset($request->token) AND $request->token == env('API_TOKEN'))
        {
            if(isset($request->user_id) AND $request->user_id != "" AND isset($request->property_id) AND $request->property_id != "")
            {
                $totalRoom = DB::table('tbl_property')->select('*')->where("id",$request->property_id)->first();
                
                $total_booked_room = DB::table("tbl_booking")->where("property_id",$request->property_id)->sum('total_room');
                
                    $return_array[] = array(
                            "total_room"         => $totalRoom->total_room,
                            "booked_room"        => $total_booked_room,
                            "available_room"     => $totalRoom->total_room-$total_booked_room,
                            "revenue"            => "0",
                            "upcoming"           => "0",
                        );   
                    APISuccess("success",$return_array);
            }
            else
            {
                APIError("User not found.");
            }
        } 
        else
        {
            APIError("Token missing.");
        }  
    }
    
}   
?>




