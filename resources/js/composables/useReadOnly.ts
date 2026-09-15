import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useReadOnly() {
    const page = usePage();

    const isReadOnly = computed(() => {
        const isGhostMode = page.props.auth?.isGhostMode;
        const isSuspended = page.props.auth?.activeWorkspace?.is_suspended;

        return isGhostMode || isSuspended;
    });

    return { isReadOnly };
}
