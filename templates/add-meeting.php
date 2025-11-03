<?php if (!defined('ABSPATH')) { exit; } 
$clients = \CoSignPlanner\cosign_get_clients_list(); 
?>
<style>
:root {
    --primary-color: #000066;
    --secondary-color: #92b92a;
    --text-color: #2c3e50;
    --light-gray: #f8f9fa;
    --border-color: #e2e8f0;
    --shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    --hover-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
    --radius: 8px;
}

.wrap.cosign-wrap {
    max-width: 1000px;
    margin: 2rem auto;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border-color);
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
.cosign-form-row input[type="date"],
.cosign-form-row input[type="time"],
.cosign-form-row input[type="datetime-local"],
.cosign-form-row input[type="email"],
.cosign-form-row input[type="url"],
.cosign-form-row input[type="tel"],
.cosign-form-row input[type="number"],
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

.cosign-btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--radius);
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    background: var(--secondary-color);
    color: white;
}

.cosign-btn.secondary {
    background: #dc3545;
    color: white;
}

.cosign-btn:hover {
    opacity: 0.9;
}

.action-button {
    padding: 8px 24px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.action-button:not(.secondary) {
    background: #92b92a;
    color: white;
    border: none;
}

.action-button.secondary {
    background: #dc3545;
    color: white;
    border: none;
}

.action-button:hover {
    opacity: 0.9;
}

input[readonly] {
    background-color: #f8f9fa;
    cursor: not-allowed;
}

.required-field::after {
    content: " *";
    color: #ef4444;
}

.cosign-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.radio-group {
    display: flex;
    gap: 1.5rem;
    margin-top: 0.5rem;
}

.radio-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.radio-text {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-color);
}

.radio-label input[type="radio"] {
    width: 1.2rem;
    height: 1.2rem;
    margin: 0;
    cursor: pointer;
}

.radio-label input[type="radio"]:checked + .radio-text {
    color: var(--primary-color);
    font-weight: 500;
}

.cosign-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
}

select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1em;
    padding-right: 2.5rem;
    appearance: none;
}

::placeholder {
    color: #94a3b8;
}

.search-container {
    position: relative;
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 0 0 var(--radius) var(--radius);
    box-shadow: var(--shadow);
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.search-results.active {
    display: block;
}

.search-result-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: var(--transition);
}

.search-result-item:hover {
    background-color: var(--light-gray);
}

.search-result-item:not(:last-child) {
    border-bottom: 1px solid var(--border-color);
}

.new-buttons-container {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.new-buttons-container .cosign-btn {
    flex: 1;
}

/* Modal Styles */
.cosign-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.cosign-modal-content {
    position: relative;
    background-color: #fff;
    margin: 2% auto;
    padding: 20px;
    width: 80%;
    max-width: 1000px;
    max-height: 90vh;
    overflow-y: auto;
    border-radius: 8px;
}

.cosign-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e2e8f0;
}

.cosign-modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: #2d3748;
}

.required-note {
    font-size: 0.875rem;
    color: #718096;
    font-weight: normal;
    margin-left: 10px;
}

.close-modal {
    font-size: 1.875rem;
    font-weight: bold;
    color: #718096;
    cursor: pointer;
}

.close-modal:hover {
    color: #2d3748;
}

.address-section,
.stakeholder-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.address-section h3,
.stakeholder-section h3 {
    margin-bottom: 15px;
    color: #2d3748;
}

.add-button {
    padding: 4px 12px;
    margin-left: 10px;
    background-color: #92b92a;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.add-button:hover {
    background-color: #7a9c23;
}

@media (max-width: 768px) {
    .wrap.cosign-wrap {
        margin: 1rem;
    }

    .cosign-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
        padding: 1.25rem;
    }

    .cosign-form {
        padding: 1.5rem;
    }

    .cosign-form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .radio-group {
        flex-direction: column;
        gap: 1rem;
    }

    .cosign-form-actions {
        flex-direction: column;
    }

    .cosign-btn {
        width: 100%;
        justify-content: center;
        margin-bottom: 0.5rem;
    }
}
</style>

