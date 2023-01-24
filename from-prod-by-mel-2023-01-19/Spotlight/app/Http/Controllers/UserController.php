<?php

namespace App\Http\Controllers;

use App\User;
use App\UserBase;
use App\RoleBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
     /**
     * Checks if a provided user attribute is avilable for use.
     *
     * @return \Illuminate\Http\Response
     */
    public function available(Request $request)
    {
        if(!$request->has('name') && !$request->has('email')) {
            return response()->json(false);
        }

        $whereClause = [];
        array_push($whereClause , ['enabled', '=', 1]);
        if($request->has('name')) {
            array_push($whereClause , ['name', '=', $request->name]);
        }
        if($request->has('email')) {
            array_push($whereClause , ['email', '=', $request->email]);
        }

        if($request->has('id')) {
            array_push($whereClause , ['id', '!=', $request->id]);
        }

        return response()->json((User::where($whereClause)->count() == 0));
    }

    /**
     * Gets a list of users.
     *
     * @return \Illuminate\Http\Response
     */
    public function get() {
        return User::with('role', 'role.type')
					->where('enabled', 1)
					->whereHas('role', function($query) {
						$query->where('typeId', '<=',  auth()->user()->role->typeId);
					})
					->orderBy('name')
					->get();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = $this->get();
        $currentUser = UserBase::where('id', auth()->user()->id)->first();
        return view('admin.security.users.index', compact('currentUser', 'users'));
    }

    /**
     * Creates a new user in the system
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email'],
            'roleId' => ['required', 'integer', 'exists:vw_role,id']
        ]);

        $role = RoleBase::where([['id', $request->roleId],['deleted', '=' , 0]])->first();

        if($role->typeId > auth()->user()->role->typeId) {
            return response()->json("Insufficient access", 400);
        }

		$userCheck = User::where("email", $request->email)->first();
		if ($userCheck) {
			if (!$request->reactivate_user) {
				return response()->json((object)["code" => "disabled_user_exists"], 419);
			}

			$userCheck->name = $request->name;
			$userCheck->roleId = $request->roleId;
			$userCheck->enabled = true;
			$userCheck->save();
		}
		else {
			User::create([
				'name' => $request->name,
				'email' => $request->email,
				'roleId' => $request->roleId,
			]);
		}

        if(request()->ajax()) return json_encode(['success' => true]);

        return redirect('/admin/users')->with('success', 'New user account has been created successfully');
    }

    /**
     * Updates an existing user on the system.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $user = User::with('role')->where('id', $id)->first();
        abort_if(auth()->user()->role->typeId < $user->role->typeId, 403);

        $role = RoleBase::where([['id', $request->roleId],['deleted', '=' , 0]])->first();
        if($role->typeId > auth()->user()->role->typeId) {
            return response()->json("Insufficient access", 400);
        }

        $user->update($request->validate([
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:vw_user,email,'.$user->id],
            'roleId' => ['required', 'integer', 'exists:vw_role,id'],
        ]));

        if(request()->ajax()) {
            return json_encode(['success' => true]);
        }

        return redirect('/admin/users')->with('success', 'User account updated successfully');
    }

    /**
     * Deletes an existing user from the system.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $user = User::with('role')->where('id', $id)->first();
        abort_if(auth()->user()->role->typeId < $user->role->typeId, 403);

        if($user->id == auth()->user()->id) {
            return response()->json("You cannot delete your own account", 400);
        }

		$user->enabled = false;
		$user->save();
        if(request()->ajax()) return json_encode(['success' => true]);

        return redirect('/admin/users')->with('success', 'User account has been deleted');
    }

}
