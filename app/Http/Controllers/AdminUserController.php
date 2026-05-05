<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = Users::with('roles','office')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function toggle($id)
    {
        $user = Users::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success','User status updated');
    }
}
