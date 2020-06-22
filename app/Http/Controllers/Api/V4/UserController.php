<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
use JWTAuth;
use App\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
        $this->user = auth('api')->user();
        dd($this->user);
    }

    public function index()
    {

    }


}
