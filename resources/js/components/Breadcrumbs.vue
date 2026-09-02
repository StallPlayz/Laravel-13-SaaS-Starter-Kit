<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import type { BreadcrumbItem as BreadcrumbType } from '@/types';

defineProps<{
    breadcrumbs: BreadcrumbType[];
}>();
</script>

<template>
    <Breadcrumb>
        <BreadcrumbList>
            <template v-for="(item, index) in breadcrumbs" :key="index">
                <BreadcrumbItem>
                    <BreadcrumbLink v-if="item.href" as-child>
                        <Link :href="item.href">{{ item.title }}</Link>
                    </BreadcrumbLink>
                    
                    <BreadcrumbPage v-else>
                        {{ item.title }}
                    </BreadcrumbPage>
                </BreadcrumbItem>
                
                <BreadcrumbSeparator v-if="index < breadcrumbs.length - 1" />
            </template>
        </BreadcrumbList>
    </Breadcrumb>
</template>