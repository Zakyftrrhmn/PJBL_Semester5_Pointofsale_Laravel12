<!doctype html>
<html lang="en">
@include('components.head')

<body x-data="{
    // DATA EXISTING (untuk delete, sidebar, dll)
    page: 'basicTables',
    loaded: true,
    stickyMenu: false,
    sidebarToggle: false,
    scrollTop: false,
    showModal: false,
    deleteUrl: '',
    loading: true,

    // DATA AI ASSISTANT (digabung disini)
    aiOpen: false,
    aiLoading: false,
    aiUserInput: '',
    aiMessages: [],

    // METHODS AI ASSISTANT
    getTime() {
        const now = new Date();
        return now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    async askAI(question) {
        if (!question || !question.trim() || this.aiLoading) return;

        const userQuestion = question.trim();

        this.aiMessages.push({
            role: 'user',
            content: userQuestion,
            time: this.getTime()
        });

        this.aiUserInput = '';
        this.aiLoading = true;

        this.$nextTick(() => {
            const container = document.getElementById('chat-container');
            if (container) container.scrollTop = container.scrollHeight;
        });

        try {
            const res = await fetch('/api/ai/ask', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    question: userQuestion,
                    history: this.aiMessages.slice(-10)
                })
            });

            const data = await res.json();

            this.aiMessages.push({
                role: 'assistant',
                content: data.answer || 'Maaf, tidak ada respon.',
                time: this.getTime()
            });

            this.$nextTick(() => {
                const container = document.getElementById('chat-container');
                if (container) container.scrollTop = container.scrollHeight;
            });

        } catch (e) {
            console.error('Error:', e);
            this.aiMessages.push({
                role: 'assistant',
                content: '❌ Maaf, terjadi kesalahan. Silakan coba lagi.',
                time: this.getTime()
            });
        } finally {
            this.aiLoading = false;
        }
    },

    clearAIChat() {
        if (confirm('Hapus semua riwayat chat?')) {
            this.aiMessages = [];
        }
    }
}" x-init="setTimeout(() => loading = false, 100)" class="bg-[#F7F7F7]">

    <!-- GLOBAL LOADING SCREEN -->
    <div x-show="loading" x-transition.opacity.duration.100ms
        class="fixed inset-0 z-[99999] flex items-center justify-center bg-white">
        <div class="flex flex-col items-center gap-4">
            <svg class="h-10 w-10 animate-spin text-blue-600" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                </path>
            </svg>
            <span class="text-sm text-gray-500">Memuat halaman...</span>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden">

        @include('components.sidebar')

        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
            <div @click="sidebarToggle = false" :class="sidebarToggle ? 'block lg:hidden' : 'hidden'"
                class="fixed inset-0 z-40 bg-gray-900/50"></div>

            @include('components.header')

            <main>
                <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                    <div>
                        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800">@yield('title')</h2>
                                <p class="text-sm text-gray-500">@yield('subtitle')</p>
                            </div>
                            <nav>
                                <ol class="flex items-center gap-1.5">
                                    <li>
                                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500"
                                            href="{{ route('dashboard.index') }}">
                                            Dashboard
                                            <i class="stroke-current bx bx-chevron-right"></i>
                                        </a>
                                    </li>
                                    <li class="text-sm text-gray-800">@yield('title')</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @include('components.modal')

    <!-- ================= AI ASSISTANT WIDGET (COMPACT VERSION) ================= -->
    <!-- PERHATIKAN: x-data DIHAPUS karena sudah digabung di <body> -->
    {{-- <div class="fixed bottom-4 right-4 z-40 flex flex-col items-end">

        <!-- Chat Window - COMPACT SIZE -->
        <div x-show="aiOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-4" x-cloak
            class="mb-3 w-80 h-[420px] flex flex-col rounded-xl bg-white shadow-xl border border-gray-200 overflow-hidden">

            <!-- Header - COMPACT -->
            <div
                class="p-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <div class="w-8 h-8 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z">
                                </path>
                                <path
                                    d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z">
                                </path>
                            </svg>
                        </div>
                        <div class="absolute bottom-0 right-0 w-2 h-2 bg-green-400 rounded-full border border-white">
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-xs">AI Assistant</h3>
                        <p class="text-[10px] text-white/80">Online</p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button @click="clearAIChat()" class="p-1 hover:bg-white/10 rounded transition-colors"
                        title="Clear chat">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </button>
                    <button @click="aiOpen=false" class="p-1 hover:bg-white/10 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Chat Messages - COMPACT -->
            <div id="chat-container"
                class="flex-1 overflow-y-auto p-3 space-y-3 bg-gradient-to-b from-gray-50 to-white">

                <!-- Welcome Message -->
                <div class="flex gap-2 animate-fade-in">
                    <div class="flex-shrink-0">
                        <div
                            class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-[10px] font-semibold">
                            AI
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="bg-white border border-gray-200 rounded-lg rounded-tl-sm p-2 shadow-sm">
                            <p class="text-xs text-gray-800">👋 Halo! Saya AI Assistant.</p>
                            <p class="text-[10px] text-gray-500 mt-0.5">Ada yang bisa saya bantu?</p>
                        </div>
                    </div>
                </div>

                <!-- Chat History -->
                <template x-for="(msg, index) in aiMessages" :key="index">
                    <div class="flex gap-2 animate-fade-in" :class="msg.role === 'user' ? 'flex-row-reverse' : ''">

                        <div class="flex-shrink-0" x-show="msg.role === 'assistant'">
                            <div
                                class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-[10px] font-semibold">
                                AI
                            </div>
                        </div>

                        <div class="flex-1 max-w-[75%]">
                            <div :class="msg.role === 'user' ?
                                'bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg rounded-tr-sm' :
                                'bg-white border border-gray-200 text-gray-800 rounded-lg rounded-tl-sm'"
                                class="p-2 shadow-sm">
                                <p class="text-xs whitespace-pre-line" x-text="msg.content"></p>
                                <p class="text-[9px] mt-1 opacity-70" x-text="msg.time"></p>
                            </div>
                        </div>

                        <div class="flex-shrink-0" x-show="msg.role === 'user'">
                            <div
                                class="w-6 h-6 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 text-[10px] font-semibold">
                                U
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Loading Indicator -->
                <div x-show="aiLoading" class="flex gap-2 animate-fade-in">
                    <div class="flex-shrink-0">
                        <div
                            class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-[10px] font-semibold">
                            AI
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="bg-white border border-gray-200 rounded-lg rounded-tl-sm p-2 shadow-sm">
                            <div class="flex gap-1">
                                <span class="w-1.5 h-1.5 bg-indigo-600 rounded-full animate-bounce"></span>
                                <span class="w-1.5 h-1.5 bg-indigo-600 rounded-full animate-bounce"
                                    style="animation-delay: 0.1s"></span>
                                <span class="w-1.5 h-1.5 bg-indigo-600 rounded-full animate-bounce"
                                    style="animation-delay: 0.2s"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area - COMPACT -->
            <div class="p-2.5 border-t bg-white">
                <div class="flex gap-1.5">
                    <input x-model="aiUserInput" @keydown.enter="askAI(aiUserInput)" :disabled="aiLoading"
                        placeholder="Ketik pertanyaan..."
                        class="flex-1 text-xs bg-gray-100 rounded-full px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500 transition-all disabled:opacity-50">
                    <button @click="askAI(aiUserInput)" :disabled="aiLoading || !aiUserInput.trim()"
                        class="w-8 h-8 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white flex items-center justify-center hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-[9px] text-gray-400 mt-1 text-center">
                    Tekan Enter untuk kirim
                </p>
            </div>
        </div>

        <!-- Floating Button - COMPACT -->
        <button @click="aiOpen=!aiOpen"
            class="group relative h-12 w-12 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200 flex items-center justify-center">
            <svg x-show="!aiOpen" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                <path
                    d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z">
                </path>
            </svg>
            <svg x-show="aiOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                </path>
            </svg>

            <!-- Notification Badge -->
            <span x-show="!aiOpen && aiMessages.length > 0"
                class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 rounded-full text-[9px] flex items-center justify-center animate-pulse"
                x-text="aiMessages.length"></span>

            <!-- Tooltip -->
            <span
                class="absolute bottom-full right-0 mb-2 px-2 py-1 bg-gray-900 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                Tanya AI
            </span>
        </button>
    </div>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }

        #chat-container::-webkit-scrollbar {
            width: 4px;
        }

        #chat-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        #chat-container::-webkit-scrollbar-thumb {
            background: #c7c7c7;
            border-radius: 2px;
        }

        #chat-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style> --}}

    @include('components.script')

    @stack('scripts')

</body>

</html>
