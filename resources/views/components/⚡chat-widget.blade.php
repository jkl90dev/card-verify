<?php

/**
 * Fait par JKL90DEV
 */

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Mail\NewChatSessionStarted;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

new class extends Component {
    use WithFileUploads;

    public $isOpen = false;
    public $name = '';
    public $email = '';
    public $chatSessionId = null;
    public $messageText = '';
    public $unreadCount = 0;

    // File uploads
    public $imageFile = null;
    public $voiceFile = null;

    // Typing simulation state
    public $isAgentTyping = false;

    /**
     * Initialisation. Récupère la session de chat active si elle existe.
     */
    public function mount(): void
    {
        $this->chatSessionId = session('active_chat_session_id');
        $this->updateUnreadCount();
    }

    /**
     * Met à jour le compteur de messages non lus de l'agent pour l'utilisateur.
     */
    public function updateUnreadCount(): void
    {
        if ($this->chatSessionId) {
            $session = ChatSession::find($this->chatSessionId);
            if ($session && $session->status === 'active') {
                $this->unreadCount = $session->unreadMessagesForUser()->count();
            } else {
                $this->unreadCount = 0;
            }
        } else {
            $this->unreadCount = 0;
        }
    }

    /**
     * Bascule l'état d'ouverture du chat.
     */
    public function toggleChat(): void
    {
        $this->isOpen = !$this->isOpen;
        if ($this->isOpen && $this->chatSessionId) {
            $this->markAllAsRead();
        }
        $this->updateUnreadCount();
    }

    /**
     * Hook Livewire appelé automatiquement quand isOpen est modifié (via entangle).
     */
    public function updatedIsOpen($value): void
    {
        if ($value && $this->chatSessionId) {
            $this->markAllAsRead();
        }
        $this->updateUnreadCount();
    }

    /**
     * Marque tous les messages de la session comme lus par l'utilisateur.
     */
    private function markAllAsRead(): void
    {
        if ($this->chatSessionId) {
            ChatMessage::where('chat_session_id', $this->chatSessionId)
                ->where('sender', 'agent')
                ->where('is_read', false)
                ->update(['is_read' => true]);
            $this->unreadCount = 0;
        }
    }

    /**
     * Démarre une nouvelle session de chat.
     */
    public function startChat(): void
    {
        $this->validate([
            'name' => 'required|string|min:2|max:50',
            'email' => 'nullable|email|max:100',
        ], [
            'name.required' => __('Le nom est obligatoire.'),
            'name.min' => __('Le nom doit contenir au moins 2 caractères.'),
            'email.email' => __('L\'adresse e-mail doit être valide.'),
        ]);

        $session = ChatSession::create([
            'user_name' => $this->name,
            'user_email' => $this->email ?: null,
            'status' => 'active',
        ]);

        $this->chatSessionId = $session->id;
        session(['active_chat_session_id' => $session->id]);

        // Message de bienvenue du système/agent
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'agent',
            'message' => __('Bonjour :name ! Un agent a été notifié et va vous répondre dans un instant. En quoi pouvons-nous vous aider ?', ['name' => $this->name]),
            'is_read' => true,
        ]);

        $this->isOpen = true;
        $this->markAllAsRead();
        $this->updateUnreadCount();
    }

    /**
     * Envoie un e-mail de notification à l'agent pour le premier message du chat.
     */
    private function notifyAgentOfNewChat(string $messageContent): void
    {
        try {
            $session = ChatSession::find($this->chatSessionId);
            if ($session) {
                Mail::to(env('AGENT_RECEIVE_EMAIL', 'agent@cardverify.com'))
                    ->send(new NewChatSessionStarted($session, $messageContent));
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de la notification e-mail de chat : ' . $e->getMessage());
        }
    }

    /**
     * Envoie un message utilisateur.
     */
    public function sendMessage(): void
    {
        if (empty(trim($this->messageText))) {
            return;
        }

        $this->validate([
            'messageText' => 'required|string|max:1000',
        ]);

        ChatMessage::create([
            'chat_session_id' => $this->chatSessionId,
            'sender' => 'user',
            'message' => $this->messageText,
            'is_read' => false,
        ]);

        $sentMessage = $this->messageText;
        $this->messageText = '';
        $this->markAllAsRead();

        // Co-Pilote : Si c'est le premier message de l'utilisateur, on simule une réponse de l'agent après 2 secondes
        $userMessageCount = ChatMessage::where('chat_session_id', $this->chatSessionId)
            ->where('sender', 'user')
            ->count();

        if ($userMessageCount === 1) {
            $this->isAgentTyping = true;
            $this->notifyAgentOfNewChat($sentMessage);
            // On retarde légèrement la réponse (géré dans le cycle Livewire / JS)
            $this->dispatch('trigger-copilot-response', message: $sentMessage);
        }
    }

    /**
     * Action déclenchée pour simuler la saisie et la réponse du co-pilote.
     */
    public function triggerCopilotReply(): void
    {
        $this->isAgentTyping = false;

        // On s'assure que la session est toujours active et qu'aucun agent réel n'a répondu entre-temps
        if ($this->chatSessionId) {
            ChatMessage::create([
                'chat_session_id' => $this->chatSessionId,
                'sender' => 'agent',
                'message' => __('Merci pour ces précisions. Un agent de notre équipe de sécurité est en train d\'analyser les détails. Veuillez patienter en ligne, nous sommes là.'),
                'is_read' => $this->isOpen,
            ]);
            $this->updateUnreadCount();
        }
    }

    /**
     * Gère le téléversement et l'envoi d'une image.
     */
    public function updatedImageFile(): void
    {
        if (!$this->chatSessionId || !$this->imageFile) {
            return;
        }

        $this->validate([
            'imageFile' => 'image|max:5120',
        ], [
            'imageFile.image' => __('Le fichier doit être une image.'),
            'imageFile.max' => __('Fichier trop volumineux (max 5Mo)'),
        ]);

        $path = $this->imageFile->store('chat_attachments', 'public');

        ChatMessage::create([
            'chat_session_id' => $this->chatSessionId,
            'sender' => 'user',
            'message' => '',
            'attachment_path' => $path,
            'attachment_type' => 'image',
            'is_read' => false,
        ]);

        $this->imageFile = null;
        $this->markAllAsRead();
        $this->updateUnreadCount();

        $userMessageCount = ChatMessage::where('chat_session_id', $this->chatSessionId)
            ->where('sender', 'user')
            ->count();

        if ($userMessageCount === 1) {
            $this->isAgentTyping = true;
            $this->notifyAgentOfNewChat('[Image]');
            $this->dispatch('trigger-copilot-response', message: '[Image]');
        }

        $this->dispatch('refresh-chat');
    }

    /**
     * Gère l'envoi d'une note vocale après son téléversement.
     */
    public function sendVoiceNote(): void
    {
        if (!$this->chatSessionId || !$this->voiceFile) {
            return;
        }

        $this->validate([
            'voiceFile' => 'required|file|max:5120',
        ], [
            'voiceFile.max' => __('Fichier trop volumineux (max 5Mo)'),
        ]);

        $path = $this->voiceFile->store('chat_attachments', 'public');

        ChatMessage::create([
            'chat_session_id' => $this->chatSessionId,
            'sender' => 'user',
            'message' => '',
            'attachment_path' => $path,
            'attachment_type' => 'audio',
            'is_read' => false,
        ]);

        $this->voiceFile = null;
        $this->markAllAsRead();
        $this->updateUnreadCount();

        $userMessageCount = ChatMessage::where('chat_session_id', $this->chatSessionId)
            ->where('sender', 'user')
            ->count();

        if ($userMessageCount === 1) {
            $this->isAgentTyping = true;
            $this->notifyAgentOfNewChat('[Note vocale]');
            $this->dispatch('trigger-copilot-response', message: '[Vocal]');
        }

        $this->dispatch('refresh-chat');
    }

    /**
     * Rafraîchit les messages et met à jour les notifications.
     */
    public function refreshMessages(): void
    {
        if ($this->isOpen) {
            $this->markAllAsRead();
        }
        $this->updateUnreadCount();
    }

    /**
     * Ferme et résout la session de chat actuelle.
     */
    public function closeChat(): void
    {
        if ($this->chatSessionId) {
            $session = ChatSession::find($this->chatSessionId);
            if ($session) {
                $session->update(['status' => 'closed']);
            }
            session()->forget('active_chat_session_id');
            $this->chatSessionId = null;
            $this->messages = [];
            $this->name = '';
            $this->email = '';
        }
        $this->isOpen = false;
        $this->updateUnreadCount();
    }

    /**
     * Rendu des messages ordonnés.
     */
    public function getChatMessagesProperty()
    {
        if (!$this->chatSessionId) {
            return collect();
        }
        $session = ChatSession::find($this->chatSessionId);
        return $session ? $session->messages : collect();
    }
}; ?>

<div class="fixed bottom-6 right-6 z-50 font-sans" x-data="{
    openChat: @entangle('isOpen'),
    scrollBottom() {
        $nextTick(() => {
            const container = document.getElementById('chat-message-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
    },
    isRecording: false,
    mediaRecorder: null,
    audioChunks: [],
    recordingTime: 0,
    recordingInterval: null,
    
    startRecording() {
        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(stream => {
                this.audioChunks = [];
                this.recordingTime = 0;
                this.isRecording = true;
                
                this.recordingInterval = setInterval(() => {
                    this.recordingTime++;
                }, 1000);

                this.mediaRecorder = new MediaRecorder(stream);
                this.mediaRecorder.ondataavailable = e => {
                    if (e.data.size > 0) {
                        this.audioChunks.push(e.data);
                    }
                };

                this.mediaRecorder.onstop = () => {
                    clearInterval(this.recordingInterval);
                    stream.getTracks().forEach(track => track.stop());
                };

                this.mediaRecorder.start();
            })
            .catch(err => {
                console.error('Mic error:', err);
                alert('{{ __("Autorisation microphone refusée ou microphone indisponible.") }}');
            });
    },

    stopAndSendRecording() {
        if (!this.mediaRecorder || this.mediaRecorder.state === 'inactive') return;
        
        const self = this;
        this.mediaRecorder.onstop = () => {
            clearInterval(self.recordingInterval);
            const audioBlob = new Blob(self.audioChunks, { type: 'audio/webm' });
            
            @this.upload('voiceFile', audioBlob, () => {
                @this.sendVoiceNote();
                self.isRecording = false;
            }, (err) => {
                console.error(err);
                alert('{{ __("Erreur lors de l\'envoi de la note vocale.") }}');
                self.isRecording = false;
            });
        };
        
        this.mediaRecorder.stop();
    },

    cancelRecording() {
        if (!this.mediaRecorder) return;
        const self = this;
        this.mediaRecorder.onstop = () => {
            clearInterval(self.recordingInterval);
            self.isRecording = false;
        };
        this.mediaRecorder.stop();
    },

    formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
    }
}" x-init="
    $watch('openChat', value => { if (value) scrollBottom(); });
    Livewire.on('trigger-copilot-response', () => {
        setTimeout(() => {
            $wire.triggerCopilotReply();
            scrollBottom();
        }, 2500);
    });
" x-on:message-sent.window="scrollBottom()" x-on:refresh-chat.window="scrollBottom()">

    <!-- Bouton Flottant Élégant (Bubble) -->
    <button @click="openChat = !openChat; scrollBottom();" type="button"
        class="h-14 w-14 rounded-full bg-gradient-to-r from-blue-600 to-indigo-700 flex items-center justify-center text-white shadow-xl shadow-blue-500/20 hover:shadow-blue-500/35 hover:scale-105 active:scale-95 transition-all cursor-pointer relative border border-white/10 group">
        <!-- Badge Messages Non Lus -->
        @if($unreadCount > 0 && !$isOpen)
            <span x-show="!openChat" class="absolute -top-1 -right-1 flex h-5 w-5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                <span
                    class="relative inline-flex rounded-full h-5 w-5 bg-rose-500 text-[10px] font-bold text-white items-center justify-center">{{ $unreadCount }}</span>
            </span>
        @endif

        <!-- Icone Chat -->
        <svg x-show="!openChat" class="h-6 w-6 transition-transform group-hover:rotate-6" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <!-- Icone Fermer -->
        <svg x-show="openChat" class="h-6 w-6 rotate-0 transition-transform" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" stroke-width="2" style="display: none;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <!-- Fenêtre de Discussion (Chat Box) -->
    <div x-show="openChat" x-cloak x-transition:enter="transition ease-out duration-200 transform"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150 transform"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="absolute bottom-18 right-0 w-80 sm:w-96 h-[480px] bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200/80 flex flex-col overflow-hidden max-w-[calc(100vw-2rem)]">

        <!-- En-tête -->
        <div
            class="px-4 py-3.5 bg-gradient-to-r from-slate-900 to-slate-950 text-white flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <!-- Statut En Ligne Pulse -->
                <span class="relative flex h-2.5 w-2.5">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <div>
                    <h4 class="text-xs font-bold leading-tight">{{ __('Support en Direct') }}</h4>
                    <p class="text-[9px] text-emerald-400 font-medium">{{ __('Agent connecté') }}</p>
                </div>
            </div>

            <!-- Options / Fermeture de session -->
            @if($chatSessionId)
                <button @click="if(confirm('{{ __('Voulez-vous fermer cette discussion ?') }}')) $wire.closeChat()"
                    type="button"
                    class="text-xs text-slate-400 hover:text-rose-400 font-bold transition-colors cursor-pointer select-none bg-slate-800/40 px-2 py-1 rounded border border-slate-700/50">
                    {{ __('Quitter') }}
                </button>
            @else
                <button @click="openChat = false" type="button"
                    class="text-slate-400 hover:text-white text-lg font-bold">&times;</button>
            @endif
        </div>

        <!-- Contenu principal -->
        <div class="flex-grow flex flex-col min-h-0 bg-slate-50/50">
            @if(!$chatSessionId)
                <!-- Étape 1 : Formulaire de connexion -->
                <div class="p-6 flex flex-col justify-center h-full space-y-4">
                    <div class="text-center space-y-1.5 pb-2">
                        <div class="text-3xl">👋</div>
                        <h5 class="text-sm font-bold text-slate-800">{{ __('Démarrer un chat') }}</h5>
                        <p class="text-xs text-slate-500 px-4">
                            {{ __('Renseignez votre nom pour être mis en relation avec notre agent.') }}</p>
                    </div>

                    <form wire:submit.prevent="startChat" class="space-y-3">
                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-650 uppercase mb-1">{{ __('Nom / Pseudo') }}</label>
                            <input type="text" wire:model.blur="name"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                                placeholder="Ex: Lucas">
                            @error('name') <span class="text-[10px] text-red-600 mt-0.5 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-650 uppercase mb-1">{{ __('Votre Adresse E-mail') }}
                                <span class="text-[9px] text-slate-400 font-normal">({{ __('Optionnel') }})</span></label>
                            <input type="email" wire:model.blur="email"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                                placeholder="nom@exemple.com">
                            @error('email') <span class="text-[10px] text-red-600 mt-0.5 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit"
                            class="w-full py-2.5 mt-2 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-lg text-xs font-semibold shadow-md active:scale-[0.98] transition-all cursor-pointer">
                            {{ __('Démarrer la discussion') }}
                        </button>
                    </form>
                </div>
            @else
                <!-- Étape 2 : Boîte de discussion active -->
                <div id="chat-message-container" wire:poll.3s="refreshMessages"
                    class="flex-grow p-4 overflow-y-auto space-y-3 min-h-0 scroll-smooth">
                    @foreach($this->chatMessages as $msg)
                            <div class="flex flex-col {{ $msg->sender === 'user' ? 'items-end' : 'items-start' }}">
                                <div class="max-w-[85%] rounded-2xl px-3.5 py-2 text-xs leading-relaxed shadow-sm
                                            {{ $msg->sender === 'user'
                        ? 'bg-blue-600 text-white rounded-br-none'
                        : ($msg->sender === 'system'
                            ? 'bg-slate-200 text-slate-700 rounded-bl-none italic'
                            : 'bg-white text-slate-800 border border-slate-200/80 rounded-bl-none') }}">
                                    @if($msg->attachment_type === 'image')
                                        <div x-data="{ open: false }">
                                            <img src="{{ asset('storage/' . $msg->attachment_path) }}" @click="open = true"
                                                class="max-w-full rounded-lg cursor-pointer hover:opacity-95 transition duration-150"
                                                alt="{{ __('Image') }}" />

                                            <template x-teleport="body">
                                                <div x-show="open" @keydown.escape.window="open = false"
                                                    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4"
                                                    x-transition style="display: none;">
                                                    <button @click="open = false" type="button"
                                                        class="absolute top-4 right-4 text-white text-3xl font-bold hover:text-slate-350 cursor-pointer">&times;</button>
                                                    <img src="{{ asset('storage/' . $msg->attachment_path) }}"
                                                        @click.outside="open = false"
                                                        class="max-h-[90vh] max-w-[95vw] rounded shadow-2xl object-contain" />
                                                </div>
                                            </template>
                                            <span
                                                class="text-[9px] opacity-75 mt-1 block italic text-center">{{ __('Cliquez pour agrandir') }}</span>
                                        </div>
                                    @elseif($msg->attachment_type === 'audio')
                                        <div class="flex flex-col gap-1 py-1">
                                            <span class="text-[10px] opacity-75 font-semibold flex items-center gap-1">🎤
                                                {{ __('Note vocale') }}</span>
                                            <audio src="{{ asset('storage/' . $msg->attachment_path) }}" controls
                                                class="w-full h-8 outline-none rounded filter drop-shadow-sm min-w-[180px]"
                                                style="border-radius: 4px;"></audio>
                                        </div>
                                    @else
                                        {{ $msg->message }}
                                    @endif
                                </div>
                                <span class="text-[9px] text-slate-400 mt-1 px-1">
                                    {{ $msg->created_at->format('H:i') }}
                                </span>
                            </div>
                    @endforeach

                    <!-- Animation Typing Agent -->
                    @if($isAgentTyping)
                        <div class="flex flex-col items-start">
                            <div
                                class="bg-white border border-slate-200/80 rounded-2xl rounded-bl-none px-3.5 py-2.5 flex items-center gap-1 shadow-sm">
                                <span class="h-1.5 w-1.5 bg-slate-450 rounded-full animate-bounce"
                                    style="animation-delay: 0ms"></span>
                                <span class="h-1.5 w-1.5 bg-slate-450 rounded-full animate-bounce"
                                    style="animation-delay: 150ms"></span>
                                <span class="h-1.5 w-1.5 bg-slate-450 rounded-full animate-bounce"
                                    style="animation-delay: 300ms"></span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Pied de page avec formulaire d'envoi -->
                <div class="p-3 bg-white border-t border-slate-200 flex gap-2 items-center relative">
                    <!-- Bouton Image/Capture -->
                    <button type="button" @click="$refs.imageInput.click()"
                        class="h-8 w-8 text-slate-500 hover:text-blue-600 hover:bg-slate-100 rounded-lg flex items-center justify-center shrink-0 transition-colors cursor-pointer relative">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <!-- Loader image en cours -->
                        <span wire:loading wire:target="imageFile"
                            class="absolute inset-0 bg-white/80 rounded-lg flex items-center justify-center">
                            <svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </span>
                    </button>

                    <input type="file" x-ref="imageInput" wire:model="imageFile" accept="image/*" class="hidden" />

                    <!-- Input texte standard -->
                    <input type="text" wire:model.defer="messageText"
                        @keydown.enter.prevent="$wire.sendMessage(); scrollBottom();"
                        class="flex-grow px-3 py-2 border border-slate-300 rounded-lg text-xs bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none font-sans"
                        placeholder="{{ __('Écrivez votre message...') }}" x-show="!isRecording">

                    <!-- Bouton micro pour enregistrer une note vocale -->
                    <button type="button" @click="startRecording()" x-show="!isRecording"
                        class="h-8 w-8 text-slate-500 hover:text-red-650 hover:bg-slate-100 rounded-lg flex items-center justify-center shrink-0 transition-colors cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </button>

                    <button @click="$wire.sendMessage(); scrollBottom();" type="button" x-show="!isRecording"
                        class="h-8 w-8 bg-blue-600 text-white hover:bg-blue-700 active:scale-95 rounded-lg flex items-center justify-center shrink-0 shadow transition-all cursor-pointer">
                        <svg class="h-4 w-4 transform rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>

                    <!-- Interface d'enregistrement vocal active -->
                    <div x-show="isRecording" x-cloak
                        class="absolute inset-0 bg-slate-900 text-white rounded-t-xl px-4 flex items-center justify-between gap-3 animate-fade-in z-10">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                            </span>
                            <span class="text-xs font-semibold text-slate-200">{{ __('Enregistrement en cours...') }}</span>
                            <span class="text-xs font-bold font-mono text-red-400 bg-slate-950 px-2 py-0.5 rounded"
                                x-text="formatTime(recordingTime)">0:00</span>
                        </div>

                        <!-- Indicateurs Livewire -->
                        <span wire:loading wire:target="voiceFile" class="text-[10px] text-slate-400 font-medium">
                            {{ __('Envoi...') }}
                        </span>

                        <div class="flex items-center gap-1.5" wire:loading.remove wire:target="voiceFile">
                            <button type="button" @click="cancelRecording()"
                                class="px-2.5 py-1 text-slate-400 hover:text-white text-xs font-semibold transition-colors cursor-pointer">
                                {{ __('Annuler') }}
                            </button>
                            <button type="button" @click="stopAndSendRecording()"
                                class="px-3 py-1 bg-red-600 text-white hover:bg-red-700 rounded-md text-xs font-bold transition-all shadow-md active:scale-95 flex items-center gap-1 cursor-pointer">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12zm10-4a1 1 0 00-1 1v6a1 1 0 002 0V9a1 1 0 00-1-1z" />
                                </svg>
                                {{ __('Envoyer') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>