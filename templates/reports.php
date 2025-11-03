<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Page header
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="reports-grid">
        <!-- Sales Pipeline Report -->
        <div class="report-card">
            <h3 class="report-header navy-bg">Sales Pipeline Report</h3>
            <div class="report-content">
                <select name="select-gallary" class="report-select">
                    <option value="">Select Gallery</option>
                    <?php
                    // Add gallery options here
                    ?>
                </select>
                <button class="button button-primary generate-report">Generate Report</button>
            </div>
        </div>

        <!-- Week's Plan -->
        <div class="report-card">
            <h3 class="report-header navy-bg">Week's Plan</h3>
            <div class="report-content">
                <input type="text" class="date-picker" placeholder="28 JAN 2025">
                <input type="text" class="date-picker" placeholder="02 JAN 2025">
                <select name="select-gallary" class="report-select">
                    <option value="">Select Gallery</option>
                </select>
                <button class="button button-primary generate-report">Generate Report</button>
            </div>
        </div>

        <!-- Visit Report -->
        <div class="report-card">
            <h3 class="report-header navy-bg">Visit Report</h3>
            <div class="report-content">
                <select name="select-type" class="report-select">
                    <option value="">Select Type</option>
                </select>
                <select name="select-report-type" class="report-select">
                    <option value="">Select Report Type</option>
                </select>
                <input type="text" class="year-input" placeholder="2025">
                <select name="select-gallary" class="report-select">
                    <option value="">Select Gallery</option>
                </select>
                <button class="button button-primary generate-report">Generate Report</button>
            </div>
        </div>

        <!-- Attendance Report -->
        <div class="report-card">
            <h3 class="report-header navy-bg">Attendance Report</h3>
            <div class="report-content">
                <select name="select-month" class="report-select">
                    <option value="">Select Month</option>
                </select>
                <input type="text" class="year-input" placeholder="2025">
                <select name="select-gallary" class="report-select">
                    <option value="">Select Gallery</option>
                </select>
                <button class="button button-primary generate-report">Generate Report</button>
            </div>
        </div>

        <!-- Quotation Report -->
        <div class="report-card">
            <h3 class="report-header navy-bg">Quotation Report</h3>
            <div class="report-content">
                <select name="select-type" class="report-select">
                    <option value="">Select Type</option>
                </select>
                <select name="select-report-type" class="report-select">
                    <option value="">Select Report Type</option>
                </select>
                <input type="text" class="year-input" placeholder="2025">
                <select name="select-gallary" class="report-select">
                    <option value="">Select Gallery</option>
                </select>
                <button class="button button-primary generate-report">Generate Report</button>
            </div>
        </div>

        <!-- New Client in Quarter Report -->
        <div class="report-card">
            <h3 class="report-header navy-bg">New Client in Quarter Report</h3>
            <div class="report-content">
                <select name="select-quarter" class="report-select">
                    <option value="">Select Quarter</option>
                </select>
                <input type="text" class="year-input" placeholder="2025">
                <select name="select-gallary" class="report-select">
                    <option value="">Select Gallery</option>
                </select>
                <button class="button button-primary generate-report">Generate Report</button>
            </div>
        </div>

        <!-- Lost Client in Quarter Report -->
        <div class="report-card">
            <h3 class="report-header navy-bg">Lost Client in Quarter Report</h3>
            <div class="report-content">
                <select name="select-quarter" class="report-select">
                    <option value="">Select Quarter</option>
                </select>
                <input type="text" class="year-input" placeholder="2025">
                <select name="select-gallary" class="report-select">
                    <option value="">Select Gallery</option>
                </select>
                <button class="button button-primary generate-report">Generate Report</button>
            </div>
        </div>

        <!-- Sales Report -->
        <div class="report-card">
            <h3 class="report-header navy-bg">Sales Report</h3>
            <div class="report-content">
                <select name="select-type" class="report-select">
                    <option value="">Select Type</option>
                </select>
                <select name="select-report-type" class="report-select">
                    <option value="">Select Report Type</option>
                </select>
                <input type="text" class="year-input" placeholder="2025">
                <select name="select-gallary" class="report-select">
                    <option value="">Select Gallery</option>
                </select>
                <button class="button button-primary generate-report">Generate Report</button>
            </div>
        </div>

        <!-- Dispatch Report -->
        <div class="report-card">
            <h3 class="report-header navy-bg">Dispatch Report</h3>
            <div class="report-content">
                <select name="select-type" class="report-select">
                    <option value="">Select Type</option>
                </select>
                <select name="select-report-type" class="report-select">
                    <option value="">Select Report Type</option>
                </select>
                <input type="text" class="year-input" placeholder="2025">
                <select name="select-gallary" class="report-select">
                    <option value="">Select Gallery</option>
                </select>
                <button class="button button-primary show-report">Show Report</button>
            </div>
        </div>

        <!-- Sample for projects(free) Report -->
        <div class="report-card">
            <h3 class="report-header navy-bg">Sample for projects(free) Report</h3>
            <div class="report-content">
                <select name="select-type" class="report-select">
                    <option value="">Select Type</option>
                </select>
                <select name="select-report-type" class="report-select">
                    <option value="">Select Report Type</option>
                </select>
                <input type="text" class="year-input" placeholder="2025">
                <select name="select-gallary" class="report-select">
                    <option value="">Select Gallery</option>
                </select>
                <button class="button button-primary show-report">Show Report</button>
            </div>
        </div>

        <!-- Client Feedback Report -->
        <div class="report-card">
            <h3 class="report-header navy-bg">Client Feedback Report</h3>
            <div class="report-content">
                <select name="select-type" class="report-select">
                    <option value="">Select Type</option>
                </select>
                <select name="select-report-type" class="report-select">
                    <option value="">Select Report Type</option>
                </select>
                <input type="text" class="year-input" placeholder="2025">
                <select name="select-gallary" class="report-select">
                    <option value="">Select Gallery</option>
                </select>
                <button class="button button-primary generate-report">Generate Report</button>
            </div>
        </div>
    </div>

    <style>
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        padding: 20px 0;
    }

    .report-card {
        background: white;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        overflow: hidden;
    }

    .report-header {
        margin: 0;
        padding: 10px 15px;
        color: white;
        font-size: 14px;
    }

    .navy-bg {
        background-color: #000066;
    }

    .report-content {
        padding: 15px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .report-select,
    .date-picker,
    .year-input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .generate-report,
    .show-report {
        background-color: #92b92a !important;
        border-color: #92b92a !important;
        color: white !important;
        text-align: center;
        padding: 8px !important;
        border-radius: 4px;
        cursor: pointer;
        width: auto;
        align-self: flex-start;
    }

    .generate-report:hover,
    .show-report:hover {
        background-color: #7a9c23 !important;
        border-color: #7a9c23 !important;
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Initialize date pickers
        $('.date-picker').datepicker({
            dateFormat: 'dd M yy',
            changeMonth: true,
            changeYear: true
        });

        // Handle report generation
        $('.generate-report, .show-report').click(function(e) {
            e.preventDefault();
            const $form = $(this).closest('.report-content');
            const reportType = $(this).closest('.report-card').find('.report-header').text();
            
            // Collect form data
            const formData = {
                action: 'generate_report',
                report_type: reportType,
                gallery: $form.find('[name="select-gallary"]').val(),
                type: $form.find('[name="select-type"]').val(),
                report_type_sub: $form.find('[name="select-report-type"]').val(),
                year: $form.find('.year-input').val(),
                quarter: $form.find('[name="select-quarter"]').val(),
                month: $form.find('[name="select-month"]').val(),
                dates: $form.find('.date-picker').map(function() {
                    return $(this).val();
                }).get(),
                nonce: signageManagerData.nonce
            };

            // Show loading state
            $(this).prop('disabled', true).text('Loading...');

            // Make AJAX request
            $.post(ajaxurl, formData, function(response) {
                if (response.success) {
                    // Handle successful report generation
                    if (response.data.downloadUrl) {
                        window.location.href = response.data.downloadUrl;
                    } else {
                        // Show report in modal or new window
                        showReport(response.data);
                    }
                } else {
                    alert(response.data.message || 'Error generating report');
                }
            }).always(function() {
                // Reset button state
                const $button = $('.generate-report, .show-report').prop('disabled', false);
                $button.text($button.hasClass('generate-report') ? 'Generate Report' : 'Show Report');
            });
        });

        function showReport(data) {
            // Implementation for showing report in modal or new window
        }
    });
    </script>
</div>