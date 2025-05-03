<?php

namespace App\Http\Controllers;

use App\Custom\Formatter;
use App\Models\User;
use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::query()->simplePaginate(10);
        return Formatter::apiResponse(200, "User list retrieved", $users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'username' => "required|string|unique:users,username",
            'password' => "required|string|min:6",
            "role" => "required|string|in:admin,user"
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, "Validation failed", null, $validator->errors()->all());
        }

        $validated = $validator->validate();
        $validated["password"] = Hash::make($validated["password"]);

        $newUser = User::query()->create($validated);
        return Formatter::apiResponse(200, "User created", $newUser);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::query()->find($id);
        if (is_null($user)) {
            return Formatter::apiResponse(404, "User not found");
        }
        return Formatter::apiResponse(200, "User found", $user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::query()->find($id);
        if (is_null($user)) {
            return Formatter::apiResponse(404, "User not found");
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'username' => "sometimes|string|unique:users,username," . $id,
            'password' => "sometimes|string|min:6",
            "role" => "sometimes|string|in:admin,user"
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, "Validation failed", null, $validator->errors()->all());
        }

        $validated = $validator->validate();
        if (isset($validated["password"])) {
            $validated["password"] = Hash::make($validated["password"]);
        }

        $user->update($validated);
        return Formatter::apiResponse(200, "User updated", User::query()->find($id));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::query()->find($id);
        if (is_null($user)) {
            return Formatter::apiResponse(404, "User not found");
        }
        $user->delete();
        return Formatter::apiResponse(200, "User deleted");
    }
}
