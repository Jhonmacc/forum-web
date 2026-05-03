<template>
    <Teleport to="body">
        <transition name="modal">
            <div v-if="show" class="fixed inset-0 z-[200] flex items-center justify-center">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close"></div>

                <!-- Modal -->
                <div
                    class="relative bg-white dark:bg-gray-900 rounded-3xl w-[440px] max-w-[calc(100vw-40px)] shadow-2xl max-h-[calc(100vh-48px)] overflow-hidden flex flex-col">
                    <!-- Close button -->
                    <button @click="close"
                        class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 border-none flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors cursor-pointer z-20">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <path d="M1 1L13 13M1 13L13 1" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" />
                        </svg>
                    </button>

                    <div class="auth-modal-body overflow-y-auto">
                        <!-- Header -->
                        <div class="pt-8 px-8 pb-2 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-accent flex items-center justify-center mx-auto mb-4">
                                <svg width="24" height="24" viewBox="0 0 18 18" fill="none">
                                    <path
                                        d="M2 4C2 2.9 2.9 2 4 2H14C15.1 2 16 2.9 16 4V11C16 12.1 15.1 13 14 13H10L6 16V13H4C2.9 13 2 12.1 2 11V4Z"
                                        fill="white" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-extrabold text-gray-900 dark:text-white mb-1">
                                {{ activeTab === 'login' ? $t('auth.login_title') : $t('auth.register_title') }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ activeTab === 'login' ? $t('auth.login_subtitle') : $t('auth.register_subtitle') }}
                            </p>
                        </div>

                        <!-- Tabs -->
                        <div class="flex mx-8 mt-4 mb-2 bg-gray-100 dark:bg-gray-800 rounded-xl p-1">
                            <button @click="activeTab = 'login'"
                                class="flex-1 py-2.5 rounded-[10px] text-sm font-bold border-none cursor-pointer transition-all"
                                :class="activeTab === 'login'
                                    ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                                    : 'bg-transparent text-gray-500 dark:text-gray-400'">
                                {{ $t('auth.login') }}
                            </button>
                            <button @click="activeTab = 'register'"
                                class="flex-1 py-2.5 rounded-[10px] text-sm font-bold border-none cursor-pointer transition-all"
                                :class="activeTab === 'register'
                                    ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                                    : 'bg-transparent text-gray-500 dark:text-gray-400'">
                                {{ $t('auth.register') }}
                            </button>
                        </div>

                        <!-- Login Form -->
                        <form v-if="activeTab === 'login'" @submit.prevent="submitLogin" class="px-8 pt-4 pb-8">
                        <div class="mb-4">
                            <label
                                class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 uppercase">{{
                                $t('auth.email') }}</label>
                            <input v-model="loginForm.email" type="email" required
                                class="w-full py-3 px-4 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-accent transition-colors"
                                :class="{ 'border-red-400': loginForm.errors.email }" />
                            <p v-if="loginForm.errors.email" class="text-red-500 text-xs mt-1">{{ authError(loginForm.errors.email)
                                }}</p>
                        </div>

                        <div class="mb-4">
                            <label
                                class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 uppercase">{{
                                $t('auth.password') }}</label>
                            <div class="relative">
                                <input v-model="loginForm.password" :type="showLoginPassword ? 'text' : 'password'" required
                                    class="w-full py-3 pl-4 pr-12 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-accent transition-colors"
                                    :class="{ 'border-red-400': loginForm.errors.password }" />
                                <button type="button" @click="showLoginPassword = !showLoginPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    :aria-label="showLoginPassword ? $t('auth.hide_password') : $t('auth.show_password')"
                                    :title="showLoginPassword ? $t('auth.hide_password') : $t('auth.show_password')">
                                    <svg v-if="!showLoginPassword" width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12s-3.5 6.75-9.75 6.75S2.25 12 2.25 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 15.25A3.25 3.25 0 1 0 12 8.75a3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                    <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M10.6 5.38c.45-.08.92-.13 1.4-.13 6.25 0 9.75 6.75 9.75 6.75a17.52 17.52 0 0 1-2.6 3.45M6.12 6.92C3.63 8.6 2.25 12 2.25 12s3.5 6.75 9.75 6.75c1.85 0 3.45-.59 4.8-1.43" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M9.9 9.9a3.25 3.25 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                            <p v-if="loginForm.errors.password" class="text-red-500 text-xs mt-1">{{
                                authError(loginForm.errors.password) }}</p>
                        </div>

                        <div class="flex items-center justify-between mb-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="loginForm.remember" type="checkbox"
                                    class="rounded border-gray-300 dark:border-gray-600 text-accent focus:ring-accent" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $t('auth.remember_me')
                                    }}</span>
                            </label>
                        </div>

                        <button type="submit" :disabled="loginForm.processing"
                            class="w-full py-3 rounded-xl border-none bg-accent text-white font-bold text-sm cursor-pointer shadow-[0_4px_16px_rgba(245,184,0,0.35)] hover:bg-accent-dark transition-colors disabled:opacity-50">
                            {{ loginForm.processing ? '...' : $t('auth.login') }}
                        </button>

                        <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-4">
                            {{ $t('auth.no_account') }}
                            <button type="button" @click="activeTab = 'register'"
                                class="text-accent font-bold border-none bg-transparent cursor-pointer">
                                {{ $t('auth.register_now') }}
                            </button>
                        </p>
                        </form>

                        <!-- Register Form -->
                        <form v-if="activeTab === 'register'" @submit.prevent="submitRegister" class="px-8 pt-4 pb-8">
                        <div class="mb-3">
                            <label
                                class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 uppercase">{{
                                $t('auth.name') }}</label>
                            <input v-model="registerForm.name" type="text" required
                                class="w-full py-3 px-4 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-accent transition-colors"
                                :class="{ 'border-red-400': registerForm.errors.name }" />
                            <p v-if="registerForm.errors.name" class="text-red-500 text-xs mt-1">{{
                                authError(registerForm.errors.name) }}</p>
                        </div>

                        <div class="mb-3">
                            <label
                                class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 uppercase">{{
                                $t('auth.username') }}</label>
                            <input v-model="registerForm.username" type="text" required
                                @input="registerForm.username = registerForm.username.replace(/\s/g, '')"
                                class="w-full py-3 px-4 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-accent transition-colors"
                                :class="{ 'border-red-400': registerForm.errors.username }" />
                            <p v-if="registerForm.errors.username" class="text-red-500 text-xs mt-1">{{
                                authError(registerForm.errors.username) }}</p>
                        </div>

                        <div class="mb-3">
                            <label
                                class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 uppercase">{{
                                $t('auth.email') }}</label>
                            <input v-model="registerForm.email" type="email" required
                                class="w-full py-3 px-4 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-accent transition-colors"
                                :class="{ 'border-red-400': registerForm.errors.email }" />
                            <p v-if="registerForm.errors.email" class="text-red-500 text-xs mt-1">{{
                                authError(registerForm.errors.email) }}</p>
                        </div>

                        <div class="mb-3">
                            <label
                                class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 uppercase">{{
                                $t('auth.password') }}</label>
                            <div class="relative">
                                <input v-model="registerForm.password" :type="showRegisterPassword ? 'text' : 'password'" required
                                    class="w-full py-3 pl-4 pr-12 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-accent transition-colors"
                                    :class="{ 'border-red-400': registerForm.errors.password }" />
                                <button type="button" @click="showRegisterPassword = !showRegisterPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    :aria-label="showRegisterPassword ? $t('auth.hide_password') : $t('auth.show_password')"
                                    :title="showRegisterPassword ? $t('auth.hide_password') : $t('auth.show_password')">
                                    <svg v-if="!showRegisterPassword" width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12s-3.5 6.75-9.75 6.75S2.25 12 2.25 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 15.25A3.25 3.25 0 1 0 12 8.75a3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                    <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M10.6 5.38c.45-.08.92-.13 1.4-.13 6.25 0 9.75 6.75 9.75 6.75a17.52 17.52 0 0 1-2.6 3.45M6.12 6.92C3.63 8.6 2.25 12 2.25 12s3.5 6.75 9.75 6.75c1.85 0 3.45-.59 4.8-1.43" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M9.9 9.9a3.25 3.25 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                            <p v-if="registerForm.errors.password" class="text-red-500 text-xs mt-1">{{
                                authError(registerForm.errors.password) }}</p>
                        </div>

                        <div class="mb-5">
                            <label
                                class="block text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 mb-1.5 uppercase">{{
                                    $t('auth.confirm_password') }}</label>
                            <div class="relative">
                                <input v-model="registerForm.password_confirmation" :type="showRegisterPasswordConfirmation ? 'text' : 'password'" required
                                    class="w-full py-3 pl-4 pr-12 rounded-xl border-[1.5px] border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-accent transition-colors" />
                                <button type="button" @click="showRegisterPasswordConfirmation = !showRegisterPasswordConfirmation"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    :aria-label="showRegisterPasswordConfirmation ? $t('auth.hide_password') : $t('auth.show_password')"
                                    :title="showRegisterPasswordConfirmation ? $t('auth.hide_password') : $t('auth.show_password')">
                                    <svg v-if="!showRegisterPasswordConfirmation" width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12s-3.5 6.75-9.75 6.75S2.25 12 2.25 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 15.25A3.25 3.25 0 1 0 12 8.75a3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                    <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M10.6 5.38c.45-.08.92-.13 1.4-.13 6.25 0 9.75 6.75 9.75 6.75a17.52 17.52 0 0 1-2.6 3.45M6.12 6.92C3.63 8.6 2.25 12 2.25 12s3.5 6.75 9.75 6.75c1.85 0 3.45-.59 4.8-1.43" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M9.9 9.9a3.25 3.25 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" :disabled="registerForm.processing"
                            class="w-full py-3 rounded-xl border-none bg-accent text-white font-bold text-sm cursor-pointer shadow-[0_4px_16px_rgba(245,184,0,0.35)] hover:bg-accent-dark transition-colors disabled:opacity-50">
                            {{ registerForm.processing ? '...' : $t('auth.register') }}
                        </button>

                        <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-4">
                            {{ $t('auth.has_account') }}
                            <button type="button" @click="activeTab = 'login'"
                                class="text-accent font-bold border-none bg-transparent cursor-pointer">
                                {{ $t('auth.login_now') }}
                            </button>
                        </p>
                        </form>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    show: { type: Boolean, default: false },
    initialTab: { type: String, default: 'login' },
});

