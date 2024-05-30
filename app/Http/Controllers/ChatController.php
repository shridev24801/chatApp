<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pusher\Pusher;
use App\Models\User;
use App\Events\PrivateMessageEvent;
use App\Events\MessageSeenEvent;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class ChatController extends Controller
{
    //

    // public function index()
    // {
    //     return view('chat');
    // }

    // public function sendMessage(Request $request)
    // {
    //     // $recipientUserId = $request->input('recipient_id');
    //     // $recipientChannel = 'private-chat.' . $recipientUserId;

    //     // $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), [
    //     //     'cluster' => env('PUSHER_APP_CLUSTER'),
    //     //     'useTLS' => true
    //     // ]);

    //     // $data = [
    //     //     'message' => $request->input('message')
    //     // ];

    //     // $pusher->trigger($recipientChannel, 'private-message', $data);

    //     // return response()->json(['status' => 'Message sent']);

    //     $recipientUserId = $request->input('recipient_id');
    // $message = $request->input('message');

    // event(new PrivateMessageEvent($recipientUserId, $message));

    // return response()->json(['status' => 'Message sent']);
    // }
        

        // public function showChat(User $recipient)
        // {
        //     // You can pass the recipient user to the view if needed
        //     $CurrentUser  = Auth::user();
        //     $CurrentUserId  = Auth::user()->id;
        //     $getlist = Message::where('sender',$CurrentUserId)->pluck('receiver')->toArray();
        //     $chatlistId = array_unique($getlist);
        //     $chatlistUserData = User::whereIn('id',$chatlistId)->get();
           
        //     return view('chat', ['recipient' => $recipient,'chatlistUserData'=> $chatlistUserData]);
        // }




        public function showChat(User $recipient = null)
{
    $currentUser = Auth::user();
    $currentUserId = $currentUser->id;

    if ($recipient) {
        // Fetch data for the specific recipient
        $chatlistUserData = $this->getChatListUserData($currentUserId);
        $getChat = Message::where('sender', $currentUserId)
            ->where('receiver', $recipient->id)
            ->orWhere('sender', $recipient->id)
            ->where('receiver', $currentUserId)
            ->get();
            $unreadMessagesCount = [];
            foreach ($chatlistUserData as $chatUser) {
                $unreadMessagesCount[$chatUser->id] = Message::where('receiver', $currentUserId)
                    ->where('sender', $chatUser->id)
                    ->where('read_status', 0)
                    ->count();
            }
            if(!empty($chatlistId)){
                $getUsers = User::whereNot('id',$currentUserId);
            }else{
                $getUsers = [];
            }
            // dd($unreadMessagesCount);
        return view('chat', ['recipient' => $recipient, 'chatlistUserData' => $chatlistUserData, 'allMessages' => $getChat, 'unreadMessagesCount' => $unreadMessagesCount,'getUsers'=>$getUsers]);
       
    } else {
        // Fetch chat list without specific recipient data
        $chatlistUserData = $this->getChatListUserData($currentUserId);
        if(!empty($chatlistId)){
            $getUsers = User::whereNot('id',$currentUserId);
        }else{
            $getUsers = [];
        }

        $unreadMessagesCount = [];
        foreach ($chatlistUserData as $chatUser) {
            $unreadMessagesCount[$chatUser->id]= Message::where('receiver', $currentUserId)
                ->where('sender', $chatUser->id)
                ->where('read_status', 0)
                ->count();
        }
        if(!empty($chatlistId)){
            $getUsers = User::whereNot('id',$currentUserId);
        }else{
            $getUsers = [];
        }
        // dd($unreadMessagesCount);
       
        return view('chat', ['chatlistUserData' => $chatlistUserData,'unreadMessagesCount' => $unreadMessagesCount,'getUsers'=>$getUsers]);
    }
}

public function getChatListUserData($userId)
{
    $chatlistIds = Message::where('sender', $userId)
        ->pluck('receiver')
        ->toArray();
    $uniqueChatlistIds = array_unique($chatlistIds);
    return User::whereIn('id', $uniqueChatlistIds)->get();
}

public function markMessagesRead(Request $request)
{
    $currentUserId = Auth::id();
    $chatId = $request->input('chat_id');

    $message =  Message::where('receiver', $currentUserId)
    ->where('sender', $chatId)
    ->where('read_status', 0)->first();
//    dd($message->id);

    // Update read status of messages
    $status =  Message::where('receiver', $currentUserId)
        ->where('sender', $chatId)
        ->where('read_status', 0)
        ->update(['read_status' => 1]);

       
    if(!empty($message)){
        event(new MessageSeenEvent($message->id,$chatId,$currentUserId,$status));
    }
   
    // Optionally, you can return a response indicating success
    return response()->json(['status' => 'Messages marked as read']);
}


//         public function showChat(User $recipient = null)
// {
//     $CurrentUser  = Auth::user();
//     $CurrentUserId  = Auth::user()->id;

//     if ($recipient) {
//         // Fetch data for the specific recipient
//         $getlist = Message::where('sender', $CurrentUserId)
//             ->pluck('receiver')
//             ->toArray();
//         $chatlistId = array_unique($getlist);
//         $chatlistUserData = User::whereIn('id', $chatlistId)->get();
       
//         $getChat=  Message::where('sender', $CurrentUserId)->where('receiver', $recipient->id)
//         ->orwhere('sender',$recipient->id)->where('receiver',$CurrentUserId)
//         ->get();
        
//         return view('chat', ['recipient' => $recipient, 'chatlistUserData' => $chatlistUserData,'allMessages'=>$getChat,'getUsers'=>$getUsers]);
//     } else {
//         // Fetch chat list without specific recipient data
//         $getlist = Message::where('sender', $CurrentUserId)->pluck('receiver')->toArray();
//         $chatlistId = array_unique($getlist);
//         if(!empty($chatlistId)){
//             $getUsers = User::whereNot('id',$CurrentUserId);
//         }else{
//             $getUsers = [];
//         }
//         $chatlistUserData = User::whereIn('id', $chatlistId)->get();
//         return view('chat', ['chatlistUserData' => $chatlistUserData,'getUsers'=>$getUsers]);
//     }
// }

        public function sendMessage(Request $request, User $recipient)
        {
            // Validate the request
            // dd($recipient,$request);
            // $request->validate([
            //     'message' => 'required|string',
            // ]);
            
            $data = array();
            $data['sender'] = Auth::user()->id;
            $data['receiver'] = $recipient->id;
            $data['message'] = $request->message;
            $data['read_status'] = 0;

            $filePath = "";


            if($request->file('attactment')){

                $file = $request->file('attactment');
                $mimeType = $file->getMimeType();
                
                if (str_starts_with($mimeType, 'image/')) {
                    $directory = 'chatimage';
                } elseif (str_starts_with($mimeType, 'video/')) {
                    $directory = 'chatvideo';
                } else {
                    $directory = 'chatfiles';
                }
                $filePath = $file->store($directory, 'public');
                $data['attachment'] = $filePath;
            

            }
            if ($request->file('audio')) {
            
                $file = $request->file('audio');
                $filePath = $file->storeAs('chataudio', 'recording-' . time() . '.mp3', 'public'); // Store with .mp3 extension

                $data['attachment'] = $filePath;
            }

            $message = Message::create($data);
            $user = User::findOrFail($recipient->id);
            $status = $user->active_status ?? 0;
        // Broadcast the message to the recipient's private channel
        event(new PrivateMessageEvent(Auth::user()->id,$recipient->id, $request->message,0,$filePath));
            

        // Optionally, you can return a response indicating success
        return response()->json(['status' => 'Message sent','active_status'=> $status,'attachment'=> $filePath]);
        }

        public function setActiveStatus(Request $request)
        {
            $activeStatus = $request['status'];
            $status = User::where('id', $request['id'])->update(['active_status' => $activeStatus]);
            return response()->json([
                'status' => $status,
            ], 200);
        }

        public function getUserStatus($id)
    {
        // Fetch the user's status
        $user = User::findOrFail($id);
        $status = $user->active_status ?? 0; // Default to 0 if status is not set

        return response()->json([
            'status' => $status,
        ]);
    }

    public function getLastSeen($id)
{
    $user = User::find($id);
    $formattedLastSeen = Message::timeAgo($user->last_seen);
    if($user) {
        return response()->json(['last_seen' => $formattedLastSeen]);
    } else {
        return response()->json(['error' => 'User not found'], 404);
    }
}
public function updateLastSeen(Request $request)
{
    $user = Auth::user();
    if ($user) {
        $user->last_seen = now();
        $user->save();

        return response()->json(['status' => 'success', 'last_seen' => $user->last_seen]);
    } else {
        return response()->json(['status' => 'error', 'message' => 'User not authenticated'], 401);
    }
}
public function deleteMessage($id)
{
    
    $message = Message::find($id);

    if ($message && $message->sender == auth()->user()->id) {
        // Check if the message has an attachment
        if ($message->attachment) {
            // Delete the file from the storage
            Storage::delete($message->attachment);
        }

        // Delete the message from the database
        $message->delete();

        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false], 403);
}
}

