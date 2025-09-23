<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Spotify Chat Interface</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex flex-col">
    <div class="container mx-auto flex flex-1 overflow-hidden">
        <!-- Sidebar with chat users -->
        <div class="w-1/3 bg-white border-r border-gray-200 flex flex-col">
            <div class="p-4 border-b border-gray-200 font-semibold">
                Chats
            </div>
            <div class="flex-1 overflow-y-auto" id="chat-users">
                <div class="p-4 text-center text-gray-500">Loading chats...</div>
            </div>
        </div>

        <div class="w-2/3 flex flex-col">
            <div class="p-4 border-b border-gray-200 font-semibold flex items-center justify-between" id="chat-header">
                <span>Select a chat</span>
                <button id="rename-user-btn" class="text-blue-500 hover:text-blue-700 text-sm hidden">
                    Rename
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 bg-gray-50" id="messages-container">
                <div class="flex items-center justify-center h-full text-gray-500" id="no-chat-selected">
                    Select a chat to start messaging
                </div>
                <div id="messages-list" class="space-y-4 hidden"></div>
            </div>
            <div class="p-4 border-t border-gray-200 bg-white hidden" id="input-area">
                <div class="flex">
                    <input 
                        type="text" 
                        class="flex-1 border border-gray-300 rounded-l-full py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        id="message-input" 
                        placeholder="Type a message..."
                    />
                    <button 
                        class="bg-blue-500 text-white px-6 py-2 rounded-r-full hover:bg-blue-600 focus:outline-none" 
                        id="send-button"
                    >
                        Send
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="rename-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-4">Rename User</h3>
            <input 
                type="text" 
                id="rename-input" 
                class="w-full border border-gray-300 rounded-lg p-2 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter new name"
            />
            <div class="flex justify-end space-x-2">
                <button id="cancel-rename" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                    Cancel
                </button>
                <button id="save-rename" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    Save
                </button>
            </div>
        </div>
    </div>

    <script>
        const API_BASE_URL = '/api';
        let selectedChatUser = null;
        let chatUsers = [];
        let messagesPolling = null;

        const chatUsersElement = document.getElementById('chat-users');
        const chatHeaderElement = document.getElementById('chat-header');
        const messagesContainerElement = document.getElementById('messages-container');
        const noChatSelectedElement = document.getElementById('no-chat-selected');
        const messagesListElement = document.getElementById('messages-list');
        const inputAreaElement = document.getElementById('input-area');
        const messageInputElement = document.getElementById('message-input');
        const sendButtonElement = document.getElementById('send-button');
        const renameUserBtn = document.getElementById('rename-user-btn');
        const renameModal = document.getElementById('rename-modal');
        const renameInput = document.getElementById('rename-input');
        const cancelRenameBtn = document.getElementById('cancel-rename');
        const saveRenameBtn = document.getElementById('save-rename');

        document.addEventListener('DOMContentLoaded', function() {
            loadChatUsers();
            
            sendButtonElement.addEventListener('click', sendMessage);
            messageInputElement.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
            
            renameUserBtn.addEventListener('click', openRenameModal);
            cancelRenameBtn.addEventListener('click', closeRenameModal);
            saveRenameBtn.addEventListener('click', saveRename);
        });

        async function loadChatUsers() {
            try {
                const response = await fetch(`${API_BASE_URL}/chats`);
                const data = await response.json();
                
                if (data.success) {
                    chatUsers = data.data;
                    renderChatUsers();
                } else {
                    chatUsersElement.innerHTML = '<div class="p-4 text-center text-red-500">Failed to load chats</div>';
                }
            } catch (error) {
                console.error('Error loading chat users:', error);
                chatUsersElement.innerHTML = '<div class="p-4 text-center text-red-500">Error loading chats</div>';
            }
        }

        function renderChatUsers() {
            if (chatUsers.length === 0) {
                chatUsersElement.innerHTML = '<div class="p-4 text-center text-gray-500">No chats found</div>';
                return;
            }

            chatUsersElement.innerHTML = '';
            
            chatUsers.forEach(user => {
                const userElement = document.createElement('div');
                userElement.className = `p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50 ${selectedChatUser && selectedChatUser.id === user.id ? 'bg-blue-50' : ''}`;
                userElement.innerHTML = `
                    <div class="font-medium">${user.name || user.phone}</div>
                    <div class="text-sm text-gray-500">${user.phone}</div>
                `;
                
                userElement.addEventListener('click', () => selectChatUser(user));
                chatUsersElement.appendChild(userElement);
            });
        }

        async function selectChatUser(user) {
            selectedChatUser = user;
            renderChatUsers();
            
            chatHeaderElement.innerHTML = `
                <span>${user.name || user.phone}</span>
                <button id="rename-user-btn" class="text-blue-500 hover:text-blue-700 text-sm">
                    Rename
                </button>
            `;
            
            document.getElementById('rename-user-btn').addEventListener('click', openRenameModal);
            
            noChatSelectedElement.classList.add('hidden');
            messagesListElement.classList.remove('hidden');
            inputAreaElement.classList.remove('hidden');
            
            await loadMessages();
            
            if (messagesPolling) {
                clearInterval(messagesPolling);
            }
            messagesPolling = setInterval(loadMessages, 5000); 
        }

        async function loadMessages() {
            if (!selectedChatUser) return;
            
            try {
                const response = await fetch(`${API_BASE_URL}/chat/${selectedChatUser.id}`);
                const data = await response.json();
                
                if (data.success) {
                    renderMessages(data.data.whatsapp_messages);
                }
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }

        function renderMessages(messages) {
            messagesListElement.innerHTML = '';
            
            if (!messages || messages.length === 0) {
                messagesListElement.innerHTML = '<div class="text-center text-gray-500">No messages yet</div>';
                return;
            }
            
            messages.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
            
            messages.forEach(message => {
                const messageElement = document.createElement('div');
                
                if (message.type === 'interactive' && message.actions) {
                    messageElement.className = 'bg-white rounded-lg shadow p-4';
                    const buttonsHtml = message.actions.action?.buttons?.map(button => 
                        `<button class="bg-blue-100 text-blue-800 px-3 py-1 rounded mr-2 mt-2 text-sm">
                            ${button.reply?.title || 'Button'}
                        </button>`
                    ).join('') || '';
                    
                    messageElement.innerHTML = `
                        <div class="font-medium mb-2">${message.body}</div>
                        <div class="flex flex-wrap">
                            ${buttonsHtml}
                        </div>
                        <div class="text-xs text-gray-500 mt-2 text-right">
                            ${new Date(message.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                        </div>
                    `;
                } else {
                    messageElement.className = `max-w-xs md:max-w-md lg:max-w-lg xl:max-w-xl p-3 rounded-lg ${message.from === selectedChatUser.phone ? 'bg-blue-500 text-white ml-auto' : 'bg-gray-200 text-gray-800'}`;
                    
                    messageElement.innerHTML = `
                        <div>${message.body}</div>
                        <div class="text-xs mt-1 text-right opacity-70">
                            ${new Date(message.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                        </div>
                    `;
                }
                
                messagesListElement.appendChild(messageElement);
            });
            
            messagesContainerElement.scrollTop = messagesContainerElement.scrollHeight;
        }

        async function sendMessage() {
            const messageText = messageInputElement.value.trim();
            if (!messageText || !selectedChatUser) return;
            
            sendButtonElement.disabled = true;
            
            try {
                const response = await fetch(`${API_BASE_URL}/whatsapp/send-message`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        to: selectedChatUser.phone,
                        body: messageText
                    })
                });
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.indexOf('application/json') !== -1) {
                    const data = await response.json();
                    
                    if (response.ok && data.messages) {
                        messageInputElement.value = '';
                        await loadMessages();
                    } else {
                        const errorMessage = data.message || data.error || 'Failed to send message';
                        alert(`Failed to send message: ${errorMessage}`);
                    }
                } else {
                    // Handle non-JSON responses (like HTML error pages)
                    const errorText = await response.text();
                    console.error('Non-JSON response:', errorText);
                    alert('Error sending message: Server returned an invalid response');
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Error sending message: Network error or server unavailable');
            } finally {
                sendButtonElement.disabled = false;
            }
        }

        function openRenameModal() {
            if (!selectedChatUser) return;
            
            renameInput.value = selectedChatUser.name || '';
            renameModal.classList.remove('hidden');
        }

        function closeRenameModal() {
            renameModal.classList.add('hidden');
        }

        async function saveRename() {
            const newName = renameInput.value.trim();
            if (!newName || !selectedChatUser) return;
            
            try {
                const response = await fetch(`${API_BASE_URL}/chat/user/${selectedChatUser.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        name: newName
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const userIndex = chatUsers.findIndex(u => u.id === selectedChatUser.id);
                    if (userIndex !== -1) {
                        chatUsers[userIndex].name = newName;
                    }
                    
                    selectedChatUser.name = newName;
                    
                    chatHeaderElement.innerHTML = `
                        <span>${newName}</span>
                        <button id="rename-user-btn" class="text-blue-500 hover:text-blue-700 text-sm">
                            Rename
                        </button>
                    `;
                    document.getElementById('rename-user-btn').addEventListener('click', openRenameModal);
                    renderChatUsers();
                    
                    closeRenameModal();
                } else {
                    alert('Failed to rename user: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error renaming user:', error);
                alert('Error renaming user');
            }
        }

        setInterval(loadChatUsers, 30000); 
    </script>
</body>
</html>