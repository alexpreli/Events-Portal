<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

class UserRegisterController extends Controller
{
    private function createUsersTableIfNotExists()
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('phone_number');
                $table->string('country');
                $table->string('city');
                $table->string('address');
                $table->string('postal_code')->nullable();
                $table->string('email')->unique();
                $table->string('password');
                $table->string('user_events')->nullable();
                $table->string('user_tickets')->nullable();
                $table->timestamps();
            });
            return true;
        } else {
            if (!Schema::hasColumn('users', 'postal_code')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('postal_code')->nullable()->after('address');
                });
            } else {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('postal_code')->nullable()->change();
                });
            }

            if (!Schema::hasColumn('users', 'user_events')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('user_events')->nullable()->after('postal_code');
                });
            }

            if (!Schema::hasColumn('users', 'user_tickets')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('user_tickets')->nullable()->after('user_events');
                });
            }


        }
        return false;
    }

    public function userRegister(Request $request)
    {
        $tableCreated = $this->createUsersTableIfNotExists();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'phone_number' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
        ], [
            'email.unique' => 'The email address is already registered.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // verifica daca exista emailul in tabela users
        if (DB::table('users')->where('email', $request->email)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email already registered'
            ], 422);
        }

        // verifica daca password este mai mic decat 8 caractere
        if (strlen($request->password) < 8) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password must be at least 8 characters long'
            ], 422);
        }

        try {
            $user = DB::table('users')->insert([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'country' => $request->country,
                'city' => $request->city,
                'address' => $request->address,
                'postal_code' => $request->postal_code ?? null,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful! You are now an user.',
                'tableCreated' => $tableCreated
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
