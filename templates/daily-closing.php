<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<style>
    :root {
        --primary-color: #000066;
        --secondary-color: #92b92a;
        --text-color: #2c3e50;
        --light-gray: #f8f9fa;
        --border-color: #e2e8f0;
        --shadow: 0 2px 4px rgba(0,0,0,0.1);
        --hover-shadow: 0 4px 6px rgba(0,0,0,0.1);
        --transition: all 0.3s ease;
        --radius: 8px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .form-container {
        max-width: 800px;
        margin: 2rem auto;
        background: white;
        padding: 0;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--border-color);
    }

    .form-header {
        background: linear-gradient(135deg, var(--primary-color), #000099);
        color: white;
        padding: 1.5rem 2rem;
        border-radius: var(--radius) var(--radius) 0 0;
        position: relative;
        overflow: hidden;
    }

    .form-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
    }

    .breadcrumb {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1rem;
        align-items: center;
        font-size: 0.9rem;
    }

    .breadcrumb a {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        transition: var(--transition);
    }

    .breadcrumb a:hover {
        color: white;
    }

    .breadcrumb span {
        color: rgba(255, 255, 255, 0.7);
    }

    h1 {
        font-size: 1.75rem;
        color: white;
        margin: 0;
        font-weight: 600;
    }

    .form-content {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    label {
        display: block;
        margin-bottom: 0.75rem;
        color: var(--text-color);
        font-weight: 600;
        font-size: 0.95rem;
    }

    .required::after {
        content: " *";
        color: #ef4444;
        font-weight: 400;
    }

    input[type="date"],
    textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        font-size: 0.95rem;
        transition: var(--transition);
        color: var(--text-color);
    }

    input[type="date"]:focus,
    textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(0, 0, 102, 0.1);
    }

    textarea {
        min-height: 200px;
        resize: vertical;
        line-height: 1.5;
    }

    .button-group {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
    }

    .btn {
        padding: 0.75rem 2rem;
        border: none;
        border-radius: var(--radius);
        cursor: pointer;
        font-weight: 600;
        font-size: 0.95rem;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn i {
        font-size: 1rem;
    }

    .btn-submit {
        background-color: var(--secondary-color);
        color: white;
    }

    .btn-submit:hover {
        background-color: #7a9c23;
        transform: translateY(-1px);
    }

    .btn-submit:disabled {
        background-color: #94a3b8;
        cursor: not-allowed;
        transform: none;
    }

    input[readonly] {
        background-color: var(--light-gray);
        cursor: not-allowed;
        opacity: 0.7;
    }

    .success-message {
        background-color: #dcfce7;
        border: 1px solid #bbf7d0;
        color: #166534;
        padding: 1rem 1.25rem;
        border-radius: var(--radius);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideIn 0.3s ease;
    }

    .error-message {
        background-color: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
        padding: 1rem 1.25rem;
        border-radius: var(--radius);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideIn 0.3s ease;
    }

    .success-message i,
    .error-message i {
        font-size: 1.25rem;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .form-container {
            margin: 1rem;
        }

        .form-header {
            padding: 1.25rem 1.5rem;
        }

        .form-content {
            padding: 1.5rem;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .button-group {
            flex-direction: column;
        }
    }
</style>

<div class="form-container">
    <div class="form-header">
        <div class="breadcrumb">
            <a href="<?php echo admin_url('admin.php?page=cosign-dashboard'); ?>">
                <i class="fas fa-home"></i>
                Home
            </a>
            <span>></span>
            <span>Daily Closing</span>
        </div>

        <h1>Daily Closing</h1>
    </div>

    <div class="form-content">
        <div id="messageContainer"></div>

        <form id="dailyClosingForm">
            <div class="form-group">
                <label for="closingDate" class="required">
                    <i class="fas fa-calendar"></i>
                    Daily Closing Date
                </label>
                <input type="date" id="closingDate" name="closingDate" required readonly>
            </div>

            <div class="form-group">
                <label for="highlights" class="required">
                    <i class="fas fa-star"></i>
                    Key Highlights Of The Day
                </label>
                <textarea 
                    id="highlights" 
                    name="highlights" 
                    required 
                    placeholder="• List your key accomplishments&#10;• Important meetings or decisions&#10;• Progress on ongoing projects&#10;• Any challenges faced and solutions implemented"
                ></textarea>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-check"></i>
                    Submit Daily Report
                </button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Set today's date in the date field
    const today = new Date();
    const formattedDate = today.toISOString().split('T')[0];
    $('#closingDate').val(formattedDate);

    // Handle form submission
    $('#dailyClosingForm').on('submit', function(e) {
        e.preventDefault();
        
        const highlights = $('#highlights').val().trim();
        
        if (!highlights) {
            showMessage('Please enter key highlights of the day.', 'error');
            return;
        }

        // Show loading state
        $('.btn-submit').prop('disabled', true).text('Submitting...');

        $.ajax({
            url: cosignData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cosign_save_daily_closing',
                nonce: cosignData.nonce,
                closing_date: $('#closingDate').val(),
                highlights: $('#highlights').val().trim()
            },
            success: function(response) {
                if (response.success) {
                    showMessage(response.data.message, 'success');
                    $('#highlights').val('');
                    
                    // Redirect to dashboard after 2 seconds
                    setTimeout(function() {
                        window.location.href = '<?php echo admin_url("admin.php?page=cosign-dashboard"); ?>';
                    }, 2000);
                } else {
                    showMessage(response.data.message, 'error');
                    $('.btn-submit').prop('disabled', false).text('Submit');
                }
            },
            error: function() {
                showMessage('An error occurred. Please try again.', 'error');
                $('.btn-submit').prop('disabled', false).text('Submit');
            }
        });
    });

    function showMessage(message, type) {
        const icon = type === 'success' ? 
            '<i class="fas fa-check-circle"></i>' : 
            '<i class="fas fa-exclamation-circle"></i>';
        const messageClass = type === 'success' ? 'success-message' : 'error-message';
        const html = '<div class="' + messageClass + '">' + icon + message + '</div>';
        
        // Fade out existing message if any
        $('#messageContainer').fadeOut(200, function() {
            $(this).html(html).fadeIn(300);
            
            // Scroll to message
            $('html, body').animate({
                scrollTop: $('#messageContainer').offset().top - 100
            }, 500);
        });

        // Auto-hide error messages after 5 seconds
        if (type === 'error') {
            setTimeout(function() {
                $('#messageContainer').fadeOut(300, function() {
                    $(this).html('');
                });
            }, 5000);
        }
    }
});
</script>