<?php 
namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        if(auth()->guard('admin')->check())
        {
            return view('admin.dashboard'); 
        } 
        else 
        {
            return redirect('admin/login');
        }
    }
}
?>