<div class="wrap cosign-wrap">
    <div class="cosign-header">
        <h1>
            <i class="fas fa-calendar-plus"></i>
            <?php echo esc_html(get_admin_page_title()); ?>
        </h1>
        <a href="<?php echo esc_url(admin_url('admin.php?page=cosign-calendar')); ?>" class="cosign-btn secondary">
            <i class="fas fa-arrow-left"></i> Back to Calendar
        </a>
    </div>

    <form id="add-meeting-form" class="cosign-form">
        <?php wp_nonce_field('cosign_nonce', 'cosign_nonce'); ?>

        <div class="cosign-form-row">
            <label for="event-type" class="required-field">Select Events Type</label>
            <select id="event-type" name="event_type" required>
                <option value="Follow Up Phone Call">Follow Up Phone Call</option>
                <option value="Client Meeting">Client Meeting</option>
                <option value="Team Meeting">Team Meeting</option>
                <option value="Site Visit">Site Visit</option>
            </select>
        </div>

        <div class="cosign-form-grid">
            <div class="cosign-form-row">
                <label for="date" class="required-field">Date</label>
                <input type="date" id="date" name="date" required>
            </div>
            <div class="cosign-form-row">
                <label for="time" class="required-field">Time</label>
                <input type="time" id="time" name="time" required>
            </div>
        </div>

        <div class="cosign-form-row">
            <label for="reminder" class="required-field">Reminder</label>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="reminder" value="yes">
                    <span class="radio-text">Yes</span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="reminder" value="no" checked>
                    <span class="radio-text">No</span>
                </label>
            </div>
        </div>

        <div class="cosign-form-row">
            <label class="required-field">Client Type</label>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="client_type" value="existing" checked>
                    <span class="radio-text">Existing Client/Project</span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="client_type" value="new">
                    <span class="radio-text">Add New Lead/Project</span>
                </label>
            </div>
        </div>

        <div class="cosign-form-row" id="new-buttons-section" style="display: none;">
            <div class="new-buttons-container">
                <button type="button" class="cosign-btn" onclick="openNewLeadForm()">
                    <i class="fas fa-user-plus"></i> Add New Lead
                </button>
                <button type="button" class="cosign-btn" onclick="openNewProjectForm()">
                    <i class="fas fa-project-diagram"></i> Add New Project
                </button>
            </div>
        </div>

        <div class="cosign-form-row" id="client-search-section">
            <label for="client-search" class="required-field">Search Client</label>
            <div class="search-container">
                <input type="text" id="client-search" name="search_client" placeholder="Search Client" autocomplete="off">
                <input type="hidden" id="selected-client-id" name="client_id">
                <div id="search-results" class="search-results"></div>
            </div>
        </div>

        <div class="cosign-form-grid" id="client-details" style="display: none;">
            <div class="cosign-form-row">
                <label>Client Name</label>
                <input type="text" id="client-name" readonly>
            </div>
            <div class="cosign-form-row">
                <label>Client Type</label>
                <input type="text" id="client-type" readonly>
            </div>
            <div class="cosign-form-row">
                <label>Client Mobile</label>
                <input type="text" id="client-mobile" readonly>
            </div>
            <div class="cosign-form-row">
                <label>Client Email</label>
                <input type="text" id="client-email" readonly>
            </div>
        </div>

        <div class="cosign-form-row">
            <label for="task-name" class="required-field">Task Name</label>
            <input type="text" id="task-name" name="task_name" value="Follow Up Phone Call" required>
        </div>

        <div class="cosign-form-row">
            <label for="task-description" class="required-field">Task Description</label>
            <textarea id="task-description" name="task_description" rows="4" required></textarea>
        </div>

        <div class="cosign-form-grid">
            <div class="cosign-form-row">
                <label for="priority" class="required-field">Priority</label>
                <select id="priority" name="priority" required>
                    <option value="">--Please Select--</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="cosign-form-row">
                <label for="deadline" class="required-field">Deadline</label>
                <input type="datetime-local" id="deadline" name="deadline" required>
            </div>
        </div>

        <div class="cosign-form-actions">
            <button type="submit" class="cosign-btn">
                <i class="fas fa-check"></i> Submit
            </button>
            <button type="button" class="cosign-btn secondary" onclick="window.history.back()">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </form>
</div>

