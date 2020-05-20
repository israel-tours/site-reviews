<?php

namespace GeminiLabs\SiteReviews\Modules\Html\Tags;

class RatingTag extends Tag
{
    /**
     * {@inheritdoc}
     */
    public function handle($value)
    {
        if (!$this->isHidden()) {
            return glsr_star_rating($value);
        }
    }
}