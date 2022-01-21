<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SiteSetting;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        if(auth()->guard('admin')->check())
        {
            return view('admin.manage_email_template.email_template'); 
        } 
        else 
        {
            return redirect('admin/login');
        }
    }

    public function addEmailTemplate(){
        return view('admin.manage_email_template.add_email_template');
    }
}
