// assets/js/main.js

$(document).ready(function() {
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 800);
        }
    });

    // Handle Active Nav Links
    const currentPath = window.location.pathname;
    $('.nav-link, .sidebar-link').each(function() {
        if ($(this).attr('href') && currentPath.includes($(this).attr('href'))) {
            $(this).addClass('active');
        }
    });

    // Form Validation Bootstrap
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Simple Chat Polling Logic (If chat-window exists)
    if ($('#chat-messages').length > 0) {
        const partnerId = $('#partner-id').val();
        
        function fetchMessages() {
            $.get(`${APP_URL}/includes/api/chat.php?action=fetch&partner_id=${partnerId}`, function(data) {
                const messages = JSON.parse(data);
                let html = '';
                messages.forEach(msg => {
                    const isMe = msg.sender_id == currentUserId;
                    html += `
                        <div class="d-flex ${isMe ? 'justify-content-end' : 'justify-content-start'} mb-3">
                            <div class="p-3 rounded-4 ${isMe ? 'bg-primary text-white' : 'bg-light'} max-w-75">
                                ${msg.message}
                                <div class="text-end mt-1" style="font-size: 10px; opacity: 0.7;">${msg.created_at}</div>
                            </div>
                        </div>
                    `;
                });
                $('#chat-messages').html(html);
                $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
            });
        }

        setInterval(fetchMessages, 3000); // Poll every 3 seconds
        fetchMessages();
    }
});
