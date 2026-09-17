<script setup lang="ts">
import { X } from '@lucide/vue';
import { onClickOutside } from '@vueuse/core';
import { ref, onMounted, computed } from 'vue';
import { Calendar } from '@/components/ui/calendar';

type FilterOption = {
    key: string;
    label: string;
    options?: string[];
    type?: 'text' | 'date';
};

const props = defineProps<{
    modelValue: string;
    filters?: FilterOption[];
}>();

const emit = defineEmits(['update:modelValue', 'search']);

const containerRef = ref<HTMLElement | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);

const tokens = ref<{ key: string; value: string }[]>([]);
const rawInput = ref('');
const isPopoverOpen = ref(false);

onClickOutside(containerRef, () => {
    isPopoverOpen.value = false;
});

const handleWrapperClick = () => {
    isPopoverOpen.value = true;
    searchInput.value?.focus();
};

const parseInitial = (val: string) => {
    const regex = /(\w+):(".*?"|\S+)/g;
    let match;
    let remaining = val;
    tokens.value = [];

    while ((match = regex.exec(val)) !== null) {
        tokens.value.push({ key: match[1], value: match[2].replace(/"/g, '') });
        remaining = remaining.replace(match[0], '');
    }

    rawInput.value = remaining.trim();
};

onMounted(() => {
    if (props.modelValue) {
parseInitial(props.modelValue);
}
});

const updateParent = () => {
    const tokenString = tokens.value.map(t => `${t.key}:"${t.value}"`).join(' ');
    const fullString = `${tokenString} ${rawInput.value}`.trim();
    emit('update:modelValue', fullString);
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === ' ' || e.key === 'Enter') {
        const regex = /(?:^|\s)(\w+):([^\s]*)$/;
        const match = rawInput.value.match(regex);

        if (match) {
            e.preventDefault();
            tokens.value.push({ key: match[1], value: match[2].replace(/"/g, '') });
            rawInput.value = rawInput.value.replace(regex, '').trim();

            if (rawInput.value) {
rawInput.value += ' ';
}

            updateParent();

            if (e.key === 'Enter') {
                emit('search');
                isPopoverOpen.value = false;
            }
        } else if (e.key === 'Enter') {
            updateParent();
            emit('search');
            isPopoverOpen.value = false;
        }
    } else if (e.key === 'Backspace' && rawInput.value === '') {
        if (tokens.value.length > 0) {
            tokens.value.pop();
            updateParent();
            emit('search');
        }
    }
};

const removeToken = (index: number) => {
    tokens.value.splice(index, 1);
    updateParent();
    searchInput.value?.focus();
    emit('search');
};

const activeFilterContext = computed(() => {
    const match = rawInput.value.match(/(?:^|\s)(\w+):([^\s]*)$/);

    if (match && props.filters) {
        return props.filters.find(f => f.key === match[1].toLowerCase()) || null;
    }

    return null;
});

const filteredOptions = computed(() => {
    if (!activeFilterContext.value?.options) {
return [];
}

    const match = rawInput.value.match(/(?:^|\s)(\w+):([^\s]*)$/);
    const search = match ? match[2].toLowerCase() : '';

    if (!search) {
return activeFilterContext.value.options;
}

    return activeFilterContext.value.options.filter(opt => opt.toLowerCase().includes(search));
});

const selectKey = (key: string) => {
    rawInput.value = rawInput.value.trim() + (rawInput.value ? ' ' : '') + `${key}:`;
    searchInput.value?.focus();
};

const selectValue = (key: string, value: string) => {
    tokens.value.push({ key, value });
    rawInput.value = rawInput.value.replace(/(?:^|\s)(\w+):([^\s]*)$/, '').trim();

    if (rawInput.value) {
rawInput.value += ' ';
}

    updateParent();
    isPopoverOpen.value = false;
    searchInput.value?.focus();
    emit('search');
};

const selectDateValue = (val: any) => {
    if (!val || !activeFilterContext.value) {
return;
}

    let formattedDate = '';

    if (val.year && val.month && val.day) {
        formattedDate = `${val.year}-${String(val.month).padStart(2, '0')}-${String(val.day).padStart(2, '0')}`;
    } else {
        formattedDate = val.toString();
    }

    tokens.value.push({ key: activeFilterContext.value.key, value: formattedDate });
    rawInput.value = rawInput.value.replace(/(?:^|\s)(\w+):([^\s]*)$/, '').trim();

    if (rawInput.value) {
rawInput.value += ' ';
}

    updateParent();
    isPopoverOpen.value = false;
    searchInput.value?.focus();
    emit('search');
};
</script>

<template>
    <div ref="containerRef" class="relative w-full text-left">

        <div class="flex min-h-9 w-full flex-wrap items-center gap-1.5 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-within:ring-1 focus-within:ring-ring cursor-text transition-all"
            @click="handleWrapperClick">
            <span v-for="(token, index) in tokens" :key="index"
                class="flex items-center gap-1 rounded bg-secondary px-1.5 py-0.5 text-xs font-medium text-secondary-foreground">
                <span class="font-bold">{{ token.key }}:</span> {{ token.value }}
                <button @click.stop="removeToken(index)" class="ml-1 rounded-full hover:bg-muted focus:outline-none">
                    <X class="h-3 w-3" />
                </button>
            </span>

            <input ref="searchInput" v-model="rawInput" @keydown="handleKeydown" @input="updateParent"
                @focus="isPopoverOpen = true" type="text"
                class="flex-1 bg-transparent outline-none min-w-[120px] placeholder:text-muted-foreground"
                placeholder="Search or filter..." />
        </div>

        <Transition enter-active-class="transition ease-out duration-150"
            enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-100" leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95">
            <div v-if="isPopoverOpen"
                class="absolute z-50 w-64 p-2 mt-1 bg-popover text-popover-foreground border rounded-md shadow-md top-full left-0">

                <div v-if="activeFilterContext?.type === 'date'" class="flex justify-center">
                    <Calendar @update:model-value="selectDateValue" />
                </div>

                <div v-else class="flex flex-col gap-1">
                    <template v-if="!activeFilterContext">
                        <div class="px-2 py-1.5 text-xs font-medium text-muted-foreground">Filters</div>
                        <button v-for="filter in filters" :key="filter.key" @mousedown.prevent="selectKey(filter.key)"
                            class="w-full flex items-center px-2 py-1.5 text-sm rounded-sm hover:bg-accent hover:text-accent-foreground text-left cursor-pointer transition-colors">
                            {{ filter.label }} <span class="ml-2 text-muted-foreground">{{ filter.key }}:</span>
                        </button>
                    </template>

                    <template v-else-if="activeFilterContext.options">
                        <div class="px-2 py-1.5 text-xs font-medium text-muted-foreground">Filter by {{
                            activeFilterContext.label }}</div>
                        <button v-for="opt in filteredOptions" :key="opt"
                            @mousedown.prevent="selectValue(activeFilterContext.key, opt)"
                            class="w-full flex items-center px-2 py-1.5 text-sm rounded-sm hover:bg-accent hover:text-accent-foreground text-left cursor-pointer transition-colors">
                            <span class="font-bold mr-1">{{ activeFilterContext.key }}:</span> {{ opt }}
                        </button>

                        <div v-if="filteredOptions.length === 0"
                            class="px-2 py-3 text-sm text-center text-muted-foreground">
                            No options found.
                        </div>
                    </template>
                </div>
            </div>
        </transition>
    </div>
</template>