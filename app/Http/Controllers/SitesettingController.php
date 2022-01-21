<?php 
namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SiteSetting;


class SitesettingController extends Controller
{
    public function index(Request $request)
    {
        if(auth()->guard('admin')->check())
        {
            $ss = SiteSetting::find(1);
            return view('admin.sitesetting',compact('ss')); 
        } 
        else 
        {
            return redirect('admin/login');
        }
    }

    public function addUpdateSetting(Request $request){
        $request->validate([
            'site_name' => 'required',
            'site_logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'admin_email' => 'required',
            'from_email' => 'required',
            'smtp_host' => 'required',
            'smtp_port' => 'required',
            'smtp_username' => 'required',
            'smtp_password' => 'required',
            'google_key' => 'required',
        ]);

        $ss = SiteSetting::find(1);
        $ss->site_name = $request->site_name;
        if($request->site_logo != ''){        
            $path = 'uploads/siteSettingLogo/';
  
            //code for remove old file
            if($ss->site_logo != ''  && $ss->site_logo != null){
                 $file_old = $path.$ss->site_logo;
                 unlink($file_old);
            }
  
            //upload new file
            $file = $request->site_logo;
            $filename = $file->getClientOriginalName();
            $file->move($path, $filename);
  
            //for update in table
            $ss->update(['site_logo' => $filename]);
       }
        $ss->admin_email = $request->admin_email;
        $ss->from_email = $request->from_email;
        $ss->smtp_host = $request->smtp_host;
        $ss->smtp_port = $request->smtp_port;
        $ss->smtp_username = $request->smtp_username;
        $ss->smtp_password = $request->smtp_password;
        $ss->google_key = $request->google_key;
        $ss->save();
        return view("admin.sitesetting",compact('ss'))->with('success','Site Settings Data Successfully Submitted!!');
    }
}
?>



