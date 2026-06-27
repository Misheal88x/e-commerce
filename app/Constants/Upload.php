<?php

declare(strict_types=1);

namespace App\Constants;

final class Upload
{
    const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    const MAX_SIZE_BYTES      = 5 * 1024 * 1024;

    const DIR_PRODUCTS   = 'products';
    const DIR_CATEGORIES = 'categories';
    const DIR_BRANDS     = 'brands';
    const DIR_PROFILES   = 'profiles';
    const DIR_SLIDERS    = 'sliders';
}
