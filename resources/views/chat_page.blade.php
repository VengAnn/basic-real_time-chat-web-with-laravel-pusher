@extends('layout.app')

@section('content')
<style>
    .outgoing {
        text-align: right;
        background-color: #d1f1d1;
        padding: 5px;
        margin: 5px 0;
        border-radius: 10px;
    }

    .incoming {
        text-align: left;
        background-color: #f1f1f1;
        padding: 5px;
        margin: 5px 0;
        border-radius: 10px;
    }
</style>

<div class="container mt-5">
    <div class="row">
        <!-- Search and User List Section -->
        <div class="col-md-4">
            <h5>Search Users</h5>
            <input type="text" class="form-control mb-3" placeholder="Search users..." id="searchUserInput">
            <!--all users here -->
            <ul class="list-group" id="userList">
                <li class="list-group-item" id="noUsersMsg">No users available</li>
            </ul>
        </div>

        <!-- Chat Section -->
        <div class="col-md-8">
            <div id="chatBox" class="border p-3">
                <h5 id="chatTitle">Select a user to chat</h5>
                <div id="chatMessages" class="mb-3" style="height: 300px; overflow-y: scroll; border: 1px solid #ccc;">
                </div>

                <!-- Input message and send -->
                <div id="messageInputArea">
                    <input type="file" id="fileInput" class="form-control mb-2">
                    <input type="text" id="messageInput" class="form-control" placeholder="Type a message...">
                    <button id="sendMessageBtn" class="btn btn-primary mt-2">Send</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!--here for pusher-->
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    const currentUserId = '{{ Auth::id() }}'; // The logged-in user's ID
    const currentUserName = "{{ Auth::user()->name }}"; // Current logged-in user's name
    var userNameClicked = '';
    var currentUserIdChatClicked = 0;

    var pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
        cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
    });

    // (all on this realTime) pusher know real time changes in database
     var channel = pusher.subscribe('chatApp');
     channel.bind('my-chat-message', function(data) {
       // Parse the message from JSON string to object
       var message = JSON.parse(data.message);

        if(message.data !== undefined ){
            if(message.data.seen === true){
                // update seen real time 
                message.data.data.forEach(d=>{
                   appendChatMessage(d, currentUserId, userNameClicked);
                });
            }
        }

        if(message.from_id !== undefined  && message.to_id === currentUserId){
                var chat = {
                    from_id: message.from_id,
                    to_id: message.to_id,
                    body: message.body,
                    attachment: message.attachment,
                    seen: message.seen,
                    created_at: message.created_at,
                    updated_at: message.updated_at,
                };

            appendChatMessage(chat,currentUserId,userNameClicked);
        }
    });


        const userList = $('#userList');
    
        // Set CSRF token globally for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function getAllUsers() {
            const searchTerm = $('#searchUserInput').val(); // Get the search term

            $.ajax({
                url: '/get-all-users',
                method: 'GET',
                data: { search: searchTerm }, // Send the search term
                success: function (res) {
                    const users = res.users;
                    userList.empty();  // Clear existing user list

                    // Check if there are users available
                    if (users.length > 0) {
                        users.forEach(user => {
                            if (user.id.toString() !== currentUserId.toString()) {
                                userList.append(`
                                    <li class="list-group-item user-item" 
                                        data-id="${user.id}" 
                                        data-name="${user.name}">
                                        ${user.name}
                                    </li>
                                `);
                            }
                        });

                        // Hide the "No users available" message if users exist
                        $('#noUsersMsg').hide();
                    } else {
                        userList.append('<li class="list-group-item" id="noUsersMsg">No users available</li>');
                    }
                },
                error: function (res) {
                    alert('Failed to retrieve users.', JSON.stringify(res));
                }
            });
        }

        $('#searchUserInput').on('input', function() {
            getAllUsers(); // Fetch users based on the input
        });

        function updateRealTimeSeenChatsUpdate() {
            $.ajax({
                url: '/update-real-time-seen',
                method: 'PUT',
                data:{
                    "userId": currentUserIdChatClicked 
                },
                success: function (res) {
                    console.log('update-real-time-seen successfully');
                },
                error: function (res) {
                    //console.log("error:", res);
                    alert('Failed to retrieve real-time seen chats:', res);
                }
            });
        }


        // Fetch all chats between two users and display them in the chat box
        function getAllChatsBetweenTwoUser(userId) {
            $.ajax({
                url: '/get-messages-between-two-user',
                method: 'GET',
                data: {
                    "userId": userId
                },
                success: function (res) {

                    res.forEach(chat => {
                        appendChatMessage(chat, currentUserId, userNameClicked);
                    });
                },
                error: function (res) {
                    //console.log("error:", res);
                    alert('Failed to retrieve chats:', res);
                }
            });
        } 

        // Display chat when a user is clicked
        function handleUserClick() {
            $('#userList').on('click', '.user-item', function () {
                currentUserIdChatClicked = $(this).data('id');
                userNameClicked = $(this).data('name');

                // Set chat title with selected user name
                $('#chatTitle').text(`Chat with ${userNameClicked}`);

                // Enable message input and send button
                $('#messageInputArea').show();

                // Clear the chat area when a new user is selected
               $('#chatMessages').empty();

                // Fetch and display chat messages between the logged-in user and the selected user
                getAllChatsBetweenTwoUser(currentUserIdChatClicked);

                // when click on chat to seen 
                updateRealTimeSeenChatsUpdate();

                // Handle message sending
                handleSendMessage(currentUserIdChatClicked);
            });
        }

        function appendChatMessage(chat, currentUserId, userName) {
            var messageClass = chat.from_id == currentUserId ? 'outgoing' : 'incoming';
            var sender = chat.from_id == currentUserId ? 'You' : userName;

            // Format the time sent (assuming created_at contains the timestamp)
            var timeSent = new Date(chat.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            // Show seen status for messages
            const seenStatus = chat.seen ? 'Seen' : 'Unseen';

                // Ensure the message belongs to the conversation between the two users
                if ((chat.from_id == currentUserId && chat.to_id == currentUserIdChatClicked) ||
                    (chat.from_id == currentUserIdChatClicked && chat.to_id == currentUserId)) {
                        // Append the message to the chat box with seen and time information
                        $('#chatMessages').append(`
                            <div class="${messageClass}">
                            <strong>${sender}:</strong> ${chat.body}
                            ${chat.attachment ? `<div><a href="${chat.attachment}" target="_blank">View Attachment</a></div>` : ''}
                            <div class="message-meta">
                                <small>${timeSent} - ${seenStatus}</small>
                            </div>
                        </div>
                    `);

                    // Scroll to the bottom of the chat box
                    $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);
                }
        }

        // Log when the user clicks on the input field (focus)
        $('#messageInput').on('focus', function() {
            updateRealTimeSeenChatsUpdate();
        });

        // Send message function
        function handleSendMessage(recipientId) {
            $('#sendMessageBtn').off('click').on('click', function () {
                const message = $('#messageInput').val();

                const fileInput = $('#fileInput')[0].files[0]; // Get selected file
                // Format local time to Y-m-d H:i:s
                const localTime = new Date().toISOString().slice(0, 19).replace('T', ' '); 

                if (!message) {
                    alert('Please enter a message or select a file to send.');
                    return;
                }

                const formData = new FormData();
                if (fileInput !== null) {
                    formData.append('file', fileInput);
                }
                formData.append('to_id', recipientId);
                formData.append('body', message);
                formData.append('created_at', localTime); 
                formData.append('updated_at', localTime); 

                $.ajax({
                    url: '/send-messages',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (res) {
                        var chat = {
                            from_id: res.from_id,
                            to_id: res.to_id,
                            body: res.body,
                            attachment: res.attachment,
                            seen: res.seen,
                            created_at: res.created_at,
                            updated_at: res.updated_at,
                        };

                        appendChatMessage(chat,currentUserId,userNameClicked);
                      
                        // Clear input fields
                        $('#messageInput').val('');
                        $('#fileInput').val('');
                    },
                    error: function (res) {
                        // console.log('error', res);
                        alert('Failed to send message:', res.responseJSON);
                    }
                });
            });
        }

        // Initialize the chat page
        function initChatPage() {
            $('#messageInputArea').hide();  // Hide message input area until a user is selected
            getAllUsers();  // Fetch and display users
            handleUserClick();  // Set up user click event
        }


      
        $(document).ready(function () {
            // Call init function
            initChatPage();
        });

</script>
@endsection