import { usePage } from '@inertiajs/vue3';

/**
 * Minimal i18n bridge (RFC-005 / D-027).
 *
 * Laravel PHP catalogs (lang/en, lang/fa) are the single source of truth;
 * HandleInertiaRequests shares the active locale's `ui` messages with the
 * English base already merged under them, so a missing translation falls
 * back to English. This composable only looks a dot-path key up in that
 * payload and interpolates `:param` placeholders — no front-end i18n
 * framework, no second translation authority.
 */
export function useI18n() {
    const page = usePage();

    const t = (key, params = {}) => {
        const found = key
            .split('.')
            .reduce((node, part) => (node == null ? undefined : node[part]), page.props.messages);

        // A missing key degrades to the key itself (visible, never blank).
        let out = typeof found === 'string' ? found : key;

        for (const [name, value] of Object.entries(params)) {
            out = out.replace(new RegExp(`:${name}(?![A-Za-z0-9_])`, 'g'), String(value));
        }

        return out;
    };

    return { t };
}
