<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $wpdb;
$table_name = $wpdb->prefix . 'products';

// Get categories for dropdown
$categories = $wpdb->get_results("SELECT DISTINCT category FROM $table_name ORDER BY category");

// Get price lists for dropdown
$price_lists = ['Standard', 'Wholesale', 'Dealer', 'Custom'];

// Get selected filters
$selected_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : 'MPO';
$selected_price_list = isset($_GET['price_list']) ? sanitize_text_field($_GET['price_list']) : 'Standard';

// Fetch products based on filters
$products = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_name WHERE category = %s ORDER BY product_code",
    $selected_category
));
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="price-list-controls">
        <form method="get" action="" class="price-list-filters">
            <input type="hidden" name="page" value="cosign-price-list">
            
            <select name="category" id="category-filter">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo esc_attr($cat->category); ?>" 
                            <?php selected($selected_category, $cat->category); ?>>
                        <?php echo esc_html($cat->category); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="price_list" id="price-list-filter">
                <?php foreach ($price_lists as $list): ?>
                    <option value="<?php echo esc_attr($list); ?>" 
                            <?php selected($selected_price_list, $list); ?>>
                        <?php echo esc_html($list); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="button">Apply Filters</button>
            <a href="#" class="button button-primary" id="download-pdf">Download PDF</a>
        </form>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column">Sr. No</th>
                <th scope="col" class="manage-column">Product Code</th>
                <th scope="col" class="manage-column">HSN Code</th>
                <th scope="col" class="manage-column">Product Description</th>
                <th scope="col" class="manage-column">Price</th>
                <th scope="col" class="manage-column">Unit</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php $counter = 1; ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo esc_html($counter++); ?></td>
                        <td><?php echo esc_html($product->product_code); ?></td>
                        <td><?php echo esc_html($product->hsn_code); ?></td>
                        <td><?php echo esc_html($product->description); ?></td>
                        <td><?php echo esc_html($product->price) . ' Rs.'; ?></td>
                        <td><?php echo esc_html($product->unit); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No products found for the selected category.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th scope="col" class="manage-column">Sr. No</th>
                <th scope="col" class="manage-column">Product Code</th>
                <th scope="col" class="manage-column">HSN Code</th>
                <th scope="col" class="manage-column">Product Description</th>
                <th scope="col" class="manage-column">Price</th>
                <th scope="col" class="manage-column">Unit</th>
            </tr>
        </tfoot>
    </table>

    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="pagination-links">
                <a class="first-page button" href="#"><span class="screen-reader-text">First page</span><span aria-hidden="true">«</span></a>
                <a class="prev-page button" href="#"><span class="screen-reader-text">Previous page</span><span aria-hidden="true">‹</span></a>
                <span class="paging-input">
                    <label for="current-page-selector" class="screen-reader-text">Current Page</label>
                    <input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging">
                    <span class="tablenav-paging-text"> of <span class="total-pages">2</span></span>
                </span>
                <a class="next-page button" href="#"><span class="screen-reader-text">Next page</span><span aria-hidden="true">›</span></a>
                <a class="last-page button" href="#"><span class="screen-reader-text">Last page</span><span aria-hidden="true">»</span></a>
            </span>
        </div>
    </div>

    <div class="card">
        <h2><?php _e('Price List Archive', 'cosign-planner'); ?></h2>
        <p><?php _e('Historical pricing data will remain accessible here during the transition period. The new system offers improved features including:', 'cosign-planner'); ?></p>
        
        <ul class="feature-list">
            <li><?php _e('✓ Dynamic pricing calculations', 'cosign-planner'); ?></li>
            <li><?php _e('✓ Product categories and variations', 'cosign-planner'); ?></li>
            <li><?php _e('✓ Bulk price updates', 'cosign-planner'); ?></li>
            <li><?php _e('✓ Price history tracking', 'cosign-planner'); ?></li>
            <li><?php _e('✓ Custom price lists per client', 'cosign-planner'); ?></li>
        </ul>
    </div>

    <div class="card migration-info">
        <h3><?php _e('Migration Information', 'cosign-planner'); ?></h3>
        <p><?php _e('Your existing price lists will be automatically migrated to the new system. During migration:', 'cosign-planner'); ?></p>
        
        <ol>
            <li><?php _e('All current prices will be preserved', 'cosign-planner'); ?></li>
            <li><?php _e('Historical pricing data will be maintained', 'cosign-planner'); ?></li>
            <li><?php _e('Custom client pricing will be transferred', 'cosign-planner'); ?></li>
        </ol>
        
        <p class="migration-note">
            <?php _e('Need help with migration or have questions? Contact our support team.', 'cosign-planner'); ?>
        </p>
    </div>
</div>

<style>
.feature-list {
    list-style: none;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 4px;
    margin: 15px 0;
}

.feature-list li {
    margin: 10px 0;
    color: #2271b1;
    font-size: 14px;
}

.price-list-controls {
    margin: 20px 0;
    padding: 15px;
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    border-radius: 4px;
}

.price-list-filters {
    display: flex;
    gap: 10px;
    align-items: center;
}

.price-list-filters select {
    min-width: 200px;
    height: 35px;
}

.price-list-filters .button {
    height: 35px;
    line-height: 33px;
}

/* Table styles */
.wp-list-table {
    margin-top: 20px;
}

.wp-list-table th {
    font-weight: 600;
}

.wp-list-table td, 
.wp-list-table th {
    padding: 12px 10px;
}

.wp-list-table .price-column {
    text-align: right;
}

/* Pagination styles */
.tablenav-pages {
    float: right;
    margin: 20px 0;
}

.pagination-links .button {
    padding: 0 10px;
}

.current-page {
    width: 50px;
    text-align: center;
}

.tablenav-paging-text {
    vertical-align: middle;
}

/* Responsive adjustments */
@media screen and (max-width: 782px) {
    .price-list-filters {
        flex-direction: column;
        gap: 15px;
    }
    
    .price-list-filters select,
    .price-list-filters .button {
        width: 100%;
    }
}
</style>