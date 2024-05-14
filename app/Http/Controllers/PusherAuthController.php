<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pusher\Pusher;

class PusherAuthController extends Controller
{
    //
    public function authenticate(Request $request)
    {
        // Authenticate the user and return the authentication response
        // Implement your authentication logic here
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $socketId = $request->input('socket_id');
        $channelName = $request->input('channel_name');

        // Here, you authenticate the user and generate the auth string
        $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'));
        $auth = $pusher->socket_auth($channelName, $socketId, $user->id);
           // Generate presence data
           $presenceData = [
            'user_id' => $user->id,
            'user_info' => [
                'name' => $user->name,
                'email' => $user->email,
                // Add any other user information you need
            ]
        ];

        // Authenticate for presence channel
        $auth = $pusher->presence_auth($channelName, $socketId, $user->id, $presenceData);


        return response($auth);
    }
}
