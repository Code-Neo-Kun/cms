<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue required scripts
wp_enqueue_script('fullcalendar');
wp_enqueue_style('fullcalendar');
?>

<style>
    .cosign-wrap {
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .cosign-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }

    .cosign-header h1 {
        margin: 0;
        font-size: 24px;
        color: #1e1e1e;
    }

    .cosign-btn {
        background: #000066;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.3s;
    }

    .cosign-btn:hover {
        background: #000099;
        color: white;
    }

    .cosign-calendar {
        margin-top: 20px;
    }

    /* FullCalendar Customizations */
    .fc {
        --fc-border-color: #eee;
        --fc-button-bg-color: #fff;
        --fc-button-border-color: #ddd;
        --fc-button-text-color: #666;
        --fc-button-hover-bg-color: #f8f9fa;
        --fc-button-hover-border-color: #ddd;
        --fc-button-active-bg-color: #e9ecef;
        --fc-today-bg-color: #fff7e6;
    }

    .fc .fc-toolbar-title {
        font-size: 1.5rem;
        color: #1e1e1e;
    }

    .fc .fc-button {
        padding: 6px 12px;
        font-weight: 500;
        border-radius: 4px;
        text-transform: capitalize;
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active {
        background: #000066;
        border-color: #000066;
        color: white;
    }

    .fc-day-today {
        background: var(--fc-today-bg-color) !important;
    }

    .fc-daygrid-day {
        transition: background-color 0.2s;
    }

    .fc-daygrid-day:hover {
        background-color: #f8f9fa;
    }

    .fc-daygrid-day-number {
        color: #1e1e1e;
        font-weight: 500;
        padding: 8px !important;
    }

    .fc .fc-day-other .fc-daygrid-day-number {
        color: #aaa;
    }

    .day-indicator {
        display: flex;
        gap: 4px;
        position: absolute;
        top: 8px;
        right: 8px;
    }

    .indicator {
        display: flex;
        align-items: center;
        gap: 2px;
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 12px;
        color: white;
    }

    .meeting-count {
        background: #000066;
    }

    .task-count {
        background: #92b92a;
    }

    /* Day Click Modal */
    .day-modal {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        width: 320px;
    }

    .day-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
    }

    .day-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid #eee;
    }

    .day-modal-title {
        font-size: 18px;
        font-weight: 600;
        color: #1e1e1e;
    }

    .day-modal-close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: #666;
    }

    .day-modal-actions {
        display: grid;
        gap: 12px;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px;
        border: 1px solid #eee;
        border-radius: 6px;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        color: #1e1e1e;
    }

    .action-btn:hover {
        background: #f8f9fa;
        border-color: #ddd;
    }

    .action-btn i {
        font-size: 16px;
    }

    .action-btn.meeting {
        border-left: 4px solid #000066;
    }

    .action-btn.task {
        border-left: 4px solid #92b92a;
    }

    .action-btn.comment {
        border-left: 4px solid #f59e0b;
    }
</style>

<!-- Day Click Modal -->
<div class="day-modal-overlay" id="dayModalOverlay"></div>
<div class="day-modal" id="dayModal">
    <div class="day-modal-header">
        <div class="day-modal-title">Add New Item</div>
        <button class="day-modal-close">&times;</button>
    </div>
    <div class="day-modal-actions">
        <a href="#" class="action-btn meeting" id="addMeeting">
            <i class="fas fa-calendar-check"></i>
            Add Meeting
        </a>
        <a href="#" class="action-btn task" id="addTask">
            <i class="fas fa-tasks"></i>
            Add Task
        </a>
        <a href="#" class="action-btn comment" id="addComment">
            <i class="fas fa-comment"></i>
            Add Comment
        </a>
    </div>
</div>

<div class="wrap cosign-wrap">
    <div class="cosign-header">
        <h1>Calendar</h1>
        <a href="<?php echo esc_url(admin_url('admin.php?page=cosign-add-meeting')); ?>" class="cosign-btn">
            + New Meeting/Task
        </a>
    </div>

    <div class="cosign-calendar" id="calendar"></div>
</div>

