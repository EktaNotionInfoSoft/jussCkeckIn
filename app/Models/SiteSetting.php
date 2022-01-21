<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;
    protected $table = 'tbl_site_settings';
    public $timestamps = false;
    protected $fillable = ['site_name', 'site_logo', 'admin_email','from_email','smtp_host','smtp_port','smtp_username','smtp_password','google_key'];   
}
