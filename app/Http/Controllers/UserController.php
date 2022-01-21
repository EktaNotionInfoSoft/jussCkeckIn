<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use DB;
use DataTables;

class UserController extends Controller
{
    public function index()
    {
        if(auth()->guard('admin')->check())
        {   
            return view('admin.manage_user.user'); 
        } 
        else 
        {
            return redirect('admin/login');
        }
    }


    public function userList(Request $request)
    {
        if ($request->ajax()) 
        {
            return Datatables::of($User = DB::table('tbl_users')->where("status","a"))
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    $button = '<input type="checkbox" checked data-toggle="toggle" data-on="Ready" data-off="Not Ready" data-onstyle="success" data-offstyle="danger">';
                    // $button .= '</td><td>';
                    // $button .= ' <label  class="switch" >';
                    // $button .= '  <input type="checkbox" id="'.$data->id.'" class="switch" ';
                    // if ($data->status == 'a') {
                    //     $button .= "checked";
                    // }
                    // $button .= '><span class="slider round"></span></label>';
                    // $button .= '</td></tr></table>';

                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

    }
}
