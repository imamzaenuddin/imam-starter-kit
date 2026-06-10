<?php

use App\Models\Berita;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.public')] class extends Component {
    public Berita $berita;

    public function mount(string $slug): void
    {
        $berita = Berita::where('slug', $slug)->where('is_published', true)->first();
        
        if (!$berita) {
            abort(404);
        }

        $this->berita = $berita;
        
        // Increment views count safely
        $berita->increment('views');
    }

    public function with(): array
    {
        return [
            'terkait' => Berita::where('is_published', true)
                ->where('id', '!=', $this->berita->id)
                ->orderByDesc('tanggal_terbit')
                ->take(4)
                ->get(),
        ];
    }
};
?>

@section('title', $berita->judul)

<div class="py-5 bg-light">
    <div class="container">
        <!-- Breadcrumb / Back Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <a href="{{ route('berita.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center gap-1">
                <i class="bx bx-arrow-back"></i> Kembali ke Berita
            </a>
        </nav>

        <div class="row g-4">
            <!-- Article Body -->
            <div class="col-lg-8">
                <div class="card border-0 rounded-4 shadow-sm overflow-hidden p-4 p-md-5 bg-white">
                    <!-- Category & Title -->
                    <span class="badge rounded-pill mb-3 px-3 py-2 align-self-start
                        @if($berita->kategori === 'Berita') bg-label-primary 
                        @elseif($berita->kategori === 'Pengumuman') bg-label-warning text-dark 
                        @else bg-label-success @endif">
                        {{ $berita->kategori }}
                    </span>
                    <h1 class="fw-extrabold text-dark mb-3" style="font-size: clamp(1.8rem, 4vw, 2.5rem); line-height: 1.25;">{{ $berita->judul }}</h1>

                    <!-- Article Meta -->
                    <div class="d-flex flex-wrap align-items-center gap-3 text-muted small pb-4 mb-4 border-bottom border-light">
                        <span class="d-flex align-items-center"><i class="bx bx-user me-1"></i>{{ $berita->penulis }}</span>
                        <span class="d-flex align-items-center"><i class="bx bx-calendar me-1"></i>{{ $berita->tanggal_terbit ? $berita->tanggal_terbit->format('d M Y') : '-' }}</span>
                        <span class="d-flex align-items-center"><i class="bx bx-show me-1"></i>{{ $berita->views }} views</span>
                    </div>

                    <!-- Article Cover Image -->
                    @if ($berita->foto)
                        <div class="mb-4 rounded-4 overflow-hidden shadow-sm" style="max-height: 400px;">
                            <img src="{{ asset('storage/' . $berita->foto) }}" alt="{{ $berita->judul }}" class="w-100 h-100" style="object-fit: cover;">
                        </div>
                    @endif

                    <!-- Article Main Content -->
                    <div class="article-content text-dark fs-6" style="line-height: 1.75;">
                        {!! $berita->isi !!}
                    </div>
                </div>
            </div>

            <!-- Sidebar / Related News -->
            <div class="col-lg-4">
                <div class="card border-0 rounded-4 shadow-sm p-4 bg-white sticky-top" style="top: 100px; z-index: 10;">
                    <h5 class="fw-bold text-dark mb-3">Artikel Terbaru</h5>
                    <hr class="mt-0 mb-3 text-light">

                    <div class="d-flex flex-column gap-3">
                        @forelse ($terkait as $item)
                            <a href="{{ route('berita.detail', $item->slug) }}" class="text-decoration-none d-flex gap-3 align-items-start text-dark group"
                               onmouseover="this.querySelector('.t-title').style.color='var(--brand)';"
                               onmouseout="this.querySelector('.t-title').style.color='inherit';">
                                
                                @if ($item->foto)
                                    <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->judul }}" class="rounded-3" style="width: 70px; height: 70px; object-fit: cover; flex-shrink: 0;">
                                @else
                                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-light" style="width: 70px; height: 70px; flex-shrink: 0;">
                                        <i class="bx bx-news text-primary" style="font-size: 1.5rem; opacity: 0.5;"></i>
                                    </div>
                                @endif
                                
                                <div>
                                    <span class="badge rounded-2 small px-2 py-1 mb-1 
                                        @if($item->kategori === 'Berita') bg-label-primary 
                                        @elseif($item->kategori === 'Pengumuman') bg-label-warning text-dark 
                                        @else bg-label-success @endif" style="font-size: 0.65rem;">
                                        {{ $item->kategori }}
                                    </span>
                                    <div class="t-title fw-semibold text-line-clamp-2 small" style="line-height: 1.35; transition: color 0.2s;">
                                        {{ $item->judul }}
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;"><i class="bx bx-calendar me-1"></i>{{ $item->tanggal_terbit ? $item->tanggal_terbit->format('d M Y') : '-' }}</small>
                                </div>
                            </a>
                        @empty
                            <div class="text-muted small">Tidak ada artikel terkait lainnya.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
