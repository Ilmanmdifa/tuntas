<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TodoTest — Feature test untuk Todo per-user (Paket C Quality-First)
 * Pattern: HTTP Feature Test (panggil route seperti browser, cek status + content)
 * Why RefreshDatabase? Tiap test DB fresh (migrate:fresh) → tidak bocor antar test, isolasi terjamin
 * Why factory? User::factory() buat user dummy cepat; Todo via create (tidak ada factory)
 * Avoid: jangan pakai Todo::factory jika belum ada; jangan lupa actingAs untuk login
 */
class TodoTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_todos(): void
    {
        // Guest (belum login) → GET /todos harus ditolak sebelum controller
        // routes/web.php:15 middleware('auth') → 302 redirect ke /login
        $response = $this->get('/todos');

        $response->assertRedirect('/login');
    }

    public function test_user_only_sees_own_todos(): void
    {
        // Buat 2 user + 2 todos beda owner
        $u1 = User::factory()->create(['name' => 'Ilman']);
        $u2 = User::factory()->create(['name' => 'Budi']);
        Todo::create(['title' => 'Milik u1', 'user_id' => $u1->id, 'is_done' => false]);
        Todo::create(['title' => 'Milik u2', 'user_id' => $u2->id, 'is_done' => false]);

        // Login sebagai u1 → GET /todos harus lihat milik sendiri saja
        // Bukti scoping TodoController.php:21 where('user_id', auth()->id())
        $response = $this->actingAs($u1)->get('/todos');

        $response->assertStatus(200);
        $response->assertSee('Milik u1');
        $response->assertDontSee('Milik u2');
    }

    public function test_store_requires_title(): void
    {
        // Validasi FormRequest StoreTodoRequest.php:26 title required|max:255
        // POST kosong harus gagal validasi (302 back + session error, bukan 201)
        $u1 = User::factory()->create();
        $response = $this->actingAs($u1)->post('/todos', ['title' => '']);

        $response->assertSessionHasErrors('title');
    }

    public function test_policy_blocks_other_user(): void
    {
        // Todo milik u2, login u1 coba PATCH → harus 403
        // Bukti TodoPolicy.php:38 update => $user->id === $todo->user_id
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $todo = Todo::create(['title' => 'Milik u2', 'user_id' => $u2->id, 'is_done' => false]);

        $response = $this->actingAs($u1)->patch("/todos/{$todo->id}", ['title' => 'Hacked']);

        $response->assertStatus(403);
    }

    public function test_assigned_user_can_see_and_update(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $u3 = User::factory()->create();
        $todo = Todo::create(['title' => 'Tugas titipan', 'user_id' => $u1->id, 'assigned_to' => $u2->id, 'is_done' => false]);

        // u2 (penerima) → lihat di /todos
        $this->actingAs($u2)->get('/todos')->assertSee('Tugas titipan');

        // u2 → update title (Policy canManage lapis assigned)
        $this->actingAs($u2)->patch("/todos/{$todo->id}", ['title' => 'Judul baru'])->assertStatus(302); // 302 atau 200, bukan 403

        // u3 (orang luar) → tidak lihat + update ditolak
        $this->actingAs($u3)->get('/todos')->assertDontSee('Tugas titipan');
        $this->actingAs($u3)->patch("/todos/{$todo->id}", ['title' => 'Hacked'])->assertStatus(403);
    }

    public function test_notif_sent_on_assign(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $todo = Todo::create(['title' => 'Tugas baru', 'user_id' => $u1->id, 'is_done' => false]);

        $this->actingAs($u1)->patch("/todos/{$todo->id}", [
            'assigned_to' => $u2->id
        ])->assertStatus(302); // lihat test lain: update sukses = 302, bukan 403

        $this->assertCount(1, $u2->fresh()->unreadNotifications);
        $this->assertEquals(
            'App\Notifications\TodoAssignedNotification',
            $u2->fresh()->unreadNotifications->first()->type
        );
    }

    public function test_search_per_user(): void
    {
        // Search ?q= per-user + when() di TodoController.php:22
        $u1 = User::factory()->create();
        Todo::create(['title' => 'Belajar PHP', 'user_id' => $u1->id, 'is_done' => false]);
        Todo::create(['title' => 'Main bola', 'user_id' => $u1->id, 'is_done' => false]);

        $response = $this->actingAs($u1)->get('/todos?q=Belajar');

        $response->assertStatus(200);
        $response->assertSee('Belajar PHP');
        $response->assertDontSee('Main bola');
    }

    public function test_search_retains_q_on_page(): void
    {
        // appends(['q'=>...]) di TodoController.php:25 → ?q tetap di links() page 2
        $u1 = User::factory()->create();
        for ($i = 1; $i <= 6; $i++) {
            Todo::create(['title' => "Todo $i", 'user_id' => $u1->id, 'is_done' => false]);
        }

        $response = $this->actingAs($u1)->get('/todos?q=Todo&page=2');

        $response->assertStatus(200);
        // paginate appends harus bawa q ke HTML links
        $this->assertStringContainsString('q=Todo', $response->content());
    }

    public function test_store_defaults_priority_medium(): void
    {
        $u1 = User::factory()->create();
        $this->actingAs($u1)->post('/todos', ['title' => 'Tanpa priority'])->assertStatus(302);

        $this->assertDatabaseHas('todos', ['title' => 'Tanpa priority', 'priority' => 'medium']);
    }

    public function test_store_rejects_invalid_priority(): void
    {
        $u1 = User::factory()->create();
        $this->actingAs($u1)->post('/todos', ['title' => 'Ngaco', 'priority' => 'urgent'])
            ->assertSessionHasErrors('priority');
    }

    public function test_show_needs_owner_or_member(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $todo = Todo::create(['title' => 'Rahasia', 'user_id' => $u1->id, 'is_done' => false]);

        $this->actingAs($u1)->get("/todos/{$todo->id}")->assertStatus(200);
        $this->actingAs($u2)->get("/todos/{$todo->id}")->assertStatus(403);
    }

    public function test_mark_read_clears_badge(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $todo = Todo::create(['title' => 'Tugas baru', 'user_id' => $u1->id, 'is_done' => false]);

        $this->actingAs($u1)->patch("/todos/{$todo->id}", ['assigned_to' => $u2->id])->assertStatus(302);

        $notifId = $u2->fresh()->unreadNotifications->first()->id;

        $this->actingAs($u2)->post("/notifications/{$notifId}/read")->assertStatus(302);

        $this->assertCount(0, $u2->fresh()->unreadNotifications);
    }

    public function test_overdue_filter(): void
    {
        $u1 = User::factory()->create();
        Todo::create(['title' => 'Telat', 'user_id' => $u1->id, 'is_done' => false, 'due_date' => now()->subDay()->toDateString()]);
        Todo::create(['title' => 'Besok', 'user_id' => $u1->id, 'is_done' => false, 'due_date' => now()->addDay()->toDateString()]);
        Todo::create(['title' => 'Telat tapi done', 'user_id' => $u1->id, 'is_done' => true, 'due_date' => now()->subDay()->toDateString()]);

        $response = $this->actingAs($u1)->get('/todos?filter=overdue');

        $response->assertStatus(200);
        $response->assertSee('Telat');
        $response->assertDontSee('Besok');
        $response->assertDontSee('Telat tapi done');
    }

    public function test_search_finds_description(): void
    {
        $u1 = User::factory()->create();
        Todo::create(['title' => 'Rapat', 'description' => 'bawa laptop', 'user_id' => $u1->id, 'is_done' => false]);
        Todo::create(['title' => 'Main bola', 'user_id' => $u1->id, 'is_done' => false]);

        $response = $this->actingAs($u1)->get('/todos?q=laptop');

        $response->assertStatus(200);
        $response->assertSee('Rapat');
        $response->assertDontSee('Main bola');
    }
}
