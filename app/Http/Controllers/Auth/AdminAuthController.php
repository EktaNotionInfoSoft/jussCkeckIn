<?php 
namespace App\Http\Controllers\Auth;

use Validator;
use Session;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Admin;
use Auth;

class AdminAuthController extends Controller
{
    protected $redirectTo = '/admin/login';

    
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function getLogin()
    {
        
        if(auth()->guard('admin')->check())
        {
            return redirect('admin/dashboard'); 
        } 
        else 
        {
            return view('admin.login');
        }
    }

    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (auth()->guard('admin')->attempt(['email' => $request->input('email'), 'password' => $request->input('password')]))
        {
            $user = auth()->guard('admin')->user();
            
            \Session::put('success','You are Login successfully!!');
            return redirect()->route('dashboard');
            
        } else {
            return back()->with('error','Username and password wrong!!');
        }
    }

    public function logout()
    {
        auth()->guard('admin')->logout();
        \Session::flush();
        \Session::put('success','You are logout successfully');        
        return redirect(route('adminLogin'));
    }
}
?>