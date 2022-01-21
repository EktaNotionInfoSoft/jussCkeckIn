<?php 
namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Crypt;
use DB;

class ChangepasswordController extends Controller
{
    public function index()
    {
        if(auth()->guard('admin')->check())
        {
            return view('admin.changepassword'); 
        } 
        else 
        {
            return redirect('admin/login');
        }
    }

    public function change_password(Request $request)
    {   
        $adminId = Auth::guard('admin')->user()->id;

        $checkPass = DB::table('tbl_admin')->where("id",$adminId)->first();

        $verify = password_verify($request->old_pass, $checkPass->password);
            
        if ($verify) 
        {  
            $password = password_hash($request->new_pass,PASSWORD_BCRYPT);

            $update_array = array(
                'password' =>$password,
            );

            DB::table('tbl_admin')->where('id', $adminId)->update($update_array);  

            return redirect('admin/dashboard');
        }
        else
        {
            return redirect('admin/changepassword');
        }
    }
}
?>



