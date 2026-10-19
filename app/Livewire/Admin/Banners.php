<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Banner;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Carga y administración de los banners del televisor. Solo admin. */
#[Layout('components.layouts.app')]
#[Title('Banners')]
class Banners extends Component
{
    use RegistraBitacora, WithFileUploads;

    public ?int $bannerId = null;

    public $imagen = null;

    public string $titulo = '';

    public string $subtitulo = '';

    public string $ancho = '60';

    public string $ajuste = 'cubrir';

    public bool $activo = true;

    public ?string $aviso = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->rol->value === 'admin', 403);
    }

    /** Al elegir una imagen: informa su tamaño y avisa si la proporción se va a recortar. */
    public function updatedImagen(): void
    {
        $this->aviso = null;
        if (! $this->imagen) {
            return;
        }
        try {
            [$w, $h] = getimagesize($this->imagen->getRealPath());
            $this->aviso = "Tamaño de la imagen: {$w} × {$h} px.";
            if ($h > 0 && ($w / $h < 4.8 || $w / $h > 6.3)) {
                $this->aviso .= ' La proporción recomendada es 1880 × 340 (franja alargada); con otra proporción se recorta.';
            }
        } catch (\Throwable) {
            // sin aviso si no se puede leer
        }
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()->rol->value === 'admin', 403);

        $d = $this->validate([
            'titulo' => ['nullable', 'string', 'max:80'],
            'subtitulo' => ['nullable', 'string', 'max:160'],
            'ancho' => ['required', 'integer', 'between:20,90'],
            'ajuste' => ['required', Rule::in(['cubrir', 'contener'])],
            'imagen' => [
                $this->bannerId ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
                'dimensions:min_width=800,min_height=140',
            ],
        ], [
            'imagen.required' => 'Elige una imagen.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'Formatos permitidos: JPG, PNG o WEBP.',
            'imagen.max' => 'La imagen no debe pesar más de 4 MB.',
            'imagen.dimensions' => 'La imagen es muy pequeña (mínimo 800 × 140 px; recomendado 1880 × 340).',
        ]);

        $ruta = $this->imagen ? $this->guardarArchivo() : null;

        if ($this->bannerId) {
            $b = Banner::findOrFail($this->bannerId);
            if ($ruta) {
                $this->borrarArchivo($b->imagen);
                $b->imagen = $ruta;
            }
            $b->fill([
                'titulo' => $d['titulo'] ?: null,
                'subtitulo' => $d['subtitulo'] ?: null,
                'ancho' => (int) $d['ancho'],
                'ajuste' => $d['ajuste'],
                'activo' => $this->activo,
            ])->save();
            $this->registrar('banner_editado', 'Banner #'.$b->id.' '.($b->titulo ?: '(sin título)'));
        } else {
            $b = Banner::create([
                'titulo' => $d['titulo'] ?: null,
                'subtitulo' => $d['subtitulo'] ?: null,
                'imagen' => $ruta,
                'ancho' => (int) $d['ancho'],
                'ajuste' => $d['ajuste'],
                'activo' => $this->activo,
                'orden' => (int) Banner::max('orden') + 1,
            ]);
            $this->registrar('banner_creado', 'Banner #'.$b->id.' '.($b->titulo ?: '(sin título)'));
        }

        $this->limpiar();
    }

    public function editar(int $id): void
    {
        $b = Banner::findOrFail($id);
        $this->resetErrorBag();
        $this->bannerId = $b->id;
        $this->imagen = null;
        $this->aviso = null;
        $this->titulo = (string) $b->titulo;
        $this->subtitulo = (string) $b->subtitulo;
        $this->ancho = (string) $b->ancho;
        $this->ajuste = $b->ajuste;
        $this->activo = $b->activo;
    }

    public function cancelar(): void
    {
        $this->limpiar();
    }

    public function toggle(int $id): void
    {
        $b = Banner::findOrFail($id);
        $b->update(['activo' => ! $b->activo]);
        $this->registrar($b->activo ? 'banner_activado' : 'banner_desactivado', 'Banner #'.$b->id.' '.($b->titulo ?: '(sin título)'));
    }

    public function mover(int $id, string $direccion): void
    {
        $ids = Banner::orderBy('orden')->orderBy('id')->pluck('id')->all();
        $i = array_search($id, $ids, true);
        $j = $direccion === 'arriba' ? $i - 1 : $i + 1;

        if ($i === false || $j < 0 || $j >= count($ids)) {
            return;
        }

        [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
        foreach ($ids as $pos => $bid) {
            Banner::whereKey($bid)->update(['orden' => $pos + 1]);
        }
    }

    public function eliminar(int $id): void
    {
        $b = Banner::findOrFail($id);
        $detalle = 'Banner #'.$b->id.' '.($b->titulo ?: '(sin título)');
        $this->borrarArchivo($b->imagen);
        $b->delete();

        if ($this->bannerId === $id) {
            $this->limpiar();
        }
        $this->registrar('banner_eliminado', $detalle);
    }

    private function limpiar(): void
    {
        $this->resetErrorBag();
        $this->reset('bannerId', 'imagen', 'titulo', 'subtitulo', 'ancho', 'ajuste', 'activo', 'aviso');
    }

    /** Guarda el archivo subido en public/img/banners con un nombre aleatorio. */
    private function guardarArchivo(): string
    {
        $dir = public_path('img/banners');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $nombre = now()->format('YmdHis').'_'.Str::lower(Str::random(6)).'.'.strtolower($this->imagen->extension());
        copy($this->imagen->getRealPath(), $dir.DIRECTORY_SEPARATOR.$nombre);

        return 'img/banners/'.$nombre;
    }

    /** Solo borra archivos subidos desde este módulo; nunca los banner1.png... originales. */
    private function borrarArchivo(?string $ruta): void
    {
        if ($ruta && str_starts_with($ruta, 'img/banners/')) {
            $archivo = public_path($ruta);
            if (is_file($archivo)) {
                @unlink($archivo);
            }
        }
    }

    public function render()
    {
        $banners = Banner::orderBy('orden')->orderBy('id')->get();

        return view('livewire.admin.banners', [
            'banners' => $banners,
            'activos' => $banners->where('activo', true)->count(),
        ]);
    }
}
