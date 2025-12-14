<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const scrolled = ref(false);

const handleScroll = () => {
    scrolled.value = window.scrollY > 50;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <Head title="Bienvenido a NotionLike" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
        <!-- Navbar con scroll -->
        <nav 
            :class="[
                'fixed top-0 left-0 right-0 z-50 transition-all duration-300',
                scrolled ? 'bg-white shadow-lg py-3' : 'bg-transparent py-6'
            ]"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-xl">N</span>
                        </div>
                        <span :class="[
                            'text-2xl font-bold transition-colors',
                            scrolled ? 'text-gray-900' : 'text-gray-900'
                        ]">
                            NotionLike
                        </span>
                    </div>

                    <!-- Navigation Links -->
                    <div v-if="canLogin" class="flex items-center space-x-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-6 py-2 rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold hover:from-blue-600 hover:to-purple-700 transition-all shadow-md hover:shadow-lg"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                :class="[
                                    'px-6 py-2 rounded-lg font-semibold transition-all',
                                    scrolled 
                                        ? 'text-gray-700 hover:text-blue-600' 
                                        : 'text-gray-700 hover:text-blue-600'
                                ]"
                            >
                                Iniciar Sesión
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-6 py-2 rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold hover:from-blue-600 hover:to-purple-700 transition-all shadow-md hover:shadow-lg"
                            >
                                Registrarse
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Content -->
                    <div class="space-y-8">
                        <div class="inline-block">
                            <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                                ✨ Tu espacio de trabajo inteligente
                            </span>
                        </div>

                        <h1 class="text-5xl lg:text-6xl font-bold text-gray-900 leading-tight">
                            Organiza tu vida
                            <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                                de forma simple
                            </span>
                        </h1>

                        <p class="text-xl text-gray-600 leading-relaxed">
                            Crea documentos, gestiona proyectos y colabora con tu equipo. 
                            Todo en un solo lugar, de manera intuitiva y poderosa.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="route('register')"
                                class="px-8 py-4 rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold text-lg hover:from-blue-600 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl text-center"
                            >
                                Comenzar Gratis
                            </Link>
                            <Link
                                v-else
                                :href="route('dashboard')"
                                class="px-8 py-4 rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold text-lg hover:from-blue-600 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl text-center"
                            >
                                Ir al Dashboard
                            </Link>
                            <button class="px-8 py-4 rounded-lg border-2 border-gray-300 text-gray-700 font-semibold text-lg hover:border-blue-500 hover:text-blue-600 transition-all">
                                Ver Demo
                            </button>
                        </div>

                        <div class="flex items-center space-x-8 pt-4">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span class="text-gray-600 font-medium">4.9/5 estrellas</span>
                            </div>
                            <div class="text-gray-600">
                                <span class="font-bold text-gray-900">10k+</span> usuarios activos
                            </div>
                        </div>
                    </div>

                    <!-- Right Content - Mockup -->
                    <div class="relative">
                        <div class="bg-white rounded-2xl shadow-2xl p-8 transform rotate-2 hover:rotate-0 transition-transform duration-300">
                            <div class="space-y-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                </div>
                                <div class="space-y-3">
                                    <div class="h-4 bg-gradient-to-r from-blue-200 to-purple-200 rounded w-3/4"></div>
                                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                                    <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                                    <div class="h-32 bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg mt-4"></div>
                                    <div class="grid grid-cols-2 gap-3 mt-4">
                                        <div class="h-20 bg-blue-100 rounded-lg"></div>
                                        <div class="h-20 bg-purple-100 rounded-lg"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Floating elements -->
                        <div class="absolute -top-6 -right-6 w-32 h-32 bg-blue-400 rounded-full opacity-20 animate-pulse"></div>
                        <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-purple-400 rounded-full opacity-20 animate-pulse" style="animation-delay: 1s;"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Funcionalidades Poderosas
                    </h2>
                    <p class="text-xl text-gray-600">
                        Todo lo que necesitas para ser más productivo
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="p-8 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 hover:shadow-xl transition-shadow">
                        <div class="w-14 h-14 bg-blue-500 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Documentos</h3>
                        <p class="text-gray-600">
                            Crea y edita documentos ricos con formato. Autoguardado en tiempo real.
                        </p>
                    </div>

                    <div class="p-8 rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100 hover:shadow-xl transition-shadow">
                        <div class="w-14 h-14 bg-purple-500 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Colaboración</h3>
                        <p class="text-gray-600">
                            Comparte workspaces con tu equipo y colabora en tiempo real.
                        </p>
                    </div>

                    <div class="p-8 rounded-2xl bg-gradient-to-br from-green-50 to-green-100 hover:shadow-xl transition-shadow">
                        <div class="w-14 h-14 bg-green-500 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Calendario</h3>
                        <p class="text-gray-600">
                            Organiza eventos y fechas importantes. Mantén tu agenda al día.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-to-r from-blue-600 to-purple-600">
            <div class="max-w-4xl mx-auto text-center px-4">
                <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6">
                    ¿Listo para comenzar?
                </h2>
                <p class="text-xl text-blue-100 mb-8">
                    Únete a miles de usuarios que ya están siendo más productivos
                </p>
                <Link
                    v-if="!$page.props.auth.user"
                    :href="route('register')"
                    class="inline-block px-10 py-4 rounded-lg bg-white text-blue-600 font-bold text-lg hover:bg-gray-100 transition-all shadow-lg hover:shadow-xl"
                >
                    Crear Cuenta Gratis
                </Link>
                <Link
                    v-else
                    :href="route('dashboard')"
                    class="inline-block px-10 py-4 rounded-lg bg-white text-blue-600 font-bold text-lg hover:bg-gray-100 transition-all shadow-lg hover:shadow-xl"
                >
                    Ir a tu Dashboard
                </Link>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-white font-bold text-lg mb-4">NotionLike</h3>
                        <p class="text-sm">
                            Tu espacio de trabajo todo-en-uno para ser más productivo.
                        </p>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-4">Producto</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-white transition">Características</a></li>
                            <li><a href="#" class="hover:text-white transition">Precios</a></li>
                            <li><a href="#" class="hover:text-white transition">Plantillas</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-4">Recursos</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-white transition">Documentación</a></li>
                            <li><a href="#" class="hover:text-white transition">Tutoriales</a></li>
                            <li><a href="#" class="hover:text-white transition">Blog</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-4">Empresa</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-white transition">Sobre Nosotros</a></li>
                            <li><a href="#" class="hover:text-white transition">Contacto</a></li>
                            <li><a href="#" class="hover:text-white transition">Privacidad</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-12 pt-8 text-center text-sm">
                    <p>&copy; 2024 NotionLike. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>
    </div>
</template>