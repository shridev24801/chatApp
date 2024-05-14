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

<body class="bg-gray-200 flex justify-center items-center h-screen">
    @if (Route::has('login'))
    <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
        @auth
        <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Dashboard</a>
        <x-dropdown-link :href="route('profile.edit')">
            {{ __('Profile') }}
        </x-dropdown-link>
        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                {{ __('Log Out') }}
            </x-dropdown-link>
        </form>
        @else
        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log in</a>

        @if (Route::has('register'))
        <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
        @endif
        @endauth
    </div>
    @endif
    <div class="bg-white rounded-lg shadow-md p-4 w-96">
        <h1 class="text-xl font-semibold mb-4">Chat Lists</h1>
        <div id="chat-lists" class="mb-4">

            @if (!$chatlistUserData->isEmpty())
            @foreach ($chatlistUserData as $chatlist)
            <?php $url = 'chat/' . $chatlist->id ?>
            <p><a href="{{ URL::to($url) }}">{{ $chatlist->name }}</a>
                @if(isset($unreadMessagesCount[$chatlist->id]) && $unreadMessagesCount[$chatlist->id] > 0)

                <span class="text-xs text-red-500 unread-count" data-chat="{{ $chatlist->id }}">{{ $unreadMessagesCount[$chatlist->id] }}</span>
                @endif
            </p>
            @endforeach
            @else
            <h3 class="text-xs font-semibold mb-4">You Can Chat with Our Users</h3>
            @foreach ($getUsers as $user)
            <?php $url = 'chat/' . $user->id ?>
            <p><a href="{{ URL::to($url) }}">{{ $user->name }}</a></p>
            @endforeach
            @endif

        </div>

    </div>
    <div class="container d-flex justify-content-center">
        <div><span>Logged by {{auth()->user()->name}}</span></div>
        <div class="card mt-5">
            <div class="d-flex flex-row justify-content-between p-3 adiv text-white">
                <i class="fas fa-chevron-left"></i>
                <span class="pb-3">Live chat <?php echo (!empty($recipient) ? "with " . $recipient->name : "") ?></span>
                <i class="fas fa-times"></i>
            </div>
            <div id="chat_area">
                @if((!empty($allMessages)) && isset($allMessages))
                @foreach($allMessages as $message)
                @if($message->sender == auth()->user()->id )
                <div class="d-flex flex-row p-3">

                    <div class="bg-white mr-2 p-3"><span class="text-muted sendChat">{{$message->message}}
                        </span> @if($message->read_status == '0')
                        <i class="fas fa-check"></i> <!-- Double tick mark indicating delivered -->
                        @elseif ($message->read_status == '1')
                        <i class="fas fa-check-double seen"></i> <!-- Double tick mark indicating seen -->
                        @endif
                    </div>
                    <img src="https://img.icons8.com/color/48/000000/circled-user-male-skin-type-7.png" width="30" height="30">


                </div>
                @else
                <div class="d-flex flex-row p-3">

                    <img src="https://img.icons8.com/color/48/000000/circled-user-female-skin-type-7.png" width="30" height="30">

                    <div class="chat ml-2 p-3">{{$message->message}}

                    </div>
                </div>
                @endif
                @endforeach
                @endif

            </div>

            <div class="form-group px-3">
                <input type="text" class="form-control" id="message-input" placeholder="Type your message" />
            </div>
            @if((!empty($recipient)) && isset($recipient))
            <?php $disable = ""; ?>
            <input type="hidden" id="recipientId" value="{{$recipient->id}}">
            @else

            <?php $disable = "disabled"; ?>
            @endif

            <button class="btn btn-success my-2" id="send-message" {{$disable}}>Send</button>
        </div>
    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script type="text/javascript" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>


    <script>
        Pusher.logToConsole = true;

        var pusher = new Pusher('8299b0b5e669284d1e9b', {
            cluster: 'mt1',
            encrypted: true,
        });

        var recipientChannel = 'private-chat.{{ auth()->user()->id }}';
       
        const channelName = "private-chat";
        clientSendChannel = pusher.subscribe(`${channelName}.{{ $recipient->id ?? "" }}`);
        clientListenChannel = pusher.subscribe(`${channelName}.{{ auth()->user()->id }}`);
        // console.log(clientSendChannel);
        // Subscribe to presence channel
        const presenceChannel = pusher.subscribe('presence-online-users');
        // console.log(presenceChannel);
        // Bind to presence events
        presenceChannel.bind('pusher:subscription_succeeded', (members) => {
        members.each((member) => {
            console.log(`${member.id} is online.`);
           
        });
        });

      

        presenceChannel.bind('pusher:member_added', (member) => {

        console.log(`${member.id} is online.`);
        });

        presenceChannel.bind('pusher:member_removed', (member) => {
        console.log(`${member.id} is offline.`);
        });

        clientListenChannel.bind("message-seen", function(data) {
            if (data.receiverId == '{{ $recipient->id ?? "" }}' && data.senderId == '{{ auth()->user()->id }}') {

                if (data.status == 1) {
                    console.log(data.status);
                    $(".sentMessage").find(".fa-check-double").removeClass("fa-check-double").addClass("fa-check-double seen");
                }
            }


        });
        var channel = pusher.subscribe(recipientChannel);


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
                // Step 1: Select the <i> element with the class 'fas fa-check' inside the '.sentMessage' div
                var checkIcon = document.querySelector('.sentMessage .fas.fa-check');

                // Step 2: Check if the element exists
                if (checkIcon) {
                    // Step 3: Remove the existing classes
                    checkIcon.classList.remove('fa-check');

                    // Step 4: Add the new class
                    checkIcon.classList.add('fa-check-double');
                } else {
                    console.log("Element not found");
                }
            }

        }
        channel.bind('private-message', function(data) {
            let recipientUserId = data.recipientUserId; // Recipient user ID received from Pusher event
            let senderId = data.senderId; // Recipient user ID received from Pusher event
            let recipientId = '{{ $recipient->id ?? "" }}'; // Authenticated user's ID

            let status = data.status === 0 ? "delivered" : "read";
            // Check if the recipient user ID matches the authenticated user's ID
            if (senderId == recipientId) {

                let tickMark = updateTickmark(status);

                let tickIcon = '<i class="fas fa-check-double"></i>';
                let tickStyle = status === "read" ? 'style="background-color: lightblue;"' : '';



                let receiverMessage = `
                    <div class="d-flex flex-row p-3">
                    <img src="https://img.icons8.com/color/48/000000/circled-user-female-skin-type-7.png" width="30" height="30">
                        <div class="chat ml-2 p-3">${data.message}</div>
                    </div>`;
                $("#chat_area").append(receiverMessage);


            }

            var chatId = senderId;
            var unreadCount = $('.unread-count[data-chat="' + chatId + '"]');
            if (unreadCount.length) {
                unreadCount.text(parseInt(unreadCount.text()) + 1);
            } else {
                var chatList = $('#chat-lists');
                var unreadSpan = '<span class="text-xs text-red-500 unread-count" data-chat="' + chatId + '">1</span>';
                chatList.find('a[href$="' + chatId + '"]').append(unreadSpan);
            }
        });

        $('#send-message').click(function() {
            var message = $('#message-input').val();
            $.ajax({
                method: 'POST',
                url: '{{ route("send.message", !empty($recipient)?$recipient->id:"") }}',
                data: {
                    '_token': '{{ csrf_token() }}',
                    'message': message
                },
                success: function(result) {
                    var chatId = '{{ $recipient->id ?? "" }}';
                    if (chatId) {
                        let send = `
                            <div class="d-flex flex-row p-3">
                                <div class="bg-white mr-2 p-3 sentMessage"><span class="text-muted sendChat">${message}</span>${updateTickmark("sent")}</div>
                                <img src="https://img.icons8.com/color/48/000000/circled-user-male-skin-type-7.png" width="30" height="30">
                            </div>`;
                        $("#chat_area").append(send);
                        changeread("delivered");
                        
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
            $('#message-input').val('');
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
                            $('.unread-count[data-chat="' + chatId + '"]').remove();
                            // changeread("read")


                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }
            }

            $(document).on('click', '#chat_area', function() {
                var chatId = '{{ $recipient->id ?? "" }}';
                // $(".sentMessage .fa-check-double").css("background-color", "blue");
                markMessagesAsRead(chatId);
            });
        });
    </script>


</body>

</html>