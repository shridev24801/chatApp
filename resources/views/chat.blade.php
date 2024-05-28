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
                            <img src="{{ asset('storage/' . $chatlist->avatar) }}" alt="{{$chatlist->name}}">
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-white">
                                <a href="{{ URL::to($url) }}" class="text-white">{{ $chatlist->name }}</a>
                            </p>
                            <p class="text-white latest_msg">{{$message}}</p>
                        </div>
                        <div class="chat_item" data-chat="{{$chatlist->id}}">
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
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{$user->name}}">
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-white">
                                <a href="{{ URL::to($url) }}" class="text-white">{{ $user->name }}</a>
                            </p>
                        </div>


                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>

        <!-- Chat area -->
        <div class="w-1/2 bg-gray-800 flex flex-col chatting">
            <!-- Chat header -->
            <div class="border-b border-gray-600 p-4 bg-gray-900 chat_header" data-receiver="{{$recipient->id ?? '' }}">
                <h2 class="text-lg font-bold text-white">Chat with {{ $recipient->name ?? '' }}</h2>
                <p class="text-sm text-gray-400 last_seen">Last Seen: 5m ago</p>
            </div>
            <!-- Chat messages -->
            <div class="p-4 flex-1 overflow-y-auto custom_scroll" id="chat_area">
                <!-- Example message -->
                @if((!empty($allMessages)) && isset($allMessages))
                @php
                $currentDate = \Carbon\Carbon::now()->format('Y-m-d');
                $yesterdayDate = \Carbon\Carbon::yesterday()->format('Y-m-d');
                $lastDate = null;
                @endphp
                @foreach($allMessages as $message)
                @php
                $messageDate = \Carbon\Carbon::parse($message->created_at)->format('Y-m-d');
                $displayDate = '';

                if ($messageDate == $currentDate) {
                $displayDate = 'Today';
                } elseif ($messageDate == $yesterdayDate) {
                $displayDate = 'Yesterday';
                } else {
                $displayDate = \Carbon\Carbon::parse($message->created_at)->format('F j, Y');
                }
                @endphp

                @if ($lastDate != $messageDate)
                <div class="text-center text-gray-500 mb-4">{{ $displayDate }}</div>
                @php $lastDate = $messageDate; @endphp
                @endif
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
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{auth()->user()->name}}">
                    </div>
                </div>
                @else
                @if((!empty($recipient)) && isset($recipient))
                <div class="flex items-start mb-4">
                    <div class="w-10 h-10 bg-black rounded-full mr-3">
                        <img src="{{ asset('storage/' . $recipient->avatar) }}" alt="{{$recipient->name}}">
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
                <form id="fupForm" enctype="multipart/form-data">
                    <input type="text" placeholder="Type your message here..." class="text_message flex-grow p-2" name="message" id="message-input">
                    @if((!empty($recipient)) && isset($recipient))
                    @php $disable = ""; @endphp
                    <input type="hidden" id="recipientId" value="{{ $recipient->id }}">
                    @else
                    @php $disable = "disabled"; @endphp
                    @endif
                    <input type="file" class="form-control attachment" id="file" name="attactment"  />
                <button type="submit" name="submit" class="btn btn-success ml-2" id="send-message" {{ $disable }}><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
            </form>
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
                    <p class="font-bold text-white">{{ $recipient->name ?? '' }}</p>
                    <p class="text-gray-400">Email: {{ $recipient->email ?? '' }}</p>
                    <!-- Add more user details here -->
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" id="logout_trig" class="" onclick="event.preventDefault(); this.closest('form').submit();"><i class="fa fa-sign-out mr-1"></i> Logout</a>
                </form>
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

        if (Notification.permission !== "granted") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    console.log("Notification permission granted.");
                }
            });
        }

        function showNotification(data) {
            if (Notification.permission === "granted") {
                new Notification('New message from ', {
                    body: data
                });
            }
        }

        channel.bind('private-message', function(data) {
            let recipientUserId = data.recipientUserId;
            let senderId = data.senderId;
            let recipientId = '{{ $recipient->id ?? "" }}';
            
            let attachment  = data.attachment;
            if (senderId == recipientId) {
        if (data.message && data.message.length > 0) {
            showNotification(data.message);
            var url = '{{ URL::to("/storage/") }}';
            let receiverMessage = `
                <div class="flex items-start mb-4">
                    <div class="w-10 h-10 bg-black rounded-full mr-3">
                        <img src="{{ asset('storage/'.$recipient->avatar ?? '') }}" alt="{{ $recipient->name ?? '' }}">
                    </div>
                    <div class="inline-block bg-black text-white p-2 rounded-lg mb-2 relative recevier_msg">
                        <p class="whitespace-nowrap">${data.message}</p>
                        <div class="absolute left-0 top-1/2 transform -translate-x-1/2 -translate-y-1/2 rotate-45 w-2 h-2 bg-black"></div>
                    </div>
                </div>`;
            $("#chat_area").append(receiverMessage);
        }

        if (attachment) {
            var url = '{{ URL::to("/storage") }}';
            var attachmentPath = attachment;
            var directory = attachmentPath.split('/')[0];
            var receiverMessage;

           
            if (directory === 'chatimage') {
                showNotification("Sent you a Image");
                receiverMessage = `
                    <div class="flex items-start mb-4">
                        <div class="w-10 h-10 bg-black rounded-full mr-3">
                        <img src="{{ asset('storage/'.$recipient->avatar ?? '') }}" alt="{{ $recipient->name ?? '' }}">
                        </div>
                        <div class="inline-block bg-black text-white p-2 rounded-lg mb-2 relative recevier_msg">
                            <img src="${url}/${attachmentPath}" alt="chatImage" width="100px" height="100px">
                            <div class="absolute left-0 top-1/2 transform -translate-x-1/2 -translate-y-1/2 rotate-45 w-2 h-2 bg-black"></div>
                        </div>
                    </div>`;
            } else if (directory === 'chatvideo') {
                showNotification("Sent you a Vedio");
                receiverMessage = `
                    <div class="flex items-start mb-4">
                        <div class="w-10 h-10 bg-black rounded-full mr-3">
                        <img src="{{ asset('storage/'.$recipient->avatar ?? '') }}" alt="{{ $recipient->name ?? '' }}">
                        </div>
                        <div class="inline-block bg-black text-white p-2 rounded-lg mb-2 relative recevier_msg">
                            <video width="320" height="240" controls>
                                <source src="${url}/${attachmentPath}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div class="absolute left-0 top-1/2 transform -translate-x-1/2 -translate-y-1/2 rotate-45 w-2 h-2 bg-black"></div>
                        </div>
                    </div>`;
            } else if (directory === 'chatfiles') {
                showNotification("Shared you a file");
                var fileName = attachmentPath.split('/').pop();
                receiverMessage = `
                    <div class="flex items-start mb-4">
                        <div class="w-10 h-10 bg-black rounded-full mr-3">
                        <img src="{{ asset('storage/'.$recipient->avatar ?? '') }}" alt="{{ $recipient->name ?? '' }}">
                        </div>
                        <div class="inline-block bg-black text-white p-2 rounded-lg mb-2 relative recevier_msg">
                            <a href="${url}/${attachmentPath}" download="${fileName}" class="text-white underline">${fileName}</a>
                            <div class="absolute left-0 top-1/2 transform -translate-x-1/2 -translate-y-1/2 rotate-45 w-2 h-2 bg-black"></div>
                        </div>
                    </div>`;
            }

            $("#chat_area").append(receiverMessage);
        }
    }



            var chatId = senderId;
            var unreadCount = $('.unread-count[data-chat="' + chatId + '"]');
            let message = data.message;
            console.log(unreadCount.length);
            if (unreadCount.length) {
                unreadCount.text(parseInt(unreadCount.text()) + 1);
            } else {
                // var chatList = $('.chat_item');
                // var unreadSpan = '<p class="text-white rounded-full w-6 h-6 bg-blue-400 notify text-center unread-count" data-chat="' + chatId + '">1</p>';
                // chatList.find('a[href$="' + chatId + '"]').append(unreadSpan);

                var chatItem = $('.chat_item[data-chat="' + chatId + '"]');
                var unreadSpan = '<p class="text-white rounded-full w-6 h-6 bg-blue-400 notify text-center unread-count" data-chat="' + chatId + '">1</p>';

                // Append the new unread count element to the chat item
                chatItem.append(unreadSpan);
            }
            var latestMsg = $('.chat_item[data-chat="' + chatId + '"]').siblings('.flex-1').find('.latest_msg');

            latestMsg.text(message);
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
            } else {
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

        // $('#send-message').click(function() {
        //     var message = $('#message-input').val();
        //     $.ajax({
        //         method: 'POST',
        //         url: '{{ route("send.message", !empty($recipient) ? $recipient->id : "") }}',
        //         data: {
        //             '_token': '{{ csrf_token() }}',
        //             'message': message
        //         },
        //         success: function(result) {
        //             var chatId = '{{ $recipient->id ?? "" }}';
        //             if (chatId) {
        //                 let send =
        //                     `<div class="flex items-start mb-4 justify-end">
        //             <div class="sentMessage inline-block bg-blue-500 text-white p-2 rounded-lg mb-2 relative send_msg">
        //                 <p class="whitespace-nowrap">${message}</p>
        //                 <div class="tick-mark">
        //                 ${updateTickmark("sent")}
        //                 </div>
        //                 <div class="absolute right-0 top-1/2 transform translate-x-1/2 -translate-y-1/2 rotate-45 w-2 h-2 bg-blue-500"></div>
        //             </div>
        //             <div class="w-10 h-10 rounded-full ml-3">
        //             <img src="{{ asset('storage/' . auth()->user()->avatar ?? "") }}" alt="{{auth()->user()->name ?? ""}}">
        //             </div>
        //         </div>`;




        //                 $("#chat_area").append(send);
        //                 if (result.active_status == 1) {
        //                     // $('.fa-check').removeClass('fa-check').addClass('fa-check-double');
        //                     changeread("delivered");
        //                 }
        //             }
        //         },
        //         error: function(xhr, status, error) {
        //             console.error(error);
        //         }
        //     });
        //     $('#message-input').val('');
        // });

        const presenceChannel = pusher.subscribe('presence-online-users');

        function isMemberOnline(memberId) {
            const member = presenceChannel.members.get(memberId);
            return member && member.user_info && member.user_info.online;
        }

        presenceChannel.bind('pusher:subscription_succeeded', (members) => {
            members.each((member) => {
                setActiveStatus(1, member.id);
                console.log(`${member.id} is online.`);
                var chatArea = $('.chat_header[data-receiver="' + member.id + '"]');
                if (chatArea) {

                    chatArea.find('.last_seen').text('Active');
                }
                updateLastSeenText(member.id, 'Active');

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

        function updateLastSeenText(userId, status) {
            var chatHeader = $('.chat_header[data-receiver="' + userId + '"]');
            if (chatHeader.length) {
                chatHeader.find('.last_seen').text(status);
            }
        }

        function fetchAndUpdateLastSeen(userId) {
            $.ajax({
                method: 'GET',
                url: '/get-last-seen/' + userId,
                success: function(response) {
                    let lastSeen = response.last_seen;
                    updateLastSeenText(userId, lastSeen);
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching last seen:", error);
                }
            });
        }




        setInterval(function() {
            $.ajax({
                method: 'POST',
                url: '{{ route("update.last.seen") }}',
                data: {
                    '_token': '{{ csrf_token() }}'
                },
                success: function(result) {
                    console.log("Last seen updated");
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }, 2000); // Update every minute


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
            fetchAndUpdateLastSeen('{{ $recipient->id ?? "" }}');

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

          // Submit form data via Ajax
    $("#fupForm").on('submit', function(e){
        e.preventDefault();
        let hasFile = !!$(".attachment").val();

        console.log(hasFile);
        var message = $('#message-input').val();
        if(message.length > 0 || hasFile){
            $.ajax({
                type: 'POST',
                url: '{{ route("send.message", !empty($recipient) ? $recipient->id : "") }}',
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData:false,
                success: function(result) {
            var chatId = '{{ $recipient->id ?? "" }}';
            if (chatId) {
                if(message.length > 0){
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
                        <img src="{{ asset('storage/' . auth()->user()->avatar ?? "") }}" alt="{{auth()->user()->name ?? ""}}">
                        </div>
                    </div>`;
            $("#chat_area").append(send);
                }
                if (result.attachment.length > 0) {
                    var url = '{{ URL::to("/storage") }}';
                    var attachmentPath = result.attachment;
                    var directory = attachmentPath.split('/')[0];
                    var sendAttachment;
                    if (directory === 'chatimage') {
                        sendAttachment = `
                            <div class="flex items-start mb-4 justify-end">
                                <div class="sentMessage inline-block bg-blue-500 text-white p-2 rounded-lg mb-2 relative send_msg">
                                    <img src="${url}/${attachmentPath}" alt="chatImage" width="100px" height="100px">
                                    <div class="tick-mark">
                                        ${updateTickmark("sent")}
                                    </div>
                                    <div class="absolute right-0 top-1/2 transform translate-x-1/2 -translate-y-1/2 rotate-45 w-2 h-2 bg-blue-500"></div>
                                </div>
                                <div class="w-10 h-10 rounded-full ml-3">
                                    <img src="{{ asset('storage/' . auth()->user()->avatar ?? "") }}" alt="{{ auth()->user()->name ?? "" }}">
                                </div>
                            </div>`;
                    } else if (directory === 'chatvideo') {
                        sendAttachment = `
                            <div class="flex items-start mb-4 justify-end">
                                <div class="sentMessage inline-block bg-blue-500 text-white p-2 rounded-lg mb-2 relative send_msg">
                                    <video width="320" height="240" controls>
                                        <source src="${url}/${attachmentPath}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                    <div class="tick-mark">
                                        ${updateTickmark("sent")}
                                    </div>
                                    <div class="absolute right-0 top-1/2 transform translate-x-1/2 -translate-y-1/2 rotate-45 w-2 h-2 bg-blue-500"></div>
                                </div>
                                <div class="w-10 h-10 rounded-full ml-3">
                                    <img src="{{ asset('storage/' . auth()->user()->avatar ?? "") }}" alt="{{ auth()->user()->name ?? "" }}">
                                </div>
                            </div>`;
                    } else if (directory === 'chatfiles') {
                        var fileName = attachmentPath.split('/').pop();
                        sendAttachment = `
                            <div class="flex items-start mb-4 justify-end">
                                <div class="sentMessage inline-block bg-blue-500 text-white p-2 rounded-lg mb-2 relative send_msg">
                                    <a href="${url}/${attachmentPath}" download="${fileName}" class="text-white underline">${fileName}</a>
                                    <div class="tick-mark">
                                        ${updateTickmark("sent")}
                                    </div>
                                    <div class="absolute right-0 top-1/2 transform translate-x-1/2 -translate-y-1/2 rotate-45 w-2 h-2 bg-blue-500"></div>
                                </div>
                                <div class="w-10 h-10 rounded-full ml-3">
                                    <img src="{{ asset('storage/' . auth()->user()->avatar ?? "") }}" alt="{{ auth()->user()->name ?? "" }}">
                                </div>
                            </div>`;
                    }

                    $("#chat_area").append(sendAttachment);
                    }

              
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
        }
        $('.attachment').val('');
        $('#message-input').val('');
    });

    </script>

</body>

</html>