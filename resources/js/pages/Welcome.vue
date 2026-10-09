<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
import { Building2, CheckSquare, Users, Receipt, ArrowRight } from '@lucide/vue';
</script>

<template>
    <Head title="SyncDesk - Agency Management Software">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <!-- Navigation -->
        <header class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="container mx-auto flex h-16 items-center justify-between px-4 md:px-8">
                <div class="flex items-center gap-2">
                    <div class="rounded-lg bg-primary/10 p-2">
                        <Building2 class="h-6 w-6 text-primary" />
                    </div>
                    <span class="text-xl font-bold tracking-tight">SyncDesk</span>
                </div>
                <nav class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
                    >
                        Go to Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="text-sm font-medium text-muted-foreground hover:text-foreground"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="register()"
                            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90"
                        >
                            Get Started
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <!-- Hero Section -->
            <section class="container mx-auto px-4 py-24 md:px-8 md:py-32 lg:py-40 text-center">
                <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl md:text-6xl lg:text-7xl max-w-4xl mx-auto">
                    Run your agency <br class="hidden sm:block" />
                    <span class="text-primary">without the chaos.</span>
                </h1>
                <p class="mt-6 max-w-2xl mx-auto text-lg text-muted-foreground sm:text-xl">
                    SyncDesk is the all-in-one platform for service businesses. Manage projects, collaborate with your team, get client approvals, and send invoices—all in one place.
                </p>
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link
                        :href="register()"
                        class="inline-flex h-12 items-center justify-center rounded-md bg-primary px-8 text-base font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 w-full sm:w-auto"
                    >
                        Start for free
                        <ArrowRight class="ml-2 h-4 w-4" />
                    </Link>
                    <a
                        href="#features"
                        class="inline-flex h-12 items-center justify-center rounded-md border border-input bg-background px-8 text-base font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground w-full sm:w-auto"
                    >
                        See how it works
                    </a>
                </div>
            </section>

            <!-- Features Section -->
            <section id="features" class="border-t bg-muted/30 py-24">
                <div class="container mx-auto px-4 md:px-8">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Everything you need to scale</h2>
                        <p class="mt-4 text-lg text-muted-foreground">Replace your patchwork of tools with one unified platform.</p>
                    </div>

                    <div class="grid gap-8 md:grid-cols-3">
                        <div class="rounded-2xl border bg-card p-8 shadow-sm">
                            <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                                <CheckSquare class="h-6 w-6 text-primary" />
                            </div>
                            <h3 class="mb-3 text-xl font-bold">Project Management</h3>
                            <p class="text-muted-foreground leading-relaxed">
                                Break down complex projects into manageable tasks. Assign work to your team, set deadlines, and track progress in real-time.
                            </p>
                        </div>

                        <div class="rounded-2xl border bg-card p-8 shadow-sm">
                            <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                                <Users class="h-6 w-6 text-primary" />
                            </div>
                            <h3 class="mb-3 text-xl font-bold">Client Portals</h3>
                            <p class="text-muted-foreground leading-relaxed">
                                Give your clients a professional dashboard to view project status, submit service requests, and approve deliverables.
                            </p>
                        </div>

                        <div class="rounded-2xl border bg-card p-8 shadow-sm">
                            <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                                <Receipt class="h-6 w-6 text-primary" />
                            </div>
                            <h3 class="mb-3 text-xl font-bold">Integrated Billing</h3>
                            <p class="text-muted-foreground leading-relaxed">
                                Turn completed projects into professional invoices with one click. Track outstanding balances and get paid faster.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="border-t py-12">
            <div class="container mx-auto px-4 md:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <Building2 class="h-5 w-5 text-primary" />
                    <span class="font-semibold">SyncDesk</span>
                </div>
                <p class="text-sm text-muted-foreground">
                    &copy; {{ new Date().getFullYear() }} SyncDesk Inc. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</template>
