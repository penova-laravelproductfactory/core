import { usePage } from '@inertiajs/vue3';

/**
 * Build a Workspace-relative URL from the configured Workspace prefix
 * (shared as the `workspace.prefix` Inertia prop) instead of hardcoding
 * "/admin". Honours PENOVA_WORKSPACE_PREFIX end to end (RFC-002 / D-024).
 *
 *   const ws = useWorkspacePath();
 *   ws('/users')              → "/workspace/users"
 *   ws(`/users/${id}/edit`)   → "/workspace/users/42/edit"
 *   ws()                      → "/workspace"
 */
export function useWorkspacePath() {
    const page = usePage();

    return (path = '') => `/${page.props.workspace.prefix}${path}`;
}
