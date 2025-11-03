<?php
if (!defined('ABSPATH')) {
    exit;
}

$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
$is_edit = !empty($project_id);

global $wpdb;
$projects_table = $wpdb->prefix . 'pipeline_projects';
$quotations_table = $wpdb->prefix . 'quotations';
$clients_table = $wpdb->prefix . 'clients';

$project = null;
if ($is_edit) {
    // Try pipeline_projects first
    $project = $wpdb->get_row($wpdb->prepare(
        "SELECT p.*, c.company_name as client_name
         FROM $projects_table p
         LEFT JOIN $clients_table c ON p.client_id = c.id
         WHERE p.id = %d",
        $project_id
    ));
    
    // If not found, try quotations table
    if (!$project) {
        $project = $wpdb->get_row($wpdb->prepare(
            "SELECT q.*, c.company_name as client_name
             FROM $quotations_table q
             LEFT JOIN $clients_table c ON q.client_id = c.id
             WHERE q.id = %d",
            $project_id
        ));
    }
}

$clients = \CoSignPlanner\cosign_get_clients_list();
$users = \CoSignPlanner\cosign_get_users_list();
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
    max-width: 1200px;
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

.cosign-form {
    padding: 2rem;
}

.cosign-form-row {
    margin-bottom: 1.5rem;
}

.cosign-form-row label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-color);
    font-weight: 600;
    font-size: 0.95rem;
}

.cosign-form-row input[type="text"],
.cosign-form-row input[type="number"],
.cosign-form-row input[type="date"],
.cosign-form-row select,
.cosign-form-row textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    font-size: 0.95rem;
    transition: var(--transition);
    box-sizing: border-box;
}

.cosign-form-row input:focus,
.cosign-form-row select:focus,
.cosign-form-row textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(0, 0, 102, 0.1);
}

.cosign-form-row textarea {
    min-height: 120px;
    resize: vertical;
}

.cosign-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.cosign-btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--radius);
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.cosign-btn.primary {
    background: var(--secondary-color);
    color: white;
}

.cosign-btn.secondary {
    background: #6c757d;
    color: white;
}

.cosign-btn:hover {
    opacity: 0.9;
}

.required-field::after {
    content: " *";
    color: #ef4444;
}

.cosign-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border-color);
}

.message {
    padding: 1rem;
    border-radius: var(--radius);
    margin-bottom: 1.5rem;
    display: none;
}

.message.success {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #86efac;
    display: block;
}

.message.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
    display: block;
}
</style>

