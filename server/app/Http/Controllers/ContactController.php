<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ContactMessage;
use Mailjet\Client;
use Mailjet\Resources;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        if (!Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email');
                $table->text('message');
                $table->timestamps();
            });
        }

        $message = ContactMessage::create([
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message,
        ]);

        // send email to admin
        $mailjet = new Client(env('MAILJET_API_KEY'), env('MAILJET_API_SECRET'), true, ['version' => 'v3.1']);

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "alexleoca7@gmail.com", // sender email address
                        'Name' => $message['name']
                    ],
                    'To' => [
                        [
                            'Email' => "alexleoca7@gmail.com", // recipient email address
                            'Name' => "Prelipceanu Alexandru"
                        ]
                    ],
                    'Subject' => "EventsPortal - Contact Message from " . $message['name'],
                    'TextPart' => json_encode([
                        'Name' => $message['name'],
                        'Email' => "alexleoca7@gmail.com", // recipient email address
                        'Message' => $message['message']
                    ], JSON_PRETTY_PRINT),

                    'HTMLPart' => "
                        <h3>Contact Message Details</h3>
                        <ul>
                            <li><strong>Name:</strong> " . e($message['name']) . "</li>
                            <li><strong>Email:</strong> " . e($message['email']) . "</li>
                            <li><strong>Message:</strong></li>
                            <p>" . nl2br(e($message['message'])) . "</p>
                        </ul>
                    ",
                ]
            ]
        ];

        $response = $mailjet->post(Resources::$Email, ['body' => $body]);

        if ($response->success()) {
            return response()->json([
                'message' => 'Contact Data was sent successfully!',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Error occurred while sending contact data!',
            ], 500);
        }
    }
}
