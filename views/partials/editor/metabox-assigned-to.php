<?php defined('WPINC') || die; ?>

<div class="glsr-search-box" id="glsr-search-posts">
    <span class="glsr-spinner"><span class="spinner"></span></span>
    <input type="search" class="glsr-search-input" autocomplete="off" placeholder="<?= esc_attr_x('Type to search...', 'admin-text', 'site-reviews'); ?>">
    <?php wp_nonce_field('search-posts', '_search_nonce', false); ?>
    <span class="glsr-search-results"></span>
    <p><?= _x('Search here for a page or post that you would like to assign this review to. You may search by title or ID.', 'admin-text', 'site-reviews'); ?></p>
    <span class="description"><?= $templates; ?></span>
</div>

<script type="text/html" id="tmpl-glsr-assigned-post">
<?php include glsr()->path('views/partials/editor/assigned-post.php'); ?>
</script>