<div class="wrap cosign-wrap">
    <div class="cosign-header">
        <h1><i class="fas fa-project-diagram"></i> <?php echo $is_edit ? 'Edit Project' : 'Add New Project'; ?></h1>
        <a href="<?php echo admin_url('admin.php?page=cosign-pipeline&tab=projects'); ?>" class="cosign-btn secondary">
            <i class="fas fa-arrow-left"></i> Back to Pipeline
        </a>
    </div>

    <div class="cosign-form">
        <div id="message" class="message"></div>
        
        <form id="project-details-form">
            <?php wp_nonce_field('cosign_nonce', 'cosign_nonce'); ?>
            <input type="hidden" name="project_id" id="project-id" value="<?php echo $is_edit ? esc_attr($project_id) : ''; ?>">
            
            <h2 style="color: var(--primary-color); margin-bottom: 1.5rem; border-bottom: 2px solid var(--secondary-color); padding-bottom: 0.5rem;">
                Project Information <span class="required-field"></span>
            </h2>

            <div class="cosign-form-grid">
                <div class="cosign-form-row">
                    <label for="project-name" class="required-field">Project Name</label>
                    <input type="text" id="project-name" name="project_name" value="<?php echo $is_edit ? esc_attr($project->project_name ?? $project->name ?? '') : ''; ?>" required>
                </div>

                <div class="cosign-form-row">
                    <label for="client-id" class="required-field">Client</label>
                    <select id="client-id" name="client_id" required>
                        <option value="">Select Client</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?php echo esc_attr($client['id']); ?>" <?php selected($is_edit && ($project->client_id ?? 0) == $client['id']); ?>>
                                <?php echo esc_html($client['company_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cosign-form-row">
                    <label for="quotation-number">Quotation Number</label>
                    <input type="text" id="quotation-number" name="quotation_number" value="<?php echo $is_edit ? esc_attr($project->quotation_number ?? '') : ''; ?>">
                </div>

                <div class="cosign-form-row">
                    <label for="project-type">Project Type</label>
                    <select id="project-type" name="project_type">
                        <option value="">Select Project Type</option>
                        <option value="Quote" <?php selected($is_edit && ($project->project_type ?? '') == 'Quote'); ?>>Quote</option>
                        <option value="Invoice" <?php selected($is_edit && ($project->project_type ?? '') == 'Invoice'); ?>>Invoice</option>
                        <option value="Proposal" <?php selected($is_edit && ($project->project_type ?? '') == 'Proposal'); ?>>Proposal</option>
                        <option value="Estimate" <?php selected($is_edit && ($project->project_type ?? '') == 'Estimate'); ?>>Estimate</option>
                    </select>
                </div>

                <div class="cosign-form-row">
                    <label for="date-of-quotation">Date of Quotation</label>
                    <input type="date" id="date-of-quotation" name="date_of_quotation" value="<?php echo $is_edit && isset($project->date_of_quotation) ? esc_attr(date('Y-m-d', strtotime($project->date_of_quotation))) : ''; ?>">
                </div>

                <div class="cosign-form-row">
                    <label for="expected-value">Expected Value</label>
                    <input type="number" id="expected-value" name="expected_value" step="0.01" min="0" value="<?php echo $is_edit && isset($project->expected_value) ? esc_attr($project->expected_value) : ''; ?>">
                </div>

                <div class="cosign-form-row">
                    <label for="expected-closure-date">Expected Closure Date</label>
                    <input type="date" id="expected-closure-date" name="expected_closure_date" value="<?php echo $is_edit && isset($project->expected_closure_date) ? esc_attr(date('Y-m-d', strtotime($project->expected_closure_date))) : ''; ?>">
                </div>

                <div class="cosign-form-row">
                    <label for="stage">Stage</label>
                    <select id="stage" name="stage">
                        <option value="">Select Stage</option>
                        <option value="Lead" <?php selected($is_edit && ($project->stage ?? '') == 'Lead'); ?>>Lead</option>
                        <option value="Qualified" <?php selected($is_edit && ($project->stage ?? '') == 'Qualified'); ?>>Qualified</option>
                        <option value="Proposal" <?php selected($is_edit && ($project->stage ?? '') == 'Proposal'); ?>>Proposal</option>
                        <option value="Negotiation" <?php selected($is_edit && ($project->stage ?? '') == 'Negotiation'); ?>>Negotiation</option>
                        <option value="Won" <?php selected($is_edit && ($project->stage ?? '') == 'Won'); ?>>Won</option>
                        <option value="Lost" <?php selected($is_edit && ($project->stage ?? '') == 'Lost'); ?>>Lost</option>
                    </select>
                </div>

                <div class="cosign-form-row">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="draft" <?php selected($is_edit && ($project->status ?? 'draft') == 'draft'); ?>>Draft</option>
                        <option value="in-process" <?php selected($is_edit && ($project->status ?? '') == 'in-process'); ?>>In Process</option>
                        <option value="approved" <?php selected($is_edit && ($project->status ?? '') == 'approved'); ?>>Approved</option>
                        <option value="completed" <?php selected($is_edit && ($project->status ?? '') == 'completed'); ?>>Completed</option>
                    </select>
                </div>

                <div class="cosign-form-row">
                    <label for="generated-by">Generated By</label>
                    <select id="generated-by" name="generated_by">
                        <option value="">Select User</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo esc_attr($user['ID']); ?>" <?php selected($is_edit && ($project->generated_by ?? 0) == $user['ID']); ?>>
                                <?php echo esc_html($user['display_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cosign-form-row" style="grid-column: 1 / -1;">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes"><?php echo $is_edit ? esc_textarea($project->notes ?? '') : ''; ?></textarea>
                </div>
            </div>

            <div class="cosign-form-actions">
                <button type="button" class="cosign-btn secondary" onclick="window.location.href='<?php echo admin_url('admin.php?page=cosign-pipeline&tab=projects'); ?>'">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="cosign-btn primary">
                    <i class="fas fa-save"></i> <?php echo $is_edit ? 'Update' : 'Save'; ?> Project
                </button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#project-details-form').on('submit', function(e) {
        e.preventDefault();

        const formData = {
            project_id: $('#project-id').val(),
            project_name: $('#project-name').val(),
            client_id: $('#client-id').val(),
            quotation_number: $('#quotation-number').val(),
            project_type: $('#project-type').val(),
            date_of_quotation: $('#date-of-quotation').val(),
            expected_value: $('#expected-value').val(),
            expected_closure_date: $('#expected-closure-date').val(),
            stage: $('#stage').val(),
            status: $('#status').val(),
            generated_by: $('#generated-by').val(),
            notes: $('#notes').val()
        };

        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: cosignData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cosign_save_project',
                nonce: $('#cosign_nonce').val(),
                project_data: formData
            },
            success: function(response) {
                if (response.success) {
                    showMessage('success', response.data.message || 'Project saved successfully!');
                    setTimeout(function() {
                        window.location.href = '<?php echo admin_url('admin.php?page=cosign-pipeline&tab=projects'); ?>';
                    }, 1500);
                } else {
                    showMessage('error', response.data || 'Failed to save project. Please try again.');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                showMessage('error', 'An error occurred. Please try again.');
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    function showMessage(type, message) {
        const messageDiv = $('#message');
        messageDiv.removeClass('success error').addClass(type).text(message).show();
        setTimeout(function() {
            messageDiv.fadeOut();
        }, 5000);
    }
});
</script>