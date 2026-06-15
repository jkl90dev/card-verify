<?php

namespace Tests\Feature;

use App\Models\VoucherRequest;
use App\Models\VoucherType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\VoucherSubmitted;
use Livewire\Livewire;
use Tests\TestCase;

class VoucherTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /**
     * Test that we can load the landing page successfully.
     */
    public function test_landing_page_renders_submit_component(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * Test successful voucher activation submission (stored in DB except for code, sent via email).
     */
    public function test_successful_activation_submission(): void
    {
        Mail::fake();
        $type = VoucherType::where('name', 'Steam Wallet Code')->first();
        
        $response = Livewire::test('submit-voucher')
            ->set('fullname', 'Test User')
            ->set('email_user', 'user@example.com')
            ->set('phone', '+33612345678')
            ->set('voucher_type_id', $type->id)
            ->set('code1', 'ABCDE-12345-FGHIJ') // Valid Steam format: 5-5-5
            ->set('code2', '')
            ->set('amount', '100€')
            ->set('acceptTerms', true)
            ->call('saveActivation');

        $response->assertHasNoErrors();
        
        // Assert a database record was created
        $this->assertEquals(1, VoucherRequest::count());
        $request = VoucherRequest::first();
        $this->assertNotNull($request);
        $this->assertEquals('activation', $request->request_type);
        $this->assertEquals('Test User', $request->fullname);
        
        // Assert the voucher code is NOT stored in the database
        $this->assertNull($request->code1);
        $this->assertNull($request->code2);

        // Assert the notification email was sent with correct data (including the code in memory)
        Mail::assertSent(VoucherSubmitted::class, function ($mail) use ($type) {
            $req = $mail->voucherRequest;
            return $req->request_type === 'activation'
                && $req->fullname === 'Test User'
                && $req->email_user === 'user@example.com'
                && $req->phone === '+33612345678'
                && $req->voucher_type_id == $type->id
                && $req->code1 === 'ABCDE-12345-FGHIJ'
                && is_null($req->code2)
                && $req->amount === '100€';
        });
    }

    /**
     * Test successful voucher refund submission (stored in DB except for code, sent via email).
     */
    public function test_successful_refund_submission(): void
    {
        Mail::fake();
        $type = VoucherType::where('name', 'Steam Wallet Code')->first();
        
        $response = Livewire::test('submit-voucher')
            ->set('refund_fullname', 'Refund User')
            ->set('refund_email_user', 'refund@example.com')
            ->set('refund_email_user_confirmation', 'refund@example.com')
            ->set('refund_voucher_type_id', $type->id)
            ->set('refund_code1', 'ABCDE-12345-FGHIJ') // Valid Steam format: 5-5-5
            ->set('refund_code2', '12345-ABCDE-67890')
            ->set('refund_amount', '250 €')
            ->set('refund_acceptTerms', true)
            ->call('saveRefund');

        $response->assertHasNoErrors();
        
        // Assert a database record was created
        $this->assertEquals(1, VoucherRequest::count());
        $request = VoucherRequest::first();
        $this->assertNotNull($request);
        $this->assertEquals('refund', $request->request_type);
        $this->assertEquals('Refund User', $request->fullname);
        
        // Assert the voucher codes are NOT stored in the database
        $this->assertNull($request->code1);
        $this->assertNull($request->code2);

        // Assert the notification email was sent with correct data (including the codes in memory)
        Mail::assertSent(VoucherSubmitted::class, function ($mail) use ($type) {
            $req = $mail->voucherRequest;
            return $req->request_type === 'refund'
                && $req->fullname === 'Refund User'
                && $req->email_user === 'refund@example.com'
                && is_null($req->phone)
                && $req->voucher_type_id == $type->id
                && $req->code1 === 'ABCDE-12345-FGHIJ'
                && $req->code2 === '12345-ABCDE-67890'
                && $req->amount === '250 €';
        });
    }

    /**
     * Test regex validation failure.
     */
    public function test_regex_validation_failure(): void
    {
        $type = VoucherType::where('name', 'Steam Wallet Code')->first();

        // Submit an invalid Steam code format (not 5-5-5)
        Livewire::test('submit-voucher')
            ->set('fullname', 'Test User')
            ->set('email_user', 'user@example.com')
            ->set('phone', '+33612345678')
            ->set('voucher_type_id', $type->id)
            ->set('code1', 'INVALID-FORMAT')
            ->set('acceptTerms', true)
            ->call('saveActivation')
            ->assertHasErrors(['code1']);
    }

    /**
     * Test language switch route and session storage.
     */
    public function test_language_switch_route(): void
    {
        $response = $this->get('/lang/en');
        $response->assertRedirect();
        $this->assertEquals('en', session('locale'));

        $response = $this->get('/lang/de');
        $response->assertRedirect();
        $this->assertEquals('de', session('locale'));
    }

    /**
     * Test language middleware applies locale correctly and renders translations.
     */
    public function test_language_middleware_applies_locale(): void
    {
        // Default locale should be 'fr' and render French text
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Comment ça fonctionne ?');
        $response->assertDontSee('How does it work?');

        // Switch locale in session and check if translation is rendered
        $response = $this->withSession(['locale' => 'en'])->get('/');
        $response->assertStatus(200);
        $response->assertSee('How does it work?');
        $response->assertDontSee('Comment ça fonctionne ?');
    }

    /**
     * Test that the email notification sent to the agent is rendered as a table.
     */
    public function test_email_notification_contains_table(): void
    {
        $type = VoucherType::where('name', 'Steam Wallet Code')->first();
        $request = VoucherRequest::create([
            'request_type' => 'activation',
            'fullname' => 'Agent Inspector',
            'email_user' => 'inspector@example.com',
            'phone' => '+33699999999',
            'voucher_type_id' => $type->id,
            'code1' => 'ABCDE-12345-FGHIJ',
            'code2' => '12345-ABCDE-67890',
            'amount' => '50€',
            'user_comment' => 'Test comment for table rendering',
            'status' => 'pending',
        ]);

        $mailable = new VoucherSubmitted($request);
        $html = $mailable->render();

        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('ID de la Demande', $html);
        $this->assertStringContainsString('Agent Inspector', $html);
        $this->assertStringContainsString('inspector@example.com', $html);
        $this->assertStringContainsString('Steam Wallet Code', $html);
        $this->assertStringContainsString('ABCDE-12345-FGHIJ', $html);
        $this->assertStringContainsString('12345-ABCDE-67890', $html);
        $this->assertStringContainsString('50€', $html);
        $this->assertStringContainsString('Test comment for table rendering', $html);
        $this->assertStringContainsString('IP du Client', $html);
    }
}

