<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
import { Building2, CheckSquare, Users, Receipt, ArrowRight, CheckCircle2, Clock, Inbox } from '@lucide/vue';
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
                        href="#how-it-works"
                        class="inline-flex h-12 items-center justify-center rounded-md border border-input bg-background px-8 text-base font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground w-full sm:w-auto"
                    >
                        See how it works
                    </a>
                </div>
            </section>

            <!-- Dashboard Preview Section -->
            <section class="container mx-auto px-4 md:px-8 pb-24">
                <div class="relative mx-auto max-w-5xl rounded-xl border bg-background shadow-2xl overflow-hidden">
                    <!-- Browser Chrome -->
                    <div class="flex items-center gap-2 border-b bg-muted/50 px-4 py-3">
                        <div class="flex gap-1.5">
                            <div class="h-3 w-3 rounded-full bg-red-500/80"></div>
                            <div class="h-3 w-3 rounded-full bg-amber-500/80"></div>
                            <div class="h-3 w-3 rounded-full bg-emerald-500/80"></div>
                        </div>
                        <div class="mx-auto flex h-6 w-full max-w-md items-center justify-center rounded-md bg-background text-xs text-muted-foreground shadow-sm">
                            syncdesk.app/workspaces/acme-agency
                        </div>
                    </div>
                    <!-- Mockup Content -->
                    <div class="flex h-[400px] md:h-[500px] bg-muted/10">
                        <!-- Sidebar -->
                        <div class="hidden w-64 border-r bg-background p-4 md:block">
                            <div class="flex items-center gap-2 mb-8 px-2">
                                <div class="rounded bg-primary/10 p-1.5">
                                    <Building2 class="h-4 w-4 text-primary" />
                                </div>
                                <span class="font-semibold text-sm">Acme Agency</span>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-3 rounded-md bg-muted px-3 py-2 text-sm font-medium">
                                    <LayoutGrid class="h-4 w-4" /> Dashboard
                                </div>
                                <div class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-muted-foreground">
                                    <Inbox class="h-4 w-4" /> Service Requests
                                </div>
                                <div class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-muted-foreground">
                                    <Briefcase class="h-4 w-4" /> Projects
                                </div>
                                <div class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-muted-foreground">
                                    <Receipt class="h-4 w-4" /> Invoices
                                </div>
                            </div>
                        </div>
                        <!-- Main Content -->
                        <div class="flex-1 p-6 md:p-8 overflow-hidden">
                            <div class="mb-6 flex items-center justify-between">
                                <div>
                                    <h2 class="text-2xl font-bold">Dashboard</h2>
                                    <p class="text-sm text-muted-foreground">Welcome back, Sarah.</p>
                                </div>
                            </div>
                            <div class="grid gap-4 md:grid-cols-3 mb-6">
                                <div class="rounded-lg border bg-card p-4 shadow-sm">
                                    <p class="text-sm font-medium text-muted-foreground mb-1">Active Projects</p>
                                    <p class="text-2xl font-bold">12</p>
                                </div>
                                <div class="rounded-lg border bg-card p-4 shadow-sm">
                                    <p class="text-sm font-medium text-muted-foreground mb-1">Pending Requests</p>
                                    <p class="text-2xl font-bold">3</p>
                                </div>
                                <div class="rounded-lg border bg-card p-4 shadow-sm">
                                    <p class="text-sm font-medium text-muted-foreground mb-1">Outstanding</p>
                                    <p class="text-2xl font-bold">$4,250</p>
                                </div>
                            </div>
                            <div class="rounded-lg border bg-card shadow-sm">
                                <div class="border-b p-4">
                                    <h3 class="font-semibold">Recent Tasks</h3>
                                </div>
                                <div class="divide-y">
                                    <div class="flex items-center justify-between p-4">
                                        <div class="flex items-center gap-3">
                                            <CheckCircle2 class="h-4 w-4 text-emerald-500" />
                                            <span class="text-sm font-medium line-through text-muted-foreground">Design new logo</span>
                                        </div>
                                        <span class="text-xs text-muted-foreground">Stark Industries</span>
                                    </div>
                                    <div class="flex items-center justify-between p-4">
                                        <div class="flex items-center gap-3">
                                            <Clock class="h-4 w-4 text-blue-500" />
                                            <span class="text-sm font-medium">Write landing page copy</span>
                                        </div>
                                        <span class="text-xs text-muted-foreground">Wayne Enterprises</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Proof -->
            <section class="border-y bg-muted/30 py-12">
                <div class="container mx-auto px-4 text-center">
                    <p class="text-sm font-medium text-muted-foreground uppercase tracking-wider mb-8">Trusted by modern agencies worldwide</p>
                    <div class="flex flex-wrap justify-center gap-8 md:gap-16 opacity-50 grayscale">
                        <!-- Placeholder Logos -->
                        <div class="flex items-center gap-2 font-bold text-xl"><div class="h-6 w-6 rounded bg-foreground"></div> Acme Corp</div>
                        <div class="flex items-center gap-2 font-bold text-xl"><div class="h-6 w-6 rounded-full bg-foreground"></div> Globex</div>
                        <div class="flex items-center gap-2 font-bold text-xl"><div class="h-6 w-6 rotate-45 bg-foreground"></div> Initech</div>
                        <div class="flex items-center gap-2 font-bold text-xl"><div class="h-6 w-6 rounded-tl-lg rounded-br-lg bg-foreground"></div> Soylent</div>
                    </div>
                </div>
            </section>

            <!-- How it Works Section -->
            <section id="how-it-works" class="py-24">
                <div class="container mx-auto px-4 md:px-8">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">How SyncDesk Works</h2>
                        <p class="mt-4 text-lg text-muted-foreground">A seamless workflow from request to payment.</p>
                    </div>

                    <div class="grid gap-12 md:grid-cols-3 relative">
                        <!-- Connecting Line (Desktop only) -->
                        <div class="hidden md:block absolute top-12 left-[16%] right-[16%] h-0.5 bg-border -z-10"></div>

                        <div class="relative text-center">
                            <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full border-8 border-background bg-primary/10 text-primary">
                                <Inbox class="h-8 w-8" />
                            </div>
                            <h3 class="mb-3 text-xl font-bold">1. Receive Requests</h3>
                            <p class="text-muted-foreground leading-relaxed">
                                Clients submit service requests directly through their portal. You review, approve, and convert them into actionable projects.
                            </p>
                        </div>

                        <div class="relative text-center">
                            <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full border-8 border-background bg-primary/10 text-primary">
                                <CheckSquare class="h-8 w-8" />
                            </div>
                            <h3 class="mb-3 text-xl font-bold">2. Execute & Approve</h3>
                            <p class="text-muted-foreground leading-relaxed">
                                Your team works on tasks. When finished, request client approval with one click to ensure everyone is aligned.
                            </p>
                        </div>

                        <div class="relative text-center">
                            <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full border-8 border-background bg-primary/10 text-primary">
                                <Receipt class="h-8 w-8" />
                            </div>
                            <h3 class="mb-3 text-xl font-bold">3. Bill the Client</h3>
                            <p class="text-muted-foreground leading-relaxed">
                                Turn completed projects into professional invoices instantly. Clients can view and pay directly from their dashboard.
                            </p>
                        </div>
                    </div>
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

            <!-- Pricing Section -->
            <section id="pricing" class="py-24">
                <div class="container mx-auto px-4 md:px-8">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Simple, transparent pricing</h2>
                        <p class="mt-4 text-lg text-muted-foreground">Start for free, upgrade when you need more power.</p>
                    </div>

                    <div class="grid gap-8 md:grid-cols-2 max-w-4xl mx-auto">
                        <!-- Free Tier -->
                        <div class="rounded-2xl border bg-card p-8 shadow-sm flex flex-col">
                            <h3 class="text-2xl font-bold mb-2">Starter</h3>
                            <p class="text-muted-foreground mb-6">Perfect for freelancers just getting started.</p>
                            <div class="mb-6">
                                <span class="text-4xl font-extrabold">$0</span>
                                <span class="text-muted-foreground">/month</span>
                            </div>
                            <ul class="space-y-3 mb-8 flex-1">
                                <li class="flex items-center gap-3">
                                    <CheckCircle2 class="h-5 w-5 text-primary" />
                                    <span>1 Workspace</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <CheckCircle2 class="h-5 w-5 text-primary" />
                                    <span>Up to 3 Active Projects</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <CheckCircle2 class="h-5 w-5 text-primary" />
                                    <span>Basic Client Portal</span>
                                </li>
                            </ul>
                            <Link
                                :href="register()"
                                class="inline-flex h-12 w-full items-center justify-center rounded-md border-2 border-primary bg-transparent px-8 text-base font-medium text-primary transition-colors hover:bg-primary/5"
                            >
                                Get Started
                            </Link>
                        </div>

                        <!-- Pro Tier -->
                        <div class="rounded-2xl border-2 border-primary bg-card p-8 shadow-md relative flex flex-col">
                            <div class="absolute top-0 right-8 -translate-y-1/2 rounded-full bg-primary px-3 py-1 text-xs font-bold text-primary-foreground uppercase tracking-wider">
                                Most Popular
                            </div>
                            <h3 class="text-2xl font-bold mb-2">Agency Pro</h3>
                            <p class="text-muted-foreground mb-6">For growing teams that need more power.</p>
                            <div class="mb-6">
                                <span class="text-4xl font-extrabold">$49</span>
                                <span class="text-muted-foreground">/month</span>
                            </div>
                            <ul class="space-y-3 mb-8 flex-1">
                                <li class="flex items-center gap-3">
                                    <CheckCircle2 class="h-5 w-5 text-primary" />
                                    <span>Unlimited Workspaces</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <CheckCircle2 class="h-5 w-5 text-primary" />
                                    <span>Unlimited Projects & Tasks</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <CheckCircle2 class="h-5 w-5 text-primary" />
                                    <span>Advanced Client Approvals</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <CheckCircle2 class="h-5 w-5 text-primary" />
                                    <span>Custom Branding</span>
                                </li>
                            </ul>
                            <Link
                                :href="register()"
                                class="inline-flex h-12 w-full items-center justify-center rounded-md bg-primary px-8 text-base font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90"
                            >
                                Start 14-Day Trial
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Final CTA -->
            <section class="border-t bg-primary text-primary-foreground py-24">
                <div class="container mx-auto px-4 text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl mb-6">Ready to stop the chaos?</h2>
                    <p class="text-lg opacity-90 mb-10 max-w-2xl mx-auto">
                        Join thousands of agencies managing their business on SyncDesk today. Setup takes less than 2 minutes.
                    </p>
                    <Link
                        :href="register()"
                        class="inline-flex h-14 items-center justify-center rounded-md bg-background px-8 text-lg font-bold text-primary shadow transition-colors hover:bg-background/90"
                    >
                        Create your free account
                        <ArrowRight class="ml-2 h-5 w-5" />
                    </Link>
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
