<?php

use App\Models\Berita;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.public')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $kategoriFilter = '';

    public function filterKategori(string $kategori): void
    {
        $this->kategoriFilter = $kategori;
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'beritas' => Berita::query()
                ->where('is_published', true)
                ->when($this->search, function ($q) {
                    $q->where('judul', 'like', '%' . $this->search . '%')
                      ->orWhere('ringkasan', 'like', '%' . $this->search . '%');
                })
                ->when($this->kategoriFilter, function ($q) {
                    $q->where('kategori', $this->kategoriFilter);
                })
                ->orderByDesc('tanggal_terbit')
                ->orderByDesc('id')
                ->paginate(6),
        ];
    }
};
?>

@section('title', 'Berita & Artikel Terkini')

<div>
    <!-- Hero Header -->
    <div class="py-5" style="background: linear-gradient(135deg, #1e40af 0%, #0f172a 100%); color: #fff; margin-bottom: 3rem;">
        <div class="container py-4 text-center">
            <h1 class="fw-extrabold display-4 mb-2 text-white">Berita & Informasi</h1>
            <p class="text-white-50 fs-5 max-width-600 mx-auto">Temukan artikel terbaru, pengumuman penting, dan rincian kegiatan seputar Imam-StarterKit.</p>
        </div>
    </div>

    <div class="container">
        <!-- Search and Filter Bar -->
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bx bx-search text-muted"></i></span>
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 ps-0" placeholder="Cari berita atau artikel...">
                </div>
            </div>
            <div class="col-md-4">
                <select wire:change="filterKategori($event.target.value)" class="form-select">
                    <option value="">Semua Kategori</option>
                    <option value="Berita">Berita</option>
                    <option value="Pengumuman">Pengumuman</option>
                    <option value="Kegiatan">Kegiatan</option>
                </select>
            </div>
        </div>

        <!-- News Cards Grid -->
        <div class="row g-4">
            @forelse ($beritas as $item)
                <div class="col-md-4 col-sm-6">
                    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden" style="transition: transform 0.3s, box-shadow 0.3s; cursor: pointer;"
                         onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 1rem 3rem rgba(0,0,0,.1)';"
                         onmouseout="this.style.transform='none'; this.style.boxShadow='0 .125rem .25rem rgba(0,0,0,.075)';">
                        
                        <!-- Image Cover -->
                        <div class="position-relative" style="height: 200px;">
                            @if ($item->foto)
                                <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->judul }}" class="w-100 h-100" style="object-fit: cover;">
                            @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #eff6ff, #dbeafe);">
                                    <i class="bx bx-news text-primary" style="font-size: 3.5rem; opacity: 0.35;"></i>
                                </div>
                            @endif
                            <!-- Category Badge -->
                            <span class="position-absolute top-0 start-0 m-3 badge rounded-3 px-3 py-2 
                                @if($item->kategori === 'Berita') bg-primary 
                                @elseif($item->kategori === 'Pengumuman') bg-warning text-dark 
                                @else bg-success @endif">
                                {{ $item->kategori }}
                            </span>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex align-items-center gap-2 mb-2 text-muted small">
                                <span class="d-flex align-items-center"><i class="bx bx-calendar me-1"></i>{{ $item->tanggal_terbit ? $item->tanggal_terbit->format('d M Y') : '-' }}</span>
                                <span class="d-flex align-items-center"><i class="bx bx-show me-1"></i>{{ $item->views }} views</span>
                            </div>
                            <h5 class="card-title fw-bold text-dark mb-2 text-line-clamp-2" style="font-size: 1.15rem; line-height: 1.4;">
                                <a href="{{ route('berita.detail', $item->slug) }}" class="text-decoration-none text-dark">{{ $item->judul }}</a>
                            </h5>
                            <p class="card-text text-muted small text-line-clamp-3 mb-4">{{ $item->ringkasan }}</p>
                            
                            <div class="mt-auto d-flex align-items-center justify-content-between pt-3 border-top border-light">
                                <span class="text-muted small"><i class="bx bx-user me-1"></i>{{ $item->penulis }}</span>
                                <a href="{{ route('berita.detail', $item->slug) }}" class="btn btn-sm btn-primary rounded-pill px-3">Baca →</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 py-5 text-center text-muted">
                    <i class="bx bx-news display-3 mb-3 d-block opacity-25"></i>
                    <h4>Tidak Ada Berita</h4>
                    <p class="text-muted">Tidak ada artikel yang cocok dengan pencarian Anda saat ini.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-5">
            {{ $beritas->links() }}
        </div>
    </div>
</div>
