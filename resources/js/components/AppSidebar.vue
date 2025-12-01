<script setup lang="ts">
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
import { dashboard } from '@/routes';
import { User, Shield, Settings, Users } from 'lucide-vue-next';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

// Roles del usuario
const page = usePage();
const roles = computed(() => (page.props as any)?.auth?.roles ?? []);
const isAdminGeneral = computed(() => roles.value?.includes('Administrador general'));
const isAdminUnidad = computed(() => roles.value?.includes('Administrador de unidad'));
const isAdminAcademico = computed(() => roles.value?.includes('Administrador academico'));
const isAdmin = computed(() => isAdminGeneral.value || isAdminUnidad.value || isAdminAcademico.value);
const isEgresado = computed(() => roles.value?.includes('Egresados'));
const isEstudiante = computed(() => roles.value?.includes('Estudiantes'));

// Navegación principal (incluye únicamente los accesos solicitados adicionalmente)
const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];
    // Ocultar Panel y Perfil para todos los administradores
    if (!isAdmin.value) {
        items.push({ title: 'Panel', href: dashboard().url, icon: LayoutGrid });
        items.push({ title: 'Perfil y datos', href: '/perfil-datos', icon: User });
    }
    
    // Encuestas solo para Egresados y Estudiantes (NO para Admin General)
    if (isEstudiante.value || isEgresado.value) {
        items.push({ title: 'Encuesta Pre-Egreso', href: '/encuesta-preegreso', icon: BookOpen });
    }
    if (isEgresado.value) {
        items.push({ title: 'Encuesta de Egreso', href: '/encuesta-egreso', icon: BookOpen });
    }
    if (isEgresado.value) {
        items.push({ title: 'Encuesta Laboral', href: '/encuesta-laboral', icon: BookOpen });
    }
    if (isEgresado.value || isEstudiante.value) {
        items.push({ title: 'Acuses de Seguimiento', href: '/acuses-seguimiento', icon: Shield });
    }
    
    if (isAdminGeneral.value) {
        items.push({ title: 'Gestor de Usuarios', href: '/admin-general', icon: Users });
    }
    if (isAdminAcademico.value || isAdminGeneral.value) {
        items.push({ title: 'Gestión Académica', href: '/admin-academica', icon: Users });
    }
    if (isAdminUnidad.value || isAdminGeneral.value) {
        items.push({ title: 'Encuestas', href: '/admin-unidad', icon: Users });
    }
    if (isAdminGeneral.value) {
        items.push({ title: 'Gestor de permisos', href: '/permisos', icon: Shield });
        items.push({ title: 'Roles', href: '/roles', icon: Settings });
        items.push({ title: 'Asignar roles', href: '/usuarios/roles', icon: Settings });
        items.push({ title: 'Invitaciones', href: '/admin/invitations', icon: Settings });
        items.push({ title: 'Catálogo egresados', href: '/catalogo-egresados', icon: Users });
    }
    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Repositorio de GitHub',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentación',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
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
            <NavMain :items="mainNavItems" label="Navegación" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
