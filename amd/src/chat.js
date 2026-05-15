define(['jquery'], function($) {
    return {
        init: function(courseid) {

            const chatBox = $('#chat-box');
            const input = $('#chat-input');
            const sendBtn = $('#send-btn');

            /**
             * Escape HTML (basic XSS protection)
             * @param {string} text
             */
            function escapeHtml(text) {
                return $('<div>').text(text).html();
            }

            /**
             * Append message bubble
             * @param {string} sender
             * @param {string} text
             * @param {string} isTemp
             */
            function appendMessage(sender, text, isTemp = false) {

                let alignment = sender === 'You' ? 'text-end' : 'text-start';
                let bg = sender === 'You' ? 'bg-primary text-white' : 'bg-light';

                const messageId = isTemp ? 'typing-msg' : '';

                const msg = `
                    <div class="mb-2 ${alignment}" ${messageId ? `id="${messageId}"` : ''}>
                        <div class="d-inline-block p-2 rounded ${bg}" style="max-width: 75%;">
                            <small><strong>${sender}</strong></small><br/>
                            ${escapeHtml(text)}
                        </div>
                    </div>
                `;

                chatBox.append(msg);
                scrollToBottom();
            }

            /**
             * Scroll chat to bottom
             */
            function scrollToBottom() {
                chatBox.scrollTop(chatBox[0].scrollHeight);
            }

            /**
             * Remove typing indicator
             */
            function removeTyping() {
                $('#typing-msg').remove();
            }

            /**
             * Load chat history
             */
            function loadHistory() {
                $.get('ajax.php', {
                    action: 'history',
                    courseid: courseid
                }, function(response) {

                    if (response.status === 'success') {

                        chatBox.html('');

                        response.data.forEach(item => {
                            appendMessage('You', item.userinput);
                            appendMessage('AI', item.agentresponse);
                        });
                    }
                }).fail(function() {
                    appendMessage('AI', '⚠️ Failed to load history.');
                });
            }

            /**
             * Send message
             */
            function sendMessage() {

                const message = input.val().trim();
                if (!message) {
                    return;
                }
                // Show user message
                appendMessage('You', message);
                input.val('');

                // Disable button
                sendBtn.prop('disabled', true);

                // Show typing indicator
                appendMessage('AI', 'Typing...', true);

                $.post('ajax.php', {
                    action: 'send',
                    message: message,
                    courseid: courseid
                }, function(response) {

                    removeTyping();

                    if (response.status === 'success') {
                        appendMessage('AI', response.data.response);
                    } else {
                        appendMessage('AI', '⚠️ Error processing request.');
                    }

                }).fail(function(xhr) {

                    removeTyping();

                    let errorMessage = '⚠️ Server error.';

                    try {

                        const response = JSON.parse(xhr.responseText);

                        if (response.message) {
                            errorMessage = '⚠️ ' + response.message;
                        }

                    } catch (e) {
                        errorMessage = e;
                    }

                    appendMessage('AI', errorMessage);

                }).always(function() {

                    sendBtn.prop('disabled', false);
                    input.focus();

                });
            }

            /**
             * Event bindings
             */
            sendBtn.on('click', sendMessage);

            input.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            /**
             * Init
             */
            loadHistory();
        }
    };
});