<?php

namespace GeminiLabs\SiteReviews\Controllers\ListTableColumns;

use GeminiLabs\SiteReviews\Modules\Html\Builder;
use GeminiLabs\SiteReviews\Review;

class ColumnValuePinned implements ColumnValue
{
    /**
     * {@inheritdoc}
     */
    public function handle(Review $review)
    {
        $pinned = $review->is_pinned ? 'pinned ' : '';
        if (glsr()->can('edit_others_posts')) {
            $pinned .= 'pin-review ';
        }
        return glsr(Builder::class)->i([
            'class' => $pinned.'dashicons dashicons-sticky',
            'data-id' => $review->ID,
        ]);
    }
}
