<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <link href="{{URL::to('/css/chat.css')}}" rel="stylesheet" />

</head>


<body class="bg-black">

    <div class="flex h-screen">

        <!-- Chat list -->
        <div class="w-1/4 bg-black border-r border-gray-600 overflow-y-auto custom_scroll">
            <!-- List of chats -->
            <div class="p-4">
                <h2 class="text-lg font-bold mb-4 text-gray-400">Chat List</h2>
                <!-- Dynamic chat list items -->
                @if (!$chatlistUserData->isEmpty())
                    @foreach ($chatlistUserData as $chatlist)
                        @php $url = 'chat/' . $chatlist->id;
                        $last_message = App\Models\Message::latestMessage($chatlist->id );
                        $message = $last_message->message;
                        $time = App\Models\Message::lastTime($last_message->created_at);
                        @endphp
                        <div class="mb-2">
                            <div class="flex items-center cursor-pointer p-2 rounded hover:bg-gray-900 chat-lists">
                                <div class="w-12 h-12 bg-blue-500 rounded-full mr-3 object-fit">
                                    <img src="{{$chatlist->avatar}}" alt="{{$chatlist->name}}">
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-white">
                                        <a href="{{ URL::to($url) }}" class="text-white">{{ $chatlist->name }}</a>
                                    </p>
                                    <p class="text-white">{{$message}}</p>
                                </div >
                                <div class="chat_item">
                                <p class="text-gray-400">{{$time}}</p>
                                @if(isset($unreadMessagesCount[$chatlist->id]) && $unreadMessagesCount[$chatlist->id] > 0)
                                        <p class="text-white rounded-full w-6 h-6 bg-blue-400 notify text-center unread-count" data-chat="{{ $chatlist->id }}">{{ $unreadMessagesCount[$chatlist->id] }}</p>
                                @endif
                                
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <h3 class="text-xs font-semibold mb-4 text-gray-400">You Can Chat with Our Users</h3>
                    @foreach ($getUsers as $user)
                        @php $url = 'chat/' . $user->id @endphp
                        <div class="mb-2">
                            <div class="flex items-center cursor-pointer p-2 rounded hover:bg-gray-900">
                                <div class="w-12 h-12 bg-blue-500 rounded-full mr-3">
                                <img src="{{$user->avatar}}" alt="{{$user->name}}">
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-white">
                                        <a href="{{ URL::to($url) }}" class="text-white">{{ $user->name }}</a>
                                    </p>
                                </div >
                                
                               
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Chat area -->
        <div class="w-1/2 bg-gray-800 flex flex-col ">
            <!-- Chat header -->
            <div class="border-b border-gray-600 p-4 bg-gray-900">
                <h2 class="text-lg font-bold text-white">Chat with {{ $recipient->name ?? 'John Doe' }}</h2>
                <p class="text-sm text-gray-400">Last Seen: 5m ago</p>
            </div>
            <!-- Chat messages -->
            <div class="p-4 flex-1 overflow-y-auto custom_scroll" id="chat_area">
                <!-- Example message -->
                    @if((!empty($allMessages)) && isset($allMessages))
                        @foreach($allMessages as $message)
                            @if($message->sender == auth()->user()->id)
                                <div class="flex items-start mb-4 justify-end">
                                    <div class="inline-block bg-blue-500 text-white p-2 rounded-lg mb-2 relative send_msg">
                                        <p class="whitespace-nowrap">{{ $message->message }}</p>
                                        <div class="tick-mark">
                                            @if($message->read_status == '0')
                                                <i class="fas fa-check"></i> <!-- Single tick mark indicating delivered -->
                                            @elseif ($message->read_status == '1')
                                                <i class="fas fa-check-double seen"></i> <!-- Double tick mark indicating seen -->
                                            @endif
                                        </div>
                                        <div class="absolute right-0 top-1/2 transform translate-x-1/2 -translate-y-1/2 rotate-45 w-2 h-2 bg-blue-500"></div>
                                    </div>
                                    <div class="w-10 h-10 rounded-full ml-3">
                                        <img src="{{auth()->user()->avatar}}" alt="{{auth()->user()->name}}">
                                    </div>
                                </div>
                            @else
                                @if((!empty($recipient)) && isset($recipient))
                                    <div class="flex items-start mb-4">
                                        <div class="w-10 h-10 bg-black rounded-full mr-3">
                                            <img src="{{$recipient->avatar}}" alt="{{$recipient->name}}">
                                        </div>
                                        <div class="inline-block bg-black text-white p-2 rounded-lg mb-2 relative receiver_msg">
                                            <p class="whitespace-nowrap">{{ $message->message }}</p>
                                            <div class="absolute left-0 top-1/2 transform -translate-x-1/2 -translate-y-1/2 rotate-45 w-2 h-2 bg-black"></div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    @endif
            </div>
            <!-- Message input -->
           
            <div class="border-t border-gray-600 p-4 w-full target_send">
                <div class="flex items-center border border-gray-600 rounded p-2">
                    <input type="text" placeholder="Type your message here..." class="text_message flex-grow p-2" id="message-input">
                    @if((!empty($recipient)) && isset($recipient))
                        @php $disable = ""; @endphp
                        <input type="hidden" id="recipientId" value="{{ $recipient->id }}">
                    @else
                        @php $disable = "disabled"; @endphp
                    @endif
                    <button class="btn btn-success ml-2" id="send-message" {{ $disable }}><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                </div>
            </div>

        </div>

        <!-- User details -->
        <div class="w-1/4 bg-black border-l border-gray-600 overflow-y-auto">
            <!-- User details -->
            <div class="p-4">
                <h2 class="text-lg font-bold mb-4 text-gray-400">User Details</h2>
                <!-- User details content -->
                <div>
                    <p class="font-bold text-white">{{ $recipient->name ?? 'John Doe' }}</p>
                    <p class="text-gray-400">Email: {{ $recipient->email ?? 'johndoe@example.com' }}</p>
                    <!-- Add more user details here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    Pusher.logToConsole = true;

    var pusher = new Pusher('8299b0b5e669284d1e9b', {
        cluster: 'mt1',
        encrypted: true,
    });

    var recipientChannel = 'private-chat.{{ auth()->user()->id }}';
    var channel = pusher.subscribe(recipientChannel);

    channel.bind('private-message', function(data) {
        let recipientUserId = data.recipientUserId;
        let senderId = data.senderId;
        let recipientId = '{{ $recipient->id ?? "" }}';

        if (senderId == recipientId) {
            let receiverMessage = `
            <div class="flex items-start mb-4">
                <div class="w-10 h-10 bg-black rounded-full mr-3">
                <img src="{{ $recipient->avatar ?? "" }}" alt="{{ $recipient->name ?? "" }}">
                </div>
                <div class="inline-block bg-black text-white p-2 rounded-lg mb-2 relative recevier_msg">
                    <p class="whitespace-nowrap">${data.message}</p>

                    
                <div class="absolute left-0 top-1/2 transform -translate-x-1/2 -translate-y-1/2 rotate-45 w-2 h-2 bg-black"></div>
                                    
            </div>`;
            $("#chat_area").append(receiverMessage);
        }

      

        var chatId = senderId;
        var unreadCount = $('.unread-count[data-chat="' + chatId + '"]');
        if (unreadCount.length) {
            unreadCount.text(parseInt(unreadCount.text()) + 1);
        } else {
            var chatList = $('.chat_item');
            var unreadSpan = '<p class="text-white rounded-full w-6 h-6 bg-blue-400 notify text-center unread-count" data-chat="' + chatId + '">1</p>';
            chatList.find('a[href$="' + chatId + '"]').append(unreadSpan);
        }
    });

    function updateTickmark(status) {
        if (status == "sent") {
            return '<i class="fas fa-check"></i>';
        } else if (status == "delivered") {
            return '<i class="fas fa-check-double"></i>';
        } else {
            return '<i class="fas fa-check-double seen" style="background-color: lightblue;"></i>';
        }
    }


    function changeread(status) {
        if (status == "delivered") {
            var checkIcon = document.querySelector('.sentMessage .fas.fa-check');
            if (checkIcon) {
                checkIcon.classList.remove('fa-check');
                checkIcon.classList.add('fa-check-double');
            } else {
                console.log("Element not found");
            }
        }else{
            var checkIcon = document.querySelector('.sentMessage .fas.fa-check');
            if (checkIcon) {
                checkIcon.classList.remove('fa-check');
                checkIcon.classList.add('fa-check-double seen');
            } else {
                console.log("Element not found");
            }
        }
    }

    const channelName = "private-chat";
    clientSendChannel = pusher.subscribe(`${channelName}.{{ $recipient->id ?? "" }}`);
    clientListenChannel = pusher.subscribe(`${channelName}.{{ auth()->user()->id }}`);

    $('#send-message').click(function() {
        var message = $('#message-input').val();
        $.ajax({
            method: 'POST',
            url: '{{ route("send.message", !empty($recipient) ? $recipient->id : "") }}',
            data: {
                '_token': '{{ csrf_token() }}',
                'message': message
            },
            success: function(result) {
                var chatId = '{{ $recipient->id ?? "" }}';
                if (chatId) {
                    let send = 
                    `<div class="flex items-start mb-4 justify-end">
                    <div class="sentMessage inline-block bg-blue-500 text-white p-2 rounded-lg mb-2 relative send_msg">
                        <p class="whitespace-nowrap">${message}</p>
                        <div class="tick-mark">
                        ${updateTickmark("sent")}
                        </div>
                        <div class="absolute right-0 top-1/2 transform translate-x-1/2 -translate-y-1/2 rotate-45 w-2 h-2 bg-blue-500"></div>
                    </div>
                    <div class="w-10 h-10 rounded-full ml-3">
                    <img src="{{auth()->user()->avatar ?? ""}}" alt="{{auth()->user()->name ?? ""}}">
                    </div>
                </div>`;

                        

                        
                    $("#chat_area").append(send);
                    if (result.active_status == 1) {
                        // $('.fa-check').removeClass('fa-check').addClass('fa-check-double');
                        changeread("delivered");
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
        $('#message-input').val('');
    });

    const presenceChannel = pusher.subscribe('presence-online-users');

    function isMemberOnline(memberId) {
        const member = presenceChannel.members.get(memberId);
        return member && member.user_info && member.user_info.online;
    }

    presenceChannel.bind('pusher:subscription_succeeded', (members) => {
        members.each((member) => {
            setActiveStatus(1, member.id);
            console.log(`${member.id} is online.`);
        });
    });

    presenceChannel.bind('pusher:member_removed', (member) => {
        console.log(`${member.id} is offline.`);
        setActiveStatus(0, member.id);
    });

    clientListenChannel.bind("message-seen", function(data) {
        if (data.receiverId == '{{ $recipient->id ?? "" }}' && data.senderId == '{{ auth()->user()->id }}') {
            if (data.status == 1) {
                $(".sentMessage").find(".fa-check-double").removeClass("fa-check-double").addClass("fa-check-double seen");
                $(".sentMessage").find(".fa-check").removeClass("fa-check").addClass("fa-check-double seen");
            }
        }
    });

   
   

    function setActiveStatus(status, id) {
        $.ajax({
            method: 'POST',
            url: '{{ route("activeStatus.set") }}',
            data: {
                '_token': '{{ csrf_token() }}',
                'status': status,
                'id': id
            },
            success: function(result) {
                console.log("StatusUpdated", result);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getUserStatus() {
        var chatId = '{{ $recipient->id ?? "" }}';
        if (chatId) {
            $.ajax({
                method: 'GET',
                url: '{{ route("get.user.status", !empty($recipient) ? $recipient->id : "") }}',
                success: function(response) {
                    if (response.status === 1) {
                        changeread("delivered");
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        getUserStatus();
    });

    $(document).ready(function() {
        markMessagesAsRead();

        function markMessagesAsRead(chatId) {
            var chatId = '{{ $recipient->id ?? "" }}';
            if (chatId) {
                $.ajax({
                    method: 'POST',
                    url: '{{ route("mark.messages.read") }}',
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'chat_id': chatId
                    },
                    success: function(result) {
                        console.log('Messages marked as read');
                        changeread('read');
                        $('.unread-count[data-chat="' + chatId + '"]').remove();
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }
        }

        $(document).on('click', '#chat_area', function() {
            var chatId = '{{ $recipient->id ?? "" }}';
            markMessagesAsRead(chatId);
        });
    });
</script>

</body>

</html>