<!-- Lead Modal -->
<div id="leadModal" class="cosign-modal">
    <div class="cosign-modal-content">
        <div class="cosign-modal-header">
            <h2>Lead Details <span class="required-note">(Compulsory fields are marked with red*)</span></h2>
            <span class="close-modal" onclick="closeModal('leadModal')">&times;</span>
        </div>
        <form id="leadForm" class="cosign-form">
            <div class="cosign-form-grid">
                <div class="cosign-form-row">
                    <label for="lead_generated_by" class="required-field">Lead Generated By</label>
                    <select id="lead_generated_by" name="lead_generated_by" required>
                        <option value="">Select Lead Generated By</option>
                    </select>
                </div>
                <div class="cosign-form-row">
                    <label for="company_name" class="required-field">Company Name</label>
                    <input type="text" id="company_name" name="company_name" placeholder="Client Name" required>
                </div>
                <div class="cosign-form-row">
                    <label for="lead_type" class="required-field">Type</label>
                    <select id="lead_type" name="lead_type" required>
                        <option value="">Select Client Type</option>
                    </select>
                </div>
                <div class="cosign-form-row">
                    <label for="lead_email" class="required-field">Email</label>
                    <input type="email" id="lead_email" name="lead_email" placeholder="Email" required>
                </div>
                <div class="cosign-form-row">
                    <label for="lead_status" class="required-field">Status</label>
                    <select id="lead_status" name="lead_status" required>
                        <option value="">Select Status</option>
                    </select>
                </div>
                <div class="cosign-form-row">
                    <label for="lead_website">Website</label>
                    <input type="url" id="lead_website" name="lead_website" placeholder="Website">
                </div>
                <div class="cosign-form-row">
                    <label for="assign_to">Assign to</label>
                    <select id="assign_to" name="assign_to">
                        <option value="">Select Sales Person Name</option>
                    </select>
                </div>
            </div>

            <div class="address-section">
                <h3>Address:</h3>
                <div class="cosign-form-grid">
                    <div class="cosign-form-row">
                        <label for="country">Country</label>
                        <select id="country" name="country">
                            <option value="India">India</option>
                        </select>
                    </div>
                    <div class="cosign-form-row">
                        <label for="zone" class="required-field">Zone</label>
                        <select id="zone" name="zone" required>
                            <option value="">Select Zone</option>
                        </select>
                    </div>
                    <div class="cosign-form-row">
                        <label for="state" class="required-field">State</label>
                        <select id="state" name="state" required>
                            <option value="">Select State</option>
                        </select>
                    </div>
                    <div class="cosign-form-row">
                        <label for="city" class="required-field">City</label>
                        <select id="city" name="city" required>
                            <option value="">Select City</option>
                        </select>
                    </div>
                </div>
                <div class="cosign-form-row">
                    <label for="address" class="required-field">Address</label>
                    <input type="text" id="address" name="address" placeholder="Address" required>
                </div>
                <div class="cosign-form-row">
                    <label for="zip_code">Zip Code</label>
                    <input type="text" id="zip_code" name="zip_code" placeholder="Zip Code">
                </div>
            </div>

            <div class="cosign-form-actions">
                <button type="submit" class="cosign-btn action-button">Submit</button>
                <button type="button" class="cosign-btn action-button" onclick="resetForm('leadForm')">Reset</button>
                <button type="button" class="cosign-btn action-button secondary" onclick="closeModal('leadModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Project Modal -->
