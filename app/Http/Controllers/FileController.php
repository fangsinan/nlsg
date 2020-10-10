<?php

namespace App\Http\Controllers;

class FileController extends Controller
{
    function browse($file_name)
    {
        return response()->file(public_path() . '/image/'.$file_name);
    }
}
