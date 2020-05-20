<?php

namespace GeminiLabs\SiteReviews\Modules\Html\Tags;

use GeminiLabs\SiteReviews\Helpers\Arr;
use GeminiLabs\SiteReviews\Helpers\Str;
use GeminiLabs\SiteReviews\Modules\Html\Builder;
use GeminiLabs\SiteReviews\Modules\Multilingual;

class AssignedToTag extends Tag
{
    /**
     * {@inheritdoc}
     */
    public function handle($value)
    {
        if ($this->isHidden('reviews.assigned_links')) {
            return;
        }
        $links = [];
        foreach (Arr::consolidate($value) as $postId) {
            $post = get_post(glsr(Multilingual::class)->getPostId($postId));
            if (!empty($post->ID)) {
                $links[] = glsr(Builder::class)->a([
                    'href' => get_the_permalink($post->ID),
                    'text' => get_the_title($post->ID),
                ]);
            }
        }
        if (!empty($links)) {
            return sprintf(__('Review of %s', 'site-reviews'), Str::naturalJoin($links));
        }
    }
}