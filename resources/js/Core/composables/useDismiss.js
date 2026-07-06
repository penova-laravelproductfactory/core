import { ref } from 'vue';

// Guarded, dismiss-once flag backed by localStorage. Degrades gracefully to
// "always visible, never remembered" if storage is unavailable (private mode,
// blocked cookies) instead of throwing and blanking the page.
export function useDismiss(key) {
    const read = () => {
        try {
            return localStorage.getItem(key) === '1';
        } catch {
            return false;
        }
    };

    const dismissed = ref(read());

    const dismiss = () => {
        try {
            localStorage.setItem(key, '1');
        } catch {
            /* ignore */
        }
        dismissed.value = true;
    };

    return { dismissed, dismiss };
}
