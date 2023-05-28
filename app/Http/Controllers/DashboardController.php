<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use function Symfony\Component\Console\Helper\fillNextRows;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     * @param Request $request
     */
    public function __invoke(Request $request)
    {
        ray()->showQueries();

        return view("dashboard", [
            "users" => User::query()
                ->where('admin', '=', false)
                ->search(\request()->search)
                ->when(
                    \request()->filled('column'),
                    fn(Builder $q) => $q->orderBy(
                        \request()->column,
                        \request()->direction ? : 'ASC'
                    )
                )
                ->get()
        ]);
    }
}
