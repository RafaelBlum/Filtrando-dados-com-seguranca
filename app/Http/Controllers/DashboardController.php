<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     * @param Request $request
     */
    public function __invoke(Request $request)
    {
        return view("dashboard", [
            "users" => User::query()
                ->where('admin', '=', false)
                ->where('name', 'like', '%'. request()->search . '%')
                ->orWhere('email', 'like', '%'. request()->search . '%')
                ->get()
        ]);
    }
}
