<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    CalendarRange,
    ExternalLink,
    Home,
    Inbox,
    LayoutGrid,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, home } from '@/routes';
import { index as holdsIndex } from '@/routes/admin/holds';
import { index as propertiesIndex } from '@/routes/admin/properties';
import { index as stayRequestsIndex } from '@/routes/admin/stay-requests';
import type { NavItem } from '@/types';

const page = usePage();

const mainNavItems = computed<NavItem[]>(() => {
    const can = page.props.auth.can;

    const items: { visible: boolean; item: NavItem }[] = [
        {
            visible: can.viewDashboard,
            item: { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
        },
        {
            visible: can.viewStayRequests,
            item: {
                title: 'Stay requests',
                href: stayRequestsIndex(),
                icon: Inbox,
            },
        },
        {
            visible: can.viewHolds,
            item: {
                title: 'Room holds',
                href: holdsIndex(),
                icon: CalendarRange,
            },
        },
        {
            visible: can.viewProperties,
            item: { title: 'Properties', href: propertiesIndex(), icon: Home },
        },
    ];

    return items.filter(({ visible }) => visible).map(({ item }) => item);
});

const footerNavItems: NavItem[] = [
    {
        title: 'View website',
        href: home(),
        icon: ExternalLink,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
