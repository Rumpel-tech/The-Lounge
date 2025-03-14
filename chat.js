document.addEventListener('DOMContentLoaded', function() {
    const ws = new WebSocket('ws://localhost:8081');
    const chatMessages = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');

    const username = 'Guest' + Math.floor(Math.random() * 1000);

    ws.onopen = function() {
        console.log('WebSocket connection established');
    };

    ws.onmessage = function(event) {
        console.log('Message received: ', event.data);
        const message = document.createElement('div');
        message.textContent = event.data;
        message.classList.add('message'); // Add a class for styling
        chatMessages.appendChild(message);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    };

    ws.onerror = function(error) {
        console.log('WebSocket error: ', error);
    };

    ws.onclose = function() {
        console.log('WebSocket connection closed');
    };

    sendButton.addEventListener('click', function() {
        const message = {
            username: username,
            message: messageInput.value
        };
        ws.send(JSON.stringify(message));
        messageInput.value = '';
        console.log('Message sent: ', message);
    });

    messageInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            sendButton.click();
        }
    });
});