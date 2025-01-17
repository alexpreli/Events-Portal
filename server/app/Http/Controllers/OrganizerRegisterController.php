<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

class OrganizerRegisterController extends Controller
{
    private function createOrganizersTableIfNotExists()
    {
        if (!Schema::hasTable('organizers')) {
            Schema::create('organizers', function (Blueprint $table) {
                $table->id();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('phone_number');
                $table->string('country');
                $table->string('city');
                $table->string('address');
                $table->string('postal_code')->nullable();
                $table->string('invite_code');
                $table->boolean('is_approved')->default(false);
                $table->string('email')->unique();
                $table->string('password');
                $table->string('organizer_events')->nullable();
                $table->string('organizer_tickets')->nullable();
                $table->string('organizer_created_events')->nullable();
                $table->timestamps();
            });

            return true;
        } else {
            if (!Schema::hasColumn('organizers', 'organizer_events')) {
                Schema::table('organizers', function (Blueprint $table) {
                    $table->string('organizer_events')->nullable()->after('invite_code');
                });
            }

            if (!Schema::hasColumn('organizers', 'organizer_tickets')) {
                Schema::table('organizers', function (Blueprint $table) {
                    $table->string('organizer_tickets')->nullable()->after('organizer_events');
                });
            }

            if (!Schema::hasColumn('organizers', 'organizer_created_events')) {
                Schema::table('organizers', function (Blueprint $table) {
                    $table->string('organizer_created_events')->nullable()->after('organizer_tickets');
                });
            }

            Schema::table('organizers', function (Blueprint $table) {
                $table->string('postal_code')->nullable()->change();
            });
        }

        return false;
    }

    public function organizerRegister(Request $request)
    {
        $tableCreated = $this->createOrganizersTableIfNotExists();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:organizers,email',
            'password' => 'required|string|min:8',
            'phone_number' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'invite_code' => 'required|string|in:ABC321',
        ], [
            'email.unique' => 'The email address is already registered.',
            'invite_code.in' => 'The invite code is invalid.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // verifica daca exista emailul in tabela organizers
        if (DB::table('organizers')->where('email', $request->email)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email already registered'
            ], 422);
        }

        // verifica invite code-ul
        if ($request->invite_code !== 'ABC321') {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid invite code'
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
            $organizer = DB::table('organizers')->insert([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'country' => $request->country,
                'city' => $request->city,
                'address' => $request->address,
                'postal_code' => $request->postal_code,
                'invite_code' => $request->invite_code,
                'is_approved' => false,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful! Please wait for admin approval.',
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
