<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Message extends Model
{
    use HasFactory;

    protected $fillable  = ['sender','receiver', 'message','read_status'];

    public static function latestMessage($id)
    {
        $chats = Message::where('sender', Auth::user()->id)->where('receiver', $id)
                    ->orWhere('sender', $id)->where('receiver', Auth::user()->id)->latest()->first();
                    return $chats;
    }

    public static function timeAgo($date)
    {
        $datetime1 = Carbon::now();
        $datetime2 = date_create($date);
        $diff = date_diff($datetime1, $datetime2);
        $timemsg = '';
        if ($diff->y > 0) {
            $timemsg = $diff->y . ' year' . ($diff->y > 1 ? "'s" : '');
        } else if ($diff->m > 0) {
            $timemsg = $diff->m . ' month' . ($diff->m > 1 ? "'s" : '');
        } else if ($diff->d > 0) {
            $timemsg = $diff->d . ' day' . ($diff->d > 1 ? "'s" : '');
        } else if ($diff->h > 0) {
            $timemsg = $diff->h . ' hour' . ($diff->h > 1 ? "'s" : '');
        } else if ($diff->i > 0) {
            $timemsg = $diff->i . ' minute' . ($diff->i > 1 ? "'s" : '');
        } else if ($diff->s > 0) {
            $timemsg = $diff->s . ' second' . ($diff->s > 1 ? "'s" : '');
        }
        $timemsg = $timemsg . ' ago';
        return $timemsg;
    }

    public static function lastTime($date){

        $createdAt = Carbon::parse($date);

        // Determine the appropriate format
        if ($createdAt->isToday()) {
            $formattedTime = $createdAt->format('h:i A');  // e.g., 10:12 AM
        } elseif ($createdAt->isYesterday()) {
            $formattedTime = 'Yesterday ';  // e.g., Yesterday
        } else {
            $formattedTime = $createdAt->format('d/m/Y');  // e.g., 18/05/2024 
        }

        return $formattedTime;

    }
}