const emit = defineEmits(['close']);
const { locale } = useI18n();

const activeTab = ref(props.initialTab);
const showLoginPassword = ref(false);
const showRegisterPassword = ref(false);
const showRegisterPasswordConfirmation = ref(false);

watch(() => props.initialTab, (val) => {
    activeTab.value = val;
});

const loginForm = useForm({
    email: '',
    password: '',
    remember: false,
});

const registerForm = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const ptBrErrorMessages = [
    {
        match: 'The password field confirmation does not match.',
        value: 'A confirmação da senha não corresponde.',
    },
    {
        match: 'These credentials do not match our records.',
        value: 'As credenciais informadas não conferem com nossos registros.',
    },
    {
        match: 'The email field must be a valid email address.',
        value: 'Informe um endereço de e-mail válido.',
    },
    {
        match: 'The email has already been taken.',
        value: 'Este e-mail já está em uso.',
    },
    {
        match: 'The username has already been taken.',
        value: 'Este nome de usuário já está em uso.',
    },
    {
        match: 'The password field must be at least 8 characters.',
        value: 'A senha deve ter pelo menos 8 caracteres.',
    },
    {
        match: 'The password field is required.',
        value: 'O campo senha é obrigatório.',
    },
    {
        match: 'The email field is required.',
        value: 'O campo e-mail é obrigatório.',
    },
    {
        match: 'The name field is required.',
        value: 'O campo nome é obrigatório.',
    },
    {
        match: 'The username field is required.',
        value: 'O campo nome de usuário é obrigatório.',
    },
];

