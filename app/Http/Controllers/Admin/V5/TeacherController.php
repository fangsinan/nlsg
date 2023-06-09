<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\TeacherServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends ControllerBackend
{
    public function list(Request $request): JsonResponse
    {
        return $this->getRes((new TeacherServers())->list($request->input()));
    }

    public function create(Request $request): JsonResponse
    {
        return $this->getRes((new TeacherServers())->create($request->input()));
    }

    public function info(Request $request): JsonResponse
    {
        return $this->getRes((new TeacherServers())->info($request->input()));
    }
}
