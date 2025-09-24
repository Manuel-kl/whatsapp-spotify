<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Spotify Chat Interface</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-gray-800 py-4 px-6 sticky top-0 z-50 border-b border-gray-700">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fab fa-spotify text-green-500 text-2xl"></i>
                <span class="text-xl font-bold">WhatsApp<span class="text-green-500">Spotify</span></span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="/" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium transition duration-300">
                    Home
                </a>
                <a href="/dashboard" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium transition duration-300">
                    Dashboard
                </a>
                <a href="/analytics" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium transition duration-300">
                    Analytics
                </a>
                <a href="/spotify-playlists" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium transition duration-300">
                    My Playlists
                </a>
                <a href="/chat" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-full font-medium transition duration-300">
                    Chat
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto flex flex-1 overflow-hidden p-4">
        <!-- Sidebar with chat users -->
        <div class="w-1/3 bg-gray-800 rounded-lg border border-gray-700 flex flex-col mr-4">
            <div class="p-4 border-b border-gray-700 font-semibold flex items-center justify-between bg-gray-900">
                <span>Chats</span>
                <button id="refresh-chats" class="text-gray-400 hover:text-white">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto" id="chat-users">
                <div class="p-4 text-center text-gray-500">Loading chats...</div>
            </div>
        </div>

        <div class="w-2/3 flex flex-col bg-gray-800 rounded-lg border border-gray-700">
            <div class="p-4 border-b border-gray-700 font-semibold flex items-center justify-between bg-gray-900" id="chat-header">
                <span>Select a chat</span>
                <button id="rename-user-btn" class="text-green-500 hover:text-green-400 text-sm hidden">
                    Rename
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 bg-gray-900" id="messages-container">
                <div class="flex items-center justify-center h-full text-gray-500" id="no-chat-selected">
                    Select a chat to start messaging
                </div>
                <div id="messages-list" class="space-y-4 hidden"></div>
            </div>
            <div class="p-4 border-t border-gray-700 bg-gray-900 hidden" id="input-area">
                <div class="flex">
                    <input 
                        type="text" 
                        class="flex-1 bg-gray-700 text-white border border-gray-600 rounded-l-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-green-500" 
                        id="message-input" 
                        placeholder="Type a message..."
                    />
                    <button 
                        class="bg-green-600 text-white px-6 py-2 rounded-r-lg hover:bg-green-700 focus:outline-none transition duration-300" 
                        id="send-button"
                    >
                        Send
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- WhatsApp Connection Modal -->
    <div id="whatsapp-connection-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-gray-800 rounded-lg p-6 w-96 border border-gray-700 max-w-11/12">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-white">
                    <i class="fab fa-whatsapp text-green-500 mr-2"></i>
                    Connect to WhatsApp
                </h3>
                <button id="close-modal-btn" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="text-gray-300 mb-4">
                <p class="mb-3">This application is currently using the Meta test number.</p>
                <p class="mb-4">To start chatting, please initiate a conversation from your WhatsApp to the number below:</p>
                
                <div class="bg-gray-700 rounded-lg p-3 mb-4 flex items-center justify-between">
                    <span id="phone-number-display" class="font-mono text-green-400">+1 (555) 178-2401</span>
                    <button id="copy-number-btn" class="ml-2 text-gray-300 hover:text-white">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                
                <div id="copy-status" class="text-green-500 text-sm hidden mb-2"></div>
            </div>
            <div class="flex justify-end">
                <button id="dismiss-modal-btn" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-300">
                    Got It
                </button>
            </div>
        </div>
    </div>

    <div id="rename-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-gray-800 rounded-lg p-6 w-96 border border-gray-700">
            <h3 class="text-lg font-semibold mb-4">Rename User</h3>
            <input 
                type="text" 
                id="rename-input" 
                class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg p-2 mb-4 focus:outline-none focus:ring-2 focus:ring-green-500"
                placeholder="Enter new name"
            />
            <div class="flex justify-end space-x-2">
                <button id="cancel-rename" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition duration-300">
                    Cancel
                </button>
                <button id="save-rename" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-300">
                    Save
                </button>
            </div>
        </div>
    </div>

    <style>
        /* Prevent long words from causing overflow in chat messages */
        .message-content {
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }
    </style>
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
        const refreshChatsBtn = document.getElementById('refresh-chats');
        const whatsappModal = document.getElementById('whatsapp-connection-modal');
        const closeModalBtn = document.getElementById('close-modal-btn');
        const dismissModalBtn = document.getElementById('dismiss-modal-btn');
        const copyNumberBtn = document.getElementById('copy-number-btn');
        const copyStatus = document.getElementById('copy-status');

        document.addEventListener('DOMContentLoaded', function() {
            loadChatUsers();
            
            sendButtonElement.addEventListener('click', sendMessage);
            messageInputElement.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
            
            // Show WhatsApp connection modal on page load
            setTimeout(() => {
                whatsappModal.classList.remove('hidden');
            }, 500);
            
            // Close modal buttons
            closeModalBtn.addEventListener('click', function() {
                whatsappModal.classList.add('hidden');
            });
            
            dismissModalBtn.addEventListener('click', function() {
                whatsappModal.classList.add('hidden');
            });
            
            // Copy phone number functionality
            copyNumberBtn.addEventListener('click', function() {
                const phoneNumber = '+1 (555) 178-2401';
                
                navigator.clipboard.writeText(phoneNumber).then(function() {
                    copyStatus.textContent = 'Phone number copied to clipboard!';
                    copyStatus.classList.remove('hidden');
                    
                    setTimeout(() => {
                        copyStatus.classList.add('hidden');
                    }, 3000);
                }).catch(function(err) {
                    console.error('Failed to copy phone number: ', err);
                    copyStatus.textContent = 'Failed to copy phone number';
                    copyStatus.classList.remove('hidden').classList.add('text-red-500');
                    
                    setTimeout(() => {
                        copyStatus.classList.add('hidden');
                        copyStatus.classList.remove('text-red-500');
                    }, 3000);
                });
            });
            
            renameUserBtn.addEventListener('click', openRenameModal);
            cancelRenameBtn.addEventListener('click', closeRenameModal);
            saveRenameBtn.addEventListener('click', saveRename);
            refreshChatsBtn.addEventListener('click', loadChatUsers);
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
                userElement.className = `p-4 border-b border-gray-700 cursor-pointer hover:bg-gray-700 ${selectedChatUser && selectedChatUser.id === user.id ? 'bg-gray-700' : ''}`;
                userElement.innerHTML = `
                    <div class="font-medium">${user.name || user.phone}</div>
                    <div class="text-sm text-gray-400">${user.phone}</div>
                `;
                
                userElement.addEventListener('click', () => selectChatUser(user));
                chatUsersElement.appendChild(userElement);
            });
        }

        async function selectChatUser(user) {
            selectedChatUser = user;
            renderChatUsers();
            
            chatHeaderElement.innerHTML = `
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-user text-green-500 text-sm"></i>
                    </div>
                    <div>
                        <span>${user.name || user.phone}</span>
                        <div class="text-xs text-gray-400">${user.phone}</div>
                    </div>
                </div>
                <button id="rename-user-btn" class="text-green-500 hover:text-green-400 text-sm">
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
                    messageElement.className = 'bg-gray-700 rounded-lg p-4 border border-gray-600';
                    const buttonsHtml = message.actions.action?.buttons?.map(button => 
                        `<button class="bg-green-600 text-white px-3 py-1 rounded mr-2 mt-2 text-sm hover:bg-green-700">
                            ${button.reply?.title || 'Button'}
                        </button>`
                    ).join('') || '';
                    
                    messageElement.innerHTML = `
                        <div class="font-medium mb-2 message-content">${message.body}</div>
                        <div class="flex flex-wrap">
                            ${buttonsHtml}
                        </div>
                        <div class="text-xs text-gray-400 mt-2 text-right">
                            ${new Date(message.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                        </div>
                    `;
                } else {
                    messageElement.className = `max-w-xs md:max-w-md lg:max-w-lg xl:max-w-xl p-3 rounded-lg ${message.from === selectedChatUser.phone ? 'bg-gray-700 text-white mr-4' : 'bg-green-600 text-white ml-auto'}`;
                    
                    messageElement.innerHTML = `
                        <div class="message-content">${message.body}</div>
                        <div class="text-xs mt-1 text-right opacity-80">
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
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-user text-green-500 text-sm"></i>
                            </div>
                            <div>
                                <span>${newName}</span>
                                <div class="text-xs text-gray-400">${selectedChatUser.phone}</div>
                            </div>
                        </div>
                        <button id="rename-user-btn" class="text-green-500 hover:text-green-400 text-sm">
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