const authError = (message) => {
    if (!message || locale.value !== 'pt-BR') {
        return message;
    }

    const normalizedMessage = String(message);
    const translated = ptBrErrorMessages.find(({ match }) => normalizedMessage.includes(match));

    return translated?.value ?? normalizedMessage
        .replaceAll('The password field confirmation does not match.', 'A confirmação da senha não corresponde.')
        .replaceAll('The password field', 'O campo senha')
        .replaceAll('The email field', 'O campo e-mail')
        .replaceAll('The name field', 'O campo nome')
        .replaceAll('The username field', 'O campo nome de usuário')
        .replaceAll('is required.', 'é obrigatório.')
        .replaceAll('must be a valid email address.', 'deve ser um endereço de e-mail válido.')
        .replaceAll('has already been taken.', 'já está em uso.');
};

const submitLogin = () => {
    loginForm.post('/login', {
        headers: { 'X-Locale': locale.value },
        onFinish: () => loginForm.reset('password'),
    });
};

const submitRegister = () => {
    registerForm.post('/register', {
        headers: { 'X-Locale': locale.value },
        onFinish: () => registerForm.reset('password', 'password_confirmation'),
    });
};

const close = () => {
    emit('close');
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.25s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.auth-modal-body {
    max-height: calc(100vh - 48px);
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.auth-modal-body::-webkit-scrollbar {
    display: none;
}
</style>