<div id="projectModal" class="cosign-modal">
    <div class="cosign-modal-content">
        <div class="cosign-modal-header">
            <h2>Project Details <span class="required-note">(Compulsory fields are marked with red*)</span></h2>
            <span class="close-modal" onclick="closeModal('projectModal')">&times;</span>
        </div>
        <form id="projectForm" class="cosign-form">
            <div class="cosign-form-grid">
                <div class="cosign-form-row">
                    <label for="project_generated_by" class="required-field">Project Generated By</label>
                    <select id="project_generated_by" name="project_generated_by" required>
                        <option value="">Select Project Generated By</option>
                    </select>
                </div>
                <div class="cosign-form-row">
                    <label for="project_name" class="required-field">Project Name</label>
                    <input type="text" id="project_name" name="project_name" placeholder="Project Name" required>
                </div>
                <div class="cosign-form-row">
                    <label for="project_email">Email</label>
                    <input type="email" id="project_email" name="project_email" placeholder="Email">
                </div>
                <div class="cosign-form-row">
                    <label for="project_status" class="required-field">Status</label>
                    <select id="project_status" name="project_status" required>
                        <option value="">Select Status</option>
                    </select>
                </div>
                <div class="cosign-form-row">
                    <label for="project_website">Website</label>
                    <input type="url" id="project_website" name="project_website" placeholder="Website">
                </div>
                <div class="cosign-form-row">
                    <label for="project_assign_to">Assign to</label>
                    <select id="project_assign_to" name="project_assign_to">
                        <option value="">Select Sales Person Name</option>
                    </select>
                </div>
                <div class="cosign-form-row">
                    <label for="contact_person" class="required-field">Contact Person</label>
                    <input type="text" id="contact_person" name="contact_person" placeholder="Contact Person" required>
                </div>
                <div class="cosign-form-row">
                    <label for="designation">Designation</label>
                    <input type="text" id="designation" name="designation" placeholder="Designation">
                </div>
                <div class="cosign-form-row">
                    <label for="contact_number" class="required-field">Contact Number</label>
                    <input type="tel" id="contact_number" name="contact_number" placeholder="Contact Number" required>
                </div>
            </div>

            <div class="address-section">
                <h3>Address:</h3>
                <div class="cosign-form-grid">
                    <div class="cosign-form-row">
                        <label for="project_country">Country</label>
                        <select id="project_country" name="project_country">
                            <option value="India">India</option>
                        </select>
                    </div>
                    <div class="cosign-form-row">
                        <label for="project_zone" class="required-field">Zone</label>
                        <select id="project_zone" name="project_zone" required>
                            <option value="">Select Zone</option>
                        </select>
                    </div>
                    <div class="cosign-form-row">
                        <label for="project_state" class="required-field">State</label>
                        <select id="project_state" name="project_state" required>
                            <option value="">Select State</option>
                        </select>
                    </div>
                    <div class="cosign-form-row">
                        <label for="project_city" class="required-field">City</label>
                        <select id="project_city" name="project_city" required>
                            <option value="">Select City</option>
                        </select>
                    </div>
                </div>
                <div class="cosign-form-row">
                    <label for="project_address" class="required-field">Address</label>
                    <input type="text" id="project_address" name="project_address" placeholder="Address" required>
                </div>
                <div class="cosign-form-row">
                    <label for="project_zip_code">Zip Code</label>
                    <input type="text" id="project_zip_code" name="project_zip_code" placeholder="Zip Code">
                </div>
            </div>

            <div class="cosign-form-grid">
                <div class="cosign-form-row">
                    <label for="expected_value">Expected Project Value</label>
                    <input type="number" id="expected_value" name="expected_value" placeholder="Expected Project Value">
                </div>
                <div class="cosign-form-row">
                    <label for="expected_closure">Expected Date of Closure</label>
                    <input type="date" id="expected_closure" name="expected_closure" placeholder="dd-mm-yyyy">
                </div>
            </div>

            <div class="stakeholder-section">
                <h3>Add Stakeholders:</h3>
                <div class="cosign-form-row">
                    <label for="stakeholder_type">Stakeholder Type</label>
                    <select id="stakeholder_type" name="stakeholder_type">
                        <option value="">Stakeholder Type</option>
                    </select>
                    <button type="button" class="cosign-btn add-button" onclick="addStakeholder()">+</button>
                </div>
            </div>

            <div class="cosign-form-actions">
                <button type="submit" class="cosign-btn action-button">Submit</button>
                <button type="button" class="cosign-btn action-button" onclick="resetForm('projectForm')">Reset</button>
                <button type="button" class="cosign-btn action-button secondary" onclick="closeModal('projectModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Client type radio button handler
    $('input[name="client_type"]').on('change', function() {
        if ($(this).val() === 'new') {
            $('#client-search-section').hide();
            $('#client-details').hide();
            $('#new-buttons-section').show();
            // Clear client details
            $('#client-name, #client-type, #client-mobile, #client-email').val('');
            $('#selected-client-id').val('');
            $('#client-search').val('');
        } else {
            $('#client-search-section').show();
            $('#new-buttons-section').hide();
        }
    });

    // Real-time search functionality
    let searchTimeout;
    $('#client-search').on('input', function() {
        const searchTerm = $(this).val();
        clearTimeout(searchTimeout);
        const $searchResults = $('#search-results');

        if (searchTerm.length < 2) {
            $searchResults.removeClass('active').empty();
            return;
        }

        searchTimeout = setTimeout(() => {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'search_clients',
                    nonce: $('#cosign_nonce').val(),
                    search: searchTerm
                },
                success: function(response) {
                    if (response.success) {
                        displaySearchResults(response.data);
                    }
                }
            });
        }, 300);
    });

    // Display search results
    function displaySearchResults(results) {
        const $searchResults = $('#search-results');
        $searchResults.empty();

        if (results.length === 0) {
            $searchResults.append('<div class="search-result-item">No clients found</div>');
        } else {
            results.forEach(client => {
                $searchResults.append(
                    `<div class="search-result-item" data-id="${client.id}">
                        ${client.company_name} <small style="color: #666;">${client.type || ''}</small>
                    </div>`
                );
            });
        }
        $searchResults.addClass('active');
    }

    // Handle search result selection
    $(document).on('click', '.search-result-item', function() {
        const clientId = $(this).data('id');
        
        if (!clientId) return;
        
        const clientName = $(this).clone().children().remove().end().text().trim();
        $('#client-search').val(clientName);
        $('#selected-client-id').val(clientId);
        $('#search-results').removeClass('active');

        // Fetch client details
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_client_details',
                nonce: $('#cosign_nonce').val(),
                client_id: clientId
            },
            success: function(response) {
                if (response.success) {
                    const client = response.data;
                    $('#client-name').val(client.company_name || '');
                    $('#client-type').val(client.client_type || '');
                    $('#client-mobile').val(client.mobile || '');
                    $('#client-email').val(client.email || '');
                    $('#client-details').slideDown();
                }
            }
        });
    });

    // Close search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-container').length) {
            $('#search-results').removeClass('active');
        }
    });

    // Form submission
    $('#add-meeting-form').on('submit', function(e) {
        e.preventDefault();
        
        var date = $('#date').val();
        var time = $('#time').val();
        
        // Combine date and time for start_time
        var startTime = new Date(date + 'T' + time);
        
        // Set end time to 1 hour after start by default
        var endTime = new Date(startTime.getTime() + (60 * 60 * 1000));

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cosign_add_meeting',
                nonce: $('#cosign_nonce').val(),
                event_type: $('#event-type').val(),
                date: $('#date').val(),
                time: $('#time').val(),
                reminder: $('input[name="reminder"]:checked').val(),
                client_type: $('input[name="client_type"]:checked').val(),
                client_id: $('#selected-client-id').val(),
                task_name: $('#task-name').val(),
                task_description: $('#task-description').val(),
                priority: $('#priority').val(),
                deadline: $('#deadline').val(),
                start_time: startTime.toISOString(),
                end_time: endTime.toISOString()
            },
            success: function(response) {
                if (response.success) {
                    alert('Meeting added successfully!');
                    window.location.href = '<?php echo esc_url(admin_url('admin.php?page=cosign-calendar')); ?>';
                } else {
                    alert(response.data || 'Failed to add meeting. Please try again.');
                }
            },
            error: function() {
                alert('Failed to add meeting. Please try again.');
            }
        });
    });

    // Check for returned meeting data
    const pendingMeetingData = sessionStorage.getItem('pending_meeting_data');
    if (pendingMeetingData) {
        const data = JSON.parse(pendingMeetingData);
        $('#event-type').val(data.event_type);
        $('#date').val(data.date);
        $('#time').val(data.time);
        $('#task-name').val(data.task_name);
        $('#task-description').val(data.task_description);
        sessionStorage.removeItem('pending_meeting_data');
    }
});

// Global functions for modal handling
function openNewLeadForm() {
    // Store the current meeting form data in session storage
    const meetingFormData = {
        event_type: jQuery('#event-type').val(),
        date: jQuery('#date').val(),
        time: jQuery('#time').val(),
        task_name: jQuery('#task-name').val(),
        task_description: jQuery('#task-description').val()
    };
    sessionStorage.setItem('pending_meeting_data', JSON.stringify(meetingFormData));
    
    // Show the lead modal
    jQuery('#leadModal').fadeIn();
}

function openNewProjectForm() {
    // Store the current meeting form data in session storage
    const meetingFormData = {
        event_type: jQuery('#event-type').val(),
        date: jQuery('#date').val(),
        time: jQuery('#time').val(),
        task_name: jQuery('#task-name').val(),
        task_description: jQuery('#task-description').val()
    };
    sessionStorage.setItem('pending_meeting_data', JSON.stringify(meetingFormData));
    
    // Show the project modal
    jQuery('#projectModal').fadeIn();
}

function closeModal(modalId) {
    jQuery('#' + modalId).fadeOut();
}

function resetForm(formId) {
    jQuery('#' + formId)[0].reset();
}

function addStakeholder() {
    // Implement stakeholder addition logic here
    alert('Stakeholder addition functionality to be implemented');
}

// Close modal when clicking outside
jQuery(document).on('click', '.cosign-modal', function(e) {
    if (jQuery(e.target).hasClass('cosign-modal')) {
        jQuery(this).fadeOut();
    }
});
</script>