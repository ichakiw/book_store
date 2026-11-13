<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@bookstore.com'],
            [
                'name' => 'Admin BookStore',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081234567890',
                'address' => 'Gedung Pustaka Jaya Lt. 3, Jakarta Pusat',
                'status' => 'active',
            ]
        );

        $user = User::firstOrCreate(
            ['email' => 'user@bookstore.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '089876543210',
                'address' => 'Jl. Mawar Melati No. 45, Tebet, Jakarta Selatan',
                'status' => 'active',
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'siti@bookstore.com'],
            [
                'name' => 'Siti Rahmawati',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '081299887766',
                'address' => 'Jl. Anggrek Blok C2, Sukajadi, Bandung',
                'status' => 'active',
            ]
        );

        // 2. Create Categories
        $categoriesData = [
            [
                'name' => 'Pengembangan Diri',
                'description' => 'Buku-buku motivasi, pola pikir, produktivitas, dan psikologi praktis.',
            ],
            [
                'name' => 'Fiksi & Sastra',
                'description' => 'Koleksi novel pilihan, karya sastra legendaris, dan fiksi modern.',
            ],
            [
                'name' => 'Bisnis & Finansial',
                'description' => 'Strategi bisnis, pengelolaan investasi, startup, dan ekonomi.',
            ],
            [
                'name' => 'Sains & Teknologi',
                'description' => 'Pemrograman komputer, rekayasa perangkat lunak, AI, dan sains modern.',
            ],
            [
                'name' => 'Sejarah & Filsafat',
                'description' => 'Perjalanan peradaban dunia, pemikiran filosofis, dan biografi tokoh bangsa.',
            ],
            [
                'name' => 'Seni & Desain',
                'description' => 'Teori desain visual, tipografi, UI/UX, dan kreativitas estetika.',
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['name']] = Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                ]
            );
        }

        // Helper to generate SVG cover
        $makeCover = function (string $title, string $author, string $color1, string $color2, string $slug) {
            $path = "covers/{$slug}.svg";
            $fullPath = storage_path("app/public/{$path}");
            
            // Clean title for display
            $displayTitle = htmlspecialchars(Str::limit($title, 36));
            $displayAuthor = htmlspecialchars(Str::limit($author, 28));

            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 580" width="100%" height="100%">
  <defs>
    <linearGradient id="grad_{$slug}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$color1}" />
      <stop offset="100%" stop-color="{$color2}" />
    </linearGradient>
    <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="2" dy="4" stdDeviation="6" flood-opacity="0.25"/>
    </filter>
  </defs>
  
  <!-- Book Cover Background -->
  <rect width="400" height="580" fill="url(#grad_{$slug})" rx="12" />
  
  <!-- Subtle Spine Overlay -->
  <rect x="0" y="0" width="30" height="580" fill="#000000" opacity="0.18" />
  <line x1="30" y1="0" x2="30" y2="580" stroke="#ffffff" stroke-opacity="0.15" stroke-width="1.5" />
  
  <!-- Decorative Geometry -->
  <circle cx="340" cy="80" r="120" fill="#ffffff" opacity="0.06" />
  <circle cx="60" cy="500" r="100" fill="#ffffff" opacity="0.05" />
  <rect x="50" y="60" width="300" height="460" rx="8" fill="none" stroke="#ffffff" stroke-opacity="0.2" stroke-width="1.5" stroke-dasharray="4 4" />
  
  <!-- Book Badge / Header -->
  <rect x="70" y="85" width="90" height="24" rx="12" fill="#ffffff" opacity="0.2" />
  <text x="115" y="101" fill="#ffffff" font-family="'Plus Jakarta Sans', Inter, sans-serif" font-size="11" font-weight="700" letter-spacing="1" text-anchor="middle">BESTSELLER</text>
  
  <!-- Center Icon Graphic -->
  <g transform="translate(165, 170)" opacity="0.85">
    <path d="M12 60 L35 15 L58 60 Z" fill="none" stroke="#ffffff" stroke-width="3" stroke-linejoin="round"/>
    <circle cx="35" cy="40" r="10" fill="#ffffff" opacity="0.4"/>
  </g>

  <!-- Title & Author Box -->
  <rect x="65" y="270" width="270" height="170" rx="8" fill="#ffffff" opacity="0.12" />
  <text x="200" y="325" fill="#ffffff" font-family="'Plus Jakarta Sans', Inter, sans-serif" font-size="20" font-weight="800" text-anchor="middle">
    {$displayTitle}
  </text>
  <line x1="120" y1="355" x2="280" y2="355" stroke="#ffffff" stroke-opacity="0.4" stroke-width="2" />
  <text x="200" y="390" fill="#ffffff" font-family="'Plus Jakarta Sans', Inter, sans-serif" font-size="14" font-weight="500" text-anchor="middle" opacity="0.95">
    {$displayAuthor}
  </text>

  <!-- Publisher Footer -->
  <text x="200" y="500" fill="#ffffff" font-family="'Plus Jakarta Sans', Inter, sans-serif" font-size="12" font-weight="600" text-anchor="middle" letter-spacing="1.5" opacity="0.75">
    BOOKSTORE PRESS
  </text>
</svg>
SVG;
            file_put_contents($fullPath, $svg);
            return $path;
        };

        // 3. Books Data
        $booksData = [
            [
                'category' => 'Pengembangan Diri',
                'title' => 'Filosofi Teras: Stoisisme Kuno',
                'author' => 'Henry Manampiring',
                'publisher' => 'Kompas Gramedia',
                'isbn' => '978-602-412-518-9',
                'price' => 98000,
                'stock' => 24,
                'pages' => 346,
                'published_year' => 2019,
                'description' => 'Buku pengantar filsafat Stoa yang dibuat khusus untuk generasi milenial dan Gen-Z. Membantu mengatasi emosi negatif, kekhawatiran berlebih, dan melatih ketenangan batin dalam menghadapi ketidakpastian hidup.',
                'c1' => '#1e3c72', 'c2' => '#2a5298',
            ],
            [
                'category' => 'Pengembangan Diri',
                'title' => 'Atomic Habits (Edisi Indonesia)',
                'author' => 'James Clear',
                'publisher' => 'Gramedia Pustaka Utama',
                'isbn' => '978-602-063-317-6',
                'price' => 108000,
                'stock' => 18,
                'pages' => 356,
                'published_year' => 2020,
                'description' => 'Cara mudah dan terbukti untuk membangun kebiasaan baik dan menghilangkan kebiasaan buruk. James Clear menjelaskan bagaimana perubahan sekecil 1% setiap hari mampu menghasilkan transformasi besar dalam jangka panjang.',
                'c1' => '#d35400', 'c2' => '#f39c12',
            ],
            [
                'category' => 'Fiksi & Sastra',
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'publisher' => 'Bentang Pustaka',
                'isbn' => '978-979-306-279-2',
                'price' => 85000,
                'stock' => 15,
                'pages' => 529,
                'published_year' => 2005,
                'description' => 'Kisah inspiratif tentang sepuluh anak laskar pelangi di Belitung yang berjuang menggapai mimpi melalui dunia pendidikan di tengah segala keterbatasan fasilitas sekolah mereka.',
                'c1' => '#16a085', 'c2' => '#2ecc71',
            ],
            [
                'category' => 'Fiksi & Sastra',
                'title' => 'Bumi Manusia',
                'author' => 'Pramoedya Ananta Toer',
                'publisher' => 'Lentera Dipantara',
                'isbn' => '978-979-973-123-4',
                'price' => 135000,
                'stock' => 10,
                'pages' => 535,
                'published_year' => 2006,
                'description' => 'Mahakarya sastra Indonesia yang menceritakan pergulatan batin Minke, pemuda pribumi terpelajar di era kolonial Hindia Belanda, dan perjuangan cintanya bersama Annelies Mellema.',
                'c1' => '#8e44ad', 'c2' => '#9b59b6',
            ],
            [
                'category' => 'Bisnis & Finansial',
                'title' => 'The Psychology of Money',
                'author' => 'Morgan Housel',
                'publisher' => 'Baca Publishing',
                'isbn' => '978-602-648-657-8',
                'price' => 95000,
                'stock' => 30,
                'pages' => 268,
                'published_year' => 2021,
                'description' => 'Pelajaran abadi mengenai kekayaan, ketamakan, dan kebahagiaan. Morgan Housel mengupas bagaimana perilaku manusia jauh lebih menentukan kesuksesan finansial daripada rumus matematika atau kecerdasan IQ.',
                'c1' => '#2c3e50', 'c2' => '#3498db',
            ],
            [
                'category' => 'Bisnis & Finansial',
                'title' => 'Zero to One: Startup & Masa Depan',
                'author' => 'Peter Thiel',
                'publisher' => 'Gramedia Pustaka Utama',
                'isbn' => '978-602-031-482-2',
                'price' => 89000,
                'stock' => 12,
                'pages' => 220,
                'published_year' => 2016,
                'description' => 'Catatan berharga dari pendiri PayPal tentang bagaimana membangun perusahaan yang menciptakan hal baru (0 ke 1) daripada sekadar meniru apa yang sudah ada di pasar (1 ke n).',
                'c1' => '#c0392b', 'c2' => '#e74c3c',
            ],
            [
                'category' => 'Sains & Teknologi',
                'title' => 'Clean Code: Panduan Engineer',
                'author' => 'Robert C. Martin',
                'publisher' => 'Prentice Hall',
                'isbn' => '978-013-235-088-4',
                'price' => 165000,
                'stock' => 8,
                'pages' => 464,
                'published_year' => 2008,
                'description' => 'Buku rujukan wajib bagi setiap programmer profesional untuk menulis kode yang bersih, mudah dibaca, mudah diuji, dan dapat dipelihara dalam jangka panjang tanpa menimbulkan technical debt.',
                'c1' => '#0052d4', 'c2' => '#4364f7',
            ],
            [
                'category' => 'Sains & Teknologi',
                'title' => 'Arsitektur Web Modern & Laravel',
                'author' => 'Dian Pratama',
                'publisher' => 'Informatika Digital',
                'isbn' => '978-602-889-112-7',
                'price' => 120000,
                'stock' => 20,
                'pages' => 380,
                'published_year' => 2023,
                'description' => 'Membahas arsitektur aplikasi web modern, best practices MVC, RESTful API, pengujian otomatis, dan optimasi performa backend menggunakan ekosistem Laravel terkini.',
                'c1' => '#e94057', 'c2' => '#f27121',
            ],
            [
                'category' => 'Sejarah & Filsafat',
                'title' => 'Sapiens: Riwayat Umat Manusia',
                'author' => 'Yuval Noah Harari',
                'publisher' => 'Kepustakaan Populer Gramedia',
                'isbn' => '978-602-424-416-3',
                'price' => 140000,
                'stock' => 14,
                'pages' => 512,
                'published_year' => 2017,
                'description' => 'Penjelajahan komprehensif tentang sejarah kemunculan spesies Homo Sapiens dari zaman batu hingga abad revolusi bioteknologi dan kecerdasan buatan masa kini.',
                'c1' => '#434343', 'c2' => '#000000',
            ],
            [
                'category' => 'Fiksi & Sastra',
                'title' => 'Laut Bercerita',
                'author' => 'Leila S. Chudori',
                'publisher' => 'KPG',
                'isbn' => '978-602-424-694-5',
                'price' => 105000,
                'stock' => 22,
                'pages' => 394,
                'published_year' => 2017,
                'description' => 'Kisah fiksi berlatar sejarah tentang para aktivis mahasiswa 1998 yang diculik dan dihilangkan, serta jeritan hati keluarga yang tak pernah berhenti mencari kepastian.',
                'c1' => '#005c97', 'c2' => '#363795',
            ],
            [
                'category' => 'Seni & Desain',
                'title' => 'Prinsip UI/UX Modern',
                'author' => 'Reza Nugraha',
                'publisher' => 'Desain Grafika Press',
                'isbn' => '978-602-778-901-2',
                'price' => 115000,
                'stock' => 16,
                'pages' => 280,
                'published_year' => 2022,
                'description' => 'Panduan visual holistik mengenai desain antarmuka, tata letak grid, psikologi warna, hierarki tipografi, dan perancangan pengalaman pengguna yang menyenangkan serta intuitif.',
                'c1' => '#833ab4', 'c2' => '#fd1d1d',
            ],
            [
                'category' => 'Pengembangan Diri',
                'title' => 'Bicara Itu Ada Seninya',
                'author' => 'Oh Su Hyang',
                'publisher' => 'Bhuana Ilmu Populer',
                'isbn' => '978-602-455-390-6',
                'price' => 88000,
                'stock' => 25,
                'pages' => 238,
                'published_year' => 2018,
                'description' => 'Rahasia komunikasi persuasif dari pakar komunikasi Korea Selatan. Mempelajari seni memikat pendengar, bernegosiasi yang santun, dan menyampaikan ide dengan percaya diri.',
                'c1' => '#11998e', 'c2' => '#38ef7d',
            ],
        ];

        $createdBooks = [];
        foreach ($booksData as $b) {
            $cat = $categories[$b['category']] ?? null;
            $slug = Str::slug($b['title']);
            $coverPath = $makeCover($b['title'], $b['author'], $b['c1'], $b['c2'], $slug);

            $book = Book::firstOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $cat?->id,
                    'title' => $b['title'],
                    'author' => $b['author'],
                    'publisher' => $b['publisher'],
                    'isbn' => $b['isbn'],
                    'price' => $b['price'],
                    'stock' => $b['stock'],
                    'pages' => $b['pages'],
                    'published_year' => $b['published_year'],
                    'description' => $b['description'],
                    'cover_image' => $coverPath,
                    'is_active' => true,
                ]
            );
            $createdBooks[] = $book;
        }

        // 4. Sample Orders to demonstrate full status flow
        $sampleOrders = [
            [
                'status' => 'menunggu_pembayaran',
                'books' => [0, 1],
                'qty' => [1, 2],
                'payment_proof' => null,
                'notes' => 'Tolong bungkus rapi dengan bubble wrap.',
            ],
            [
                'status' => 'menunggu_konfirmasi',
                'books' => [4, 6],
                'qty' => [1, 1],
                'payment_proof' => 'sample_proof.jpg',
                'notes' => 'Sudah ditransfer melalui BCA Mobile.',
            ],
            [
                'status' => 'diproses',
                'books' => [2, 3],
                'qty' => [1, 1],
                'payment_proof' => 'sample_proof.jpg',
                'notes' => 'Packing kayu bila memungkinkan.',
            ],
            [
                'status' => 'dikirim',
                'books' => [7],
                'qty' => [1],
                'payment_proof' => 'sample_proof.jpg',
                'courier_name' => 'JNE Express (Reguler)',
                'tracking_number' => 'JNE889920192831',
                'notes' => 'Kirim ke alamat kantor.',
            ],
            [
                'status' => 'selesai',
                'books' => [8, 9],
                'qty' => [1, 1],
                'payment_proof' => 'sample_proof.jpg',
                'courier_name' => 'SiCepat BEST',
                'tracking_number' => '003928172651',
                'notes' => 'Buku sudah diterima dengan baik.',
            ],
            [
                'status' => 'pembayaran_ditolak',
                'books' => [5],
                'qty' => [1],
                'payment_proof' => 'sample_proof.jpg',
                'payment_note' => 'Nominal transfer tidak sesuai dengan tagihan pesanan (kurang Rp 15.000 untuk ongkir). Silakan upload bukti transfer yang benar.',
                'notes' => null,
            ],
        ];

        foreach ($sampleOrders as $idx => $s) {
            $subtotal = 0;
            $itemsData = [];
            foreach ($s['books'] as $i => $bookIdx) {
                $book = $createdBooks[$bookIdx] ?? $createdBooks[0];
                $quantity = $s['qty'][$i] ?? 1;
                $lineSubtotal = $book->price * $quantity;
                $subtotal += $lineSubtotal;
                $itemsData[] = [
                    'book_id' => $book->id,
                    'book_title' => $book->title,
                    'book_author' => $book->author,
                    'book_cover' => $book->cover_image,
                    'price' => $book->price,
                    'quantity' => $quantity,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $shippingCost = 15000;
            $grandTotal = $subtotal + $shippingCost;

            $order = Order::create([
                'order_number' => 'INV-' . date('Ymd') . '-000' . ($idx + 1),
                'user_id' => $user->id,
                'total_amount' => $subtotal,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'status' => $s['status'],
                'recipient_name' => 'Budi Santoso',
                'recipient_phone' => '089876543210',
                'shipping_address' => 'Jl. Mawar Melati No. 45, RT 03 / RW 05, Kel. Tebet Barat',
                'city' => 'Jakarta Selatan',
                'postal_code' => '12810',
                'notes' => $s['notes'] ?? null,
                'courier_name' => $s['courier_name'] ?? null,
                'tracking_number' => $s['tracking_number'] ?? null,
                'shipped_at' => in_array($s['status'], ['dikirim', 'selesai']) ? now()->subDay() : null,
                'completed_at' => $s['status'] === 'selesai' ? now() : null,
                'payment_method' => 'Transfer Bank BCA',
                'payment_proof' => $s['payment_proof'] ?? null,
                'payment_note' => $s['payment_note'] ?? null,
                'paid_at' => in_array($s['status'], ['menunggu_konfirmasi', 'diproses', 'dikirim', 'selesai', 'pembayaran_ditolak']) ? now()->subHours(5) : null,
                'stock_reduced' => in_array($s['status'], ['diproses', 'dikirim', 'selesai']),
            ]);

            foreach ($itemsData as $item) {
                $order->items()->create($item);
            }
        }
    }
}
