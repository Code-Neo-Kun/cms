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
    --transition: all 0.3s ease;
    --radius: 8px;
}

.wrap.cosign-wrap {
    max-width: 1400px;
    margin: 2rem auto;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.cosign-header {
    background: linear-gradient(135deg, var(--primary-color), #000099);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: var(--radius) var(--radius) 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cosign-header h1 {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 600;
}

.unread-badge {
    background: #ef4444;
    color: white;
    border-radius: 12px;
    padding: 0.25rem 0.6rem;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 0.5rem;
}

.comments-container {
    padding: 2rem;
}

.comment-item {
    background: var(--light-gray);
    padding: 1.5rem;
    border-radius: var(--radius);
    margin-bottom: 1rem;
    border-left: 4px solid var(--secondary-color);
    transition: var(--transition);
}

.comment-item:hover {
    box-shadow: var(--shadow);
    transform: translateX(5px);
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.comment-meta {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.comment-author {
    font-weight: 600;
    color: var(--primary-color);
}

.comment-date {
    color: #64748b;
    font-size: 0.9rem;
}

.comment-content {
    margin-bottom: 1rem;
    line-height: 1.6;
}

.comment-actions {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    transition: var(--transition);
    border: none;
    color: white;
    font-size: 0.875rem;
}

.action-btn.mark-read {
    background: var(--secondary-color);
}

.action-btn.view-task {
    background: #3b82f6;
}

.action-btn:hover {
    opacity: 0.9;
}
</style>

<div class="wrap cosign-wrap">
    <div class="cosign-header">
        <h1>
            <i class="fas fa-comments"></i> Unread Comments
            <span class="unread-badge" id="unread-count">0</span>
        </h1>
    </div>

    <div class="comments-container" id="comments-container">
        <div style="text-align: center; padding: 3rem;">
            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
            <p>Loading unread comments...</p>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    function loadUnreadComments() {
        $.ajax({
            url: cosignData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cosign_get_unread_comments',
                nonce: cosignData.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayComments(response.data.comments || []);
                    $('#unread-count').text(response.data.total || 0);
                } else {
                    $('#comments-container').html('<div style="text-align: center; padding: 3rem;">No unread comments</div>');
                }
            },
            error: function() {
                $('#comments-container').html('<div style="text-align: center; padding: 3rem; color: #ef4444;">Error loading comments</div>');
            }
        });
    }

    function displayComments(comments) {
        if (comments.length === 0) {
            $('#comments-container').html('<div style="text-align: center; padding: 3rem;">All comments have been read</div>');
            return;
        }

        let html = '';
        comments.forEach(function(comment) {
            const date = comment.created_at ? new Date(comment.created_at).toLocaleString() : '-';
            
            html += `
                <div class="comment-item">
                    <div class="comment-header">
                        <div class="comment-meta">
                            <span class="comment-author">${comment.commented_by_name || 'Unknown'}</span>
                            <span class="comment-date">${date}</span>
                        </div>
                    </div>
                    <div class="comment-content">
                        <p><strong>Task:</strong> ${comment.task_title || '-'}</p>
                        <p><strong>Comment:</strong> ${comment.comment || '-'}</p>
                    </div>
                    <div class="comment-actions">
                        <button class="action-btn mark-read" onclick="markAsRead(${comment.id})">
                            <i class="fas fa-check"></i> Mark as Read
                        </button>
                        <button class="action-btn view-task" onclick="viewTask(${comment.task_id})">
                            <i class="fas fa-eye"></i> View Task
                        </button>
                    </div>
                </div>
            `;
        });

        $('#comments-container').html(html);
    }

    window.markAsRead = function(commentId) {
        $.ajax({
            url: cosignData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cosign_mark_comment_read',
                nonce: cosignData.nonce,
                comment_id: commentId
            },
            success: function(response) {
                if (response.success) {
                    loadUnreadComments();
                } else {
                    alert(response.data || 'Failed to mark comment as read');
                }
            }
        });
    };

    window.viewTask = function(taskId) {
        window.location.href = '<?php echo admin_url("admin.php?page=cosign-task-list&task_id="); ?>' + taskId;
    };

    // Initial load
    loadUnreadComments();
});
</script>
