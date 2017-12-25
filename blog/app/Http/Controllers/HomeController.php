<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class HomeController extends BaseController
{
//    public function index($name, $id = null)
//    {
//        return 'Hello, '.$name.', '.$id;
//    }
    public function getIndex($username)
    {
        return 'Hello '.$username;
    }
}