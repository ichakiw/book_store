<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookStoreWorkflowTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_home_and_catalog_pages_render_successfully(): void
    {
        // Home page renders successfully
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('BookStore');

        // Login page renders
        $responseLogin = $this->get('/login');
        $responseLogin->assertStatus(200);
        $responseLogin->assertSee('Selamat Datang Kembali');

        // Catalog page renders
        $responseCatalog = $this->get('/buku');
        $responseCatalog->assertStatus(200);
        $responseCatalog->assertSee('Katalog Buku');
    }

    public function test_cart_does_not_decrement_book_stock(): void
    {
        $user = User::where('role', 'user')->first();
        $book = Book::where('is_active', true)->where('stock', '>', 5)->first();
        $initialStock = $book->stock;

        // User adds book to cart
        $response = $this->actingAs($user)->post('/keranjang/tambah/'.$book->id, [
            'quantity' => 2,
        ]);

        $response->assertRedirect('/keranjang');

        // Verify stock in books table has NOT changed
        $this->assertEquals($initialStock, $book->fresh()->stock);

        // Verify cart has the item
        $cartItem = Cart::where('user_id', $user->id)->where('book_id', $book->id)->first();
        $this->assertNotNull($cartItem);
        $this->assertEquals(2, $cartItem->quantity);
    }

    public function test_full_purchase_and_order_flow_with_stock_deduction(): void
    {
        Storage::fake('public');

        $user = User::where('role', 'user')->first();
        $admin = User::where('role', 'admin')->first();
        $book = Book::where('is_active', true)->where('stock', '>=', 5)->first();
        $initialStock = $book->stock;

        // 1. User adds to cart
        $this->actingAs($user)->post('/keranjang/tambah/'.$book->id, ['quantity' => 2]);
        $this->assertEquals($initialStock, $book->fresh()->stock);

        // 2. User checks out
        $checkoutResponse = $this->actingAs($user)->post('/checkout', [
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '089876543210',
            'shipping_address' => 'Jl. Mawar Tebet No. 12',
            'city' => 'Jakarta Selatan',
            'postal_code' => '12810',
            'payment_method' => 'Transfer Bank BCA',
            'notes' => 'Tolong kirim siang hari',
        ]);

        $order = Order::where('shipping_address', 'Jl. Mawar Tebet No. 12')->first();
        $this->assertNotNull($order);
        $this->assertEquals('menunggu_pembayaran', $order->status);
        $this->assertFalse($order->stock_reduced);
        // Stock must still be untouched!
        $this->assertEquals($initialStock, $book->fresh()->stock);

        // 3. User uploads payment proof
        $file = UploadedFile::fake()->create('bukti_transfer.jpg', 100, 'image/jpeg');
        $uploadResponse = $this->actingAs($user)->post("/pesanan/{$order->order_number}/pembayaran", [
            'payment_proof' => $file,
        ]);
        $this->assertEquals('menunggu_konfirmasi', $order->fresh()->status);
        $this->assertEquals($initialStock, $book->fresh()->stock);

        // 4. Admin approves / confirms payment -> Stock decrements NOW
        $confirmResponse = $this->actingAs($admin)->post("/admin/pesanan/{$order->id}/konfirmasi-pembayaran");
        $confirmResponse->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('diproses', $order->status);
        $this->assertTrue($order->stock_reduced);
        // Stock MUST be decremented by 2
        $this->assertEquals($initialStock - 2, $book->fresh()->stock);

        // 5. Admin ships order with tracking number
        $shipResponse = $this->actingAs($admin)->post("/admin/pesanan/{$order->id}/update-pengiriman", [
            'courier_name' => 'JNE Express',
            'tracking_number' => 'JNE991827361',
        ]);
        $order->refresh();
        $this->assertEquals('dikirim', $order->status);
        $this->assertEquals('JNE991827361', $order->tracking_number);

        // 6. User confirms receipt -> status selesai
        $receivedResponse = $this->actingAs($user)->post("/pesanan/{$order->order_number}/diterima");
        $order->refresh();
        $this->assertEquals('selesai', $order->status);
        $this->assertNotNull($order->completed_at);
    }

    public function test_deleting_book_does_not_break_historical_orders(): void
    {
        $admin = User::where('role', 'admin')->first();
        $user = User::where('role', 'user')->first();

        // Create an order with an item
        $book = Book::where('is_active', true)->first();
        $order = Order::create([
            'order_number' => 'INV-TEST-DELETEREF',
            'user_id' => $user->id,
            'total_amount' => $book->price,
            'shipping_cost' => 15000,
            'grand_total' => $book->price + 15000,
            'status' => 'selesai',
            'recipient_name' => 'Test',
            'recipient_phone' => '08123',
            'shipping_address' => 'Jl. Test',
            'city' => 'Jakarta',
            'postal_code' => '12345',
            'payment_method' => 'Transfer Bank BCA',
            'stock_reduced' => true,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $book->id,
            'book_title' => $book->title,
            'book_author' => $book->author,
            'book_cover' => $book->cover_image,
            'price' => $book->price,
            'quantity' => 1,
            'subtotal' => $book->price,
        ]);

        // Admin deletes book
        $this->actingAs($admin)->delete("/admin/buku/{$book->id}");

        // Book is soft deleted
        $this->assertSoftDeleted('books', ['id' => $book->id]);

        // User views order detail page - MUST succeed with 200 OK without any error
        $response = $this->actingAs($user)->get("/pesanan/{$order->order_number}");
        $response->assertStatus(200);
        $response->assertSee($book->title);
    }

    public function test_order_invoice_can_be_viewed_by_customer_and_admin(): void
    {
        $admin = User::where('role', 'admin')->first();
        $user = User::where('role', 'user')->first();
        $book = Book::where('is_active', true)->first();

        $order = Order::create([
            'order_number' => 'INV-TEST-INVOICE',
            'user_id' => $user->id,
            'total_amount' => $book->price,
            'shipping_cost' => 15000,
            'grand_total' => $book->price + 15000,
            'status' => 'selesai',
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '089876543210',
            'shipping_address' => 'Jl. Tebet Barat',
            'city' => 'Jakarta Selatan',
            'postal_code' => '12810',
            'payment_method' => 'Transfer Bank BCA',
            'stock_reduced' => true,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $book->id,
            'book_title' => $book->title,
            'book_author' => $book->author,
            'book_cover' => $book->cover_image,
            'price' => $book->price,
            'quantity' => 1,
            'subtotal' => $book->price,
        ]);

        // 1. Owner customer can view invoice
        $customerResponse = $this->actingAs($user)->get("/pesanan/{$order->order_number}/invoice");
        $customerResponse->assertStatus(200);
        $customerResponse->assertSee($order->order_number);
        $customerResponse->assertSee($book->title);

        // 2. Admin can also view customer invoice without 404
        $adminResponse = $this->actingAs($admin)->get("/pesanan/{$order->order_number}/invoice");
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee($order->order_number);
        $adminResponse->assertSee($book->title);

        // 3. Guest can also view invoice with order number
        $guestResponse = $this->get("/pesanan/{$order->order_number}/invoice");
        $guestResponse->assertStatus(200);
        $guestResponse->assertSee($order->order_number);
    }

    public function test_category_duplicate_name_and_description_are_rejected(): void
    {
        $admin = User::where('role', 'admin')->first();

        // 1. Existing category
        $existing = Category::first();

        // 2. Try creating category with duplicate name
        $responseDuplicateName = $this->actingAs($admin)->post('/admin/kategori', [
            'name' => $existing->name,
            'description' => 'Deskripsi baru yang unik',
        ]);
        $responseDuplicateName->assertSessionHasErrors('name');

        // 3. Try creating category with duplicate description
        $existing->update(['description' => 'Deskripsi Unik Kategori Khusus']);

        $responseDuplicateDesc = $this->actingAs($admin)->post('/admin/kategori', [
            'name' => 'Nama Kategori Baru Unik',
            'description' => 'Deskripsi Unik Kategori Khusus',
        ]);
        $responseDuplicateDesc->assertSessionHasErrors('description');
    }

    public function test_book_duplicate_title_and_isbn_are_rejected(): void
    {
        $admin = User::where('role', 'admin')->first();
        $existingBook = Book::where('is_active', true)->first();

        // 1. Try creating a book with duplicate title
        $responseDuplicateTitle = $this->actingAs($admin)->post('/admin/buku', [
            'title' => $existingBook->title,
            'author' => 'Penulis Lain',
            'price' => 50000,
            'stock' => 10,
        ]);
        $responseDuplicateTitle->assertSessionHasErrors('title');

        // 2. Set an ISBN on existing book and try creating with same ISBN
        $existingBook->update(['isbn' => '978-602-03-8888-1']);

        $responseDuplicateIsbn = $this->actingAs($admin)->post('/admin/buku', [
            'title' => 'Judul Buku Baru Banget',
            'author' => 'Penulis Unik',
            'isbn' => '978-602-03-8888-1',
            'price' => 60000,
            'stock' => 5,
        ]);
        $responseDuplicateIsbn->assertSessionHasErrors('isbn');
    }

    public function test_admin_access_control_rules(): void
    {
        $user = User::where('role', 'user')->first();

        // 1. Unauthenticated user (guest) to admin dashboard -> 401 Unauthorized
        $responseGuest = $this->get('/admin/dashboard');
        $responseGuest->assertStatus(401);
        $responseGuest->assertSee('Unauthorized');

        // 2. Logged in customer to admin dashboard -> 403 Forbidden (Access Denied)
        $responseUser = $this->actingAs($user)->get('/admin/dashboard');
        $responseUser->assertStatus(403);
        $responseUser->assertSee('Access Denied');
    }

    public function test_chat_between_user_and_admin(): void
    {
        $user = User::where('role', 'user')->first();
        $admin = User::where('role', 'admin')->first();

        // 1. Customer visits chat page
        $chatPage = $this->actingAs($user)->get('/chat');
        $chatPage->assertStatus(200);

        // 2. Customer sends a message
        $sendResponse = $this->actingAs($user)->postJson('/chat/messages', [
            'message' => 'Halo admin, apakah buku Atomic Habits masih ready stock?',
        ]);
        $sendResponse->assertStatus(200);
        $sendResponse->assertJson(['success' => true]);

        // Verify message stored in DB
        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $user->id,
            'sender_id' => $user->id,
            'message' => 'Halo admin, apakah buku Atomic Habits masih ready stock?',
            'is_read' => false,
        ]);

        // 3. Admin visits admin chat dashboard
        $adminChatPage = $this->actingAs($admin)->get('/admin/chat?user_id='.$user->id);
        $adminChatPage->assertStatus(200);
        $adminChatPage->assertSee('Halo admin, apakah buku Atomic Habits');

        // 4. Admin replies to customer
        $replyResponse = $this->actingAs($admin)->postJson('/admin/chat/'.$user->id.'/messages', [
            'message' => 'Halo kak Budi, stok Atomic Habits ready banyak siap kirim ya!',
        ]);
        $replyResponse->assertStatus(200);
        $replyResponse->assertJson(['success' => true]);

        // Verify admin reply stored
        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $user->id,
            'sender_id' => $admin->id,
            'message' => 'Halo kak Budi, stok Atomic Habits ready banyak siap kirim ya!',
        ]);

        // 5. Customer fetches messages via JSON polling API
        $pollResponse = $this->actingAs($user)->getJson('/chat/messages');
        $pollResponse->assertStatus(200);
        $pollResponse->assertJsonFragment([
            'message' => 'Halo kak Budi, stok Atomic Habits ready banyak siap kirim ya!',
        ]);
    }

    public function test_custom_pagination_is_rendered(): void
    {
        $items = collect(range(1, 20));
        $paginator = new LengthAwarePaginator($items->forPage(1, 5), $items->count(), 5, 1);
        $html = view('pagination.custom', ['paginator' => $paginator, 'elements' => [$paginator->getUrlRange(1, 4)]])->render();
        $this->assertStringContainsString('custom-pagination-nav', $html);
        $this->assertStringContainsString('custom-page-link', $html);
    }
}
