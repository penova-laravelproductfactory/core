<?php

namespace App\Core\Support;

/**
 * The locale-metadata seam (RFC-005 / D-027).
 *
 * Core's i18n consumes exactly ONE piece of standardized locale metadata:
 * text direction. This is deliberately the whole seam — any additional
 * locale metadata (numeral system, calendar, formatting) is regional policy
 * and stays outside Core unless separate proof of necessity clears governance
 * (D-027). Core owns the capability to operate in any locale; it owns no
 * particular locale.
 */
class Locale
{
    /** Locales Core renders right-to-left. */
    private const RTL = ['fa', 'ar', 'he', 'ur'];

    /**
     * Text direction ('ltr' | 'rtl') for a locale — the only locale metadata
     * Core standardizes.
     */
    public static function direction(string $locale): string
    {
        return in_array($locale, self::RTL, true) ? 'rtl' : 'ltr';
    }
}