<script>
jQuery(document).ready(function($) {
    var selectedDate;
    
    var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev today next',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Today',
            month: 'Month',
            week: 'Week',
            day: 'Day'
        },
        dayMaxEvents: true,
        height: 'auto',
        firstDay: 0, // Start week on Sunday
        dayCellDidMount: function(info) {
            // Add indicators container
            var indicators = document.createElement('div');
            indicators.className = 'day-indicator';
            
            // Get counts for this day from our events data
            var date = info.date;
            var dateStr = date.toISOString().split('T')[0];
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cosign_get_day_counts',
                    nonce: '<?php echo wp_create_nonce("cosign_nonce"); ?>',
                    date: dateStr
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.meetings > 0) {
                            var meetingIndicator = document.createElement('span');
                            meetingIndicator.className = 'indicator meeting-count';
                            meetingIndicator.innerHTML = '<i class="fas fa-calendar-check"></i> ' + response.data.meetings;
                            indicators.appendChild(meetingIndicator);
                        }
                        
                        if (response.data.tasks > 0) {
                            var taskIndicator = document.createElement('span');
                            taskIndicator.className = 'indicator task-count';
                            taskIndicator.innerHTML = '<i class="fas fa-tasks"></i> ' + response.data.tasks;
                            indicators.appendChild(taskIndicator);
                        }
                        
                        info.el.appendChild(indicators);
                    }
                }
            });
        },
        dateClick: function(info) {
            selectedDate = info.dateStr;
            showDayModal(info.dateStr);
        },
        views: {
            dayGridMonth: {
                dayMaxEventRows: 2,
                fixedWeekCount: false
            }
        },
        events: function(info, successCallback, failureCallback) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cosign_get_meetings',
                    nonce: '<?php echo wp_create_nonce('cosign_nonce'); ?>',
                    start_date: info.startStr,
                    end_date: info.endStr
                },
                success: function(response) {
                    if (response.success) {
                        var events = response.data.map(function(meeting) {
                            return {
                                id: meeting.id,
                                title: meeting.title,
                                start: meeting.start_time,
                                end: meeting.end_time,
                                location: meeting.location,
                                client: meeting.client_name,
                                extendedProps: {
                                    type: meeting.meeting_type
                                }
                            };
                        });
                        successCallback(events);
                    }
                }
            });
        },
        eventClick: function(info) {
            // Show meeting details in a modal
            showMeetingDetails(info.event);
        }
    });

    calendar.render();

    function showMeetingDetails(event) {
        var content = '<h3>' + event.title + '</h3>' +
                     '<p><strong>Client:</strong> ' + event.extendedProps.client + '</p>' +
                     '<p><strong>Location:</strong> ' + event.extendedProps.location + '</p>' +
                     '<p><strong>Type:</strong> ' + event.extendedProps.type + '</p>' +
                     '<p><strong>Start:</strong> ' + event.start.toLocaleString() + '</p>' +
                     '<p><strong>End:</strong> ' + event.end.toLocaleString() + '</p>';

        // Use WordPress thickbox or your preferred modal
        tb_show('Meeting Details', '#TB_inline?width=600&height=400&inlineId=meeting-details');
        $('#meeting-details').html(content);
    }

    function showDayModal(date) {
        const modal = document.getElementById('dayModal');
        const overlay = document.getElementById('dayModalOverlay');
        const formattedDate = new Date(date).toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        // Update modal title with selected date
        document.querySelector('.day-modal-title').textContent = formattedDate;

        // Update action URLs with the selected date
        document.getElementById('addMeeting').href = '<?php echo admin_url("admin.php?page=cosign-add-meeting&date="); ?>' + date;
        document.getElementById('addTask').href = '<?php echo admin_url("admin.php?page=cosign-add-meeting&type=task&date="); ?>' + date;
        document.getElementById('addComment').href = '<?php echo admin_url("admin.php?page=cosign-comments&date="); ?>' + date;

        modal.style.display = 'block';
        overlay.style.display = 'block';
    }

    // Close modal when clicking the close button or overlay
    document.querySelector('.day-modal-close').addEventListener('click', function() {
        document.getElementById('dayModal').style.display = 'none';
        document.getElementById('dayModalOverlay').style.display = 'none';
    });

    document.getElementById('dayModalOverlay').addEventListener('click', function() {
        document.getElementById('dayModal').style.display = 'none';
        document.getElementById('dayModalOverlay').style.display = 'none';
    });
});
</script>

<div id="meeting-details" style="display:none;"></div>