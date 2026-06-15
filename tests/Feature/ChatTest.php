<?php

namespace Tests\Feature;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /**
     * Test chat dashboard access control.
     */
    public function test_chat_dashboard_access_control(): void
    {
        // Without key, it should render "Accès Refusé" (shows 'Accès Refusé' or 'Accès Refusé')
        $response = $this->get('/agent/chat-dashboard');
        $response->assertStatus(200);
        $response->assertSee('Accès Refusé');

        // With incorrect key, it should also render "Accès Refusé"
        $response = $this->get('/agent/chat-dashboard?key=wrongkey');
        $response->assertStatus(200);
        $response->assertSee('Accès Refusé');

        // With correct key, it should authorize and see "Console active"
        // In testing, env('CHAT_AGENT_KEY') defaults to 'secret123' if not set, or we can mock/set env
        config(['app.chat_agent_key' => 'secret123']); // or verify correct env value
        $response = $this->get('/agent/chat-dashboard?key=secret123');
        $response->assertStatus(200);
        $response->assertSee('Console active');
    }

    /**
     * Test starting a chat session as a user.
     */
    public function test_user_can_start_chat(): void
    {
        $this->assertEquals(0, ChatSession::count());

        $response = Livewire::test('chat-widget')
            ->set('name', 'Alice')
            ->set('email', 'alice@example.com')
            ->call('startChat');

        $response->assertHasNoErrors();
        $this->assertEquals(1, ChatSession::count());

        $session = ChatSession::first();
        $this->assertEquals('Alice', $session->user_name);
        $this->assertEquals('alice@example.com', $session->user_email);
        $this->assertEquals('active', $session->status);

        // Verify that a welcome message was automatically added
        $this->assertEquals(1, $session->messages()->count());
        $welcomeMessage = $session->messages()->first();
        $this->assertEquals('agent', $welcomeMessage->sender);
        $this->assertStringContainsString('Bonjour Alice', $welcomeMessage->message);
    }

    /**
     * Test sending user message and receiving co-pilot response.
     */
    public function test_user_can_send_message_and_get_copilot_response(): void
    {
        $session = ChatSession::create([
            'user_name' => 'Bob',
            'status' => 'active',
        ]);

        $response = Livewire::test('chat-widget')
            ->set('chatSessionId', $session->id)
            ->set('messageText', 'Hello, I need help with my Transcash card.')
            ->call('sendMessage');

        $response->assertHasNoErrors();

        // Verify message was stored in DB
        $this->assertEquals(1, ChatMessage::where('sender', 'user')->count());
        $userMsg = ChatMessage::where('sender', 'user')->first();
        $this->assertEquals('Hello, I need help with my Transcash card.', $userMsg->message);

        // Trigger co-pilot response simulation
        $response->call('triggerCopilotReply');

        // Verify co-pilot response was stored
        $this->assertEquals(2, ChatMessage::count()); // Welcome message wasn't created in this manually created session test, so total is 2
        $copilotMsg = ChatMessage::orderBy('id', 'desc')->first();
        $this->assertEquals('agent', $copilotMsg->sender);
        $this->assertStringContainsString('analyser les détails', $copilotMsg->message);
    }

    /**
     * Test agent console session selection and reply.
     */
    public function test_agent_can_reply_to_chat(): void
    {
        // Set agent key
        config(['app.chat_agent_key' => 'secret123']);

        $session = ChatSession::create([
            'user_name' => 'Charlie',
            'status' => 'active',
        ]);

        // Create user message
        $userMsg = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'user',
            'message' => 'Help me please!',
            'is_read' => false,
        ]);

        // Test dashboard loading and replying
        $response = Livewire::withQueryParams(['key' => 'secret123'])
            ->test('chat-dashboard')
            ->call('selectSession', $session->id)
            ->set('replyText', 'Hello Charlie, how can I assist you?')
            ->call('sendReply');

        $response->assertHasNoErrors();

        // Check if agent message is stored
        $this->assertEquals(2, ChatMessage::count());
        $agentMsg = ChatMessage::orderBy('id', 'desc')->first();
        $this->assertEquals('agent', $agentMsg->sender);
        $this->assertEquals('Hello Charlie, how can I assist you?', $agentMsg->message);

        // Check if user message was marked as read by agent
        $userMsg->refresh();
        $this->assertTrue((bool) $userMsg->is_read);
    }

    /**
     * Test closing a chat session.
     */
    public function test_agent_can_close_session(): void
    {
        config(['app.chat_agent_key' => 'secret123']);

        $session = ChatSession::create([
            'user_name' => 'David',
            'status' => 'active',
        ]);

        $response = Livewire::withQueryParams(['key' => 'secret123'])
            ->test('chat-dashboard')
            ->call('closeSession', $session->id);

        $response->assertHasNoErrors();

        $session->refresh();
        $this->assertEquals('closed', $session->status);
    }

    /**
     * Test user can upload and send an image.
     */
    public function test_user_can_upload_and_send_image(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $session = ChatSession::create([
            'user_name' => 'Emma',
            'status' => 'active',
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('screenshot.png');

        $response = Livewire::test('chat-widget')
            ->set('chatSessionId', $session->id)
            ->set('imageFile', $file);

        $response->assertHasNoErrors();

        // Verify message was stored in DB with attachment
        $this->assertEquals(1, ChatMessage::where('attachment_type', 'image')->count());
        $msg = ChatMessage::where('attachment_type', 'image')->first();
        $this->assertEquals('user', $msg->sender);
        $this->assertNotNull($msg->attachment_path);
        
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($msg->attachment_path);
    }

    /**
     * Test user can upload and send a voice note.
     */
    public function test_user_can_upload_and_send_voice_note(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $session = ChatSession::create([
            'user_name' => 'Gabriel',
            'status' => 'active',
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('voice.webm', 100, 'audio/webm');

        $response = Livewire::test('chat-widget')
            ->set('chatSessionId', $session->id)
            ->set('voiceFile', $file)
            ->call('sendVoiceNote');

        $response->assertHasNoErrors();

        // Verify message was stored in DB with attachment
        $this->assertEquals(1, ChatMessage::where('attachment_type', 'audio')->count());
        $msg = ChatMessage::where('attachment_type', 'audio')->first();
        $this->assertEquals('user', $msg->sender);
        $this->assertNotNull($msg->attachment_path);
        
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($msg->attachment_path);
    }

    /**
     * Test agent can upload and send an image.
     */
    public function test_agent_can_upload_and_send_image(): void
    {
        config(['app.chat_agent_key' => 'secret123']);
        \Illuminate\Support\Facades\Storage::fake('public');

        $session = ChatSession::create([
            'user_name' => 'Hugo',
            'status' => 'active',
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('agent_screenshot.jpg');

        $response = Livewire::withQueryParams(['key' => 'secret123'])
            ->test('chat-dashboard')
            ->call('selectSession', $session->id)
            ->set('imageFile', $file);

        $response->assertHasNoErrors();

        // Verify message was stored in DB
        $this->assertEquals(1, ChatMessage::where('attachment_type', 'image')->count());
        $msg = ChatMessage::where('attachment_type', 'image')->first();
        $this->assertEquals('agent', $msg->sender);
        $this->assertNotNull($msg->attachment_path);

        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($msg->attachment_path);
    }
}
