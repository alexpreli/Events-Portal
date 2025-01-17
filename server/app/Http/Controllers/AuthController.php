<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    private $table_name = 'user_sessions';  // Definim numele tabelei ca proprietate

    public function __construct()
{
    try {
        if (!Schema::hasTable($this->table_name)) {
            // Crearea tabelei cu coloana id setată ca AUTO_INCREMENT
            Schema::create($this->table_name, function ($table) {
                $table->bigIncrements('id'); // Asigură că id-ul este auto-increment
                $table->unsignedBigInteger('user_id');
                $table->string('token', 64)->unique();
                $table->enum('user_type', ['user', 'organizer']);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('expires_at')->nullable();
            });
        } else {
            // Verifică și adaugă coloanele lipsă
            $requiredColumns = [
                'user_id' => 'bigint(20) unsigned NOT NULL',
                'token' => 'varchar(64) NOT NULL',
                'user_type' => "enum('user','organizer') NOT NULL",
                'created_at' => 'timestamp NULL',
                'expires_at' => 'timestamp NULL'
            ];

            foreach ($requiredColumns as $column => $definition) {
                if (!Schema::hasColumn($this->table_name, $column)) {
                    DB::statement("ALTER TABLE {$this->table_name} ADD COLUMN {$column} {$definition}");
                }
            }

            // Verifică dacă există un index UNIQUE pentru token
            $hasUniqueToken = DB::select("SHOW INDEXES FROM {$this->table_name} WHERE Column_name = 'token' AND Non_unique = 0");
            if (empty($hasUniqueToken)) {
                DB::statement("UPDATE {$this->table_name} SET token = CONCAT(HEX(RAND()), id) WHERE token IS NULL OR token = ''");
                DB::statement("ALTER TABLE {$this->table_name} ADD UNIQUE (token)");
            }

            // Verifică dacă există PRIMARY KEY
            $hasPrimaryKey = DB::select("SHOW KEYS FROM {$this->table_name} WHERE Key_name = 'PRIMARY'");
            if (empty($hasPrimaryKey)) {
                DB::statement("ALTER TABLE {$this->table_name} ADD PRIMARY KEY (id)");
            }

            // Verifică dacă coloana id este AUTO_INCREMENT
            $result = DB::select("SHOW COLUMNS FROM {$this->table_name} WHERE Field = 'id'");
            if (!empty($result) && strpos($result[0]->Extra, 'auto_increment') === false) {
                DB::statement("ALTER TABLE {$this->table_name} MODIFY id bigint(20) unsigned NOT NULL AUTO_INCREMENT");
            }
        }
    } catch (\Exception $e) {
        \Log::error('Error in user_sessions table setup: ' . $e->getMessage());
    }
}


    public function organizerLogin(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $organizer = DB::table('organizers')
                ->where('email', $request->email)
                ->first();

            if (!$organizer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Account not found'
                ], 404);
            }

            if (!Hash::check($request->password, $organizer->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }

            if (!$organizer->is_approved) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Account not yet approved'
                ], 403);
            }

            DB::table($this->table_name)
                ->where('user_id', $organizer->id)
                ->where('user_type', 'organizer')
                ->delete();

            // create session token
            $token = bin2hex(random_bytes(32));

            DB::table($this->table_name)->insert([
                'user_id' => $organizer->id,
                'token' => $token,
                'user_type' => 'organizer',
                'created_at' => now(),
                'expires_at' => now()->addDays(7)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'id' => $organizer->id,
                    'email' => $organizer->email,
                    'first_name' => $organizer->first_name,
                    'last_name' => $organizer->last_name,
                    'is_organizer' => true
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function userLogin(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = DB::table('users')
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not found.'
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials.'
            ], 401);
        }

        // create session token
        $token = bin2hex(random_bytes(32));
        
        // Schimbă de la 'sessions' la 'user_sessions'
        DB::table($this->table_name)->insert([
            'user_id' => $user->id,
            'token' => $token,
            'user_type' => 'user',
            'created_at' => now(),
            'expires_at' => now()->addDays(7)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'is_organizer' => false
            ]
        ]);
    }
}