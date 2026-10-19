<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Configuracion;
use App\Support\Institucional as Inst;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Control de la identidad institucional: nombre, colores y logos de todas las pantallas. Solo admin. */
#[Layout('components.layouts.app')]
#[Title('Identidad institucional')]
class ControlInstitucional extends Component
{
    use RegistraBitacora, WithFileUploads;

    /** Textos y colores en edición (clave => valor). */
    public array $v = [];

    public $escudo = null;

    public $secundario = null;

    public bool $guardado = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->rol->value === 'admin', 403);
        $this->cargar();
    }

    private function cargar(): void
    {
        foreach (array_merge(array_keys(Inst::TEXTOS), array_keys(Inst::COLORES)) as $k) {
            $this->v[$k] = Inst::get($k);
        }
    }

    public function updated(): void
    {
        $this->guardado = false;
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()->rol->value === 'admin', 403);

        $reglas = [
            'v.inst_nombre' => ['required', 'string', 'max:80'],
            'v.inst_subtitulo' => ['nullable', 'string', 'max:80'],
            'v.inst_lema' => ['nullable', 'string', 'max:80'],
            'escudo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'secundario' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];
        foreach (array_keys(Inst::COLORES) as $c) {
            $reglas["v.$c"] = ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        }

        $this->validate($reglas, [
            'v.*.regex' => 'Usa un color con el formato #RRGGBB.',
            'v.*.required' => 'Este campo es obligatorio.',
            'escudo.image' => 'El escudo debe ser una imagen.',
            'escudo.mimes' => 'Formatos permitidos: PNG, JPG o WEBP.',
            'escudo.max' => 'El archivo no debe pesar más de 2 MB.',
            'secundario.image' => 'El logo debe ser una imagen.',
            'secundario.mimes' => 'Formatos permitidos: PNG, JPG o WEBP.',
            'secundario.max' => 'El archivo no debe pesar más de 2 MB.',
        ]);

        foreach ($this->v as $clave => $valor) {
            Configuracion::guardar($clave, trim((string) $valor));
        }

        foreach (['escudo' => 'logo_escudo', 'secundario' => 'logo_secundario'] as $prop => $clave) {
            if ($this->{$prop}) {
                $ruta = $this->guardarLogo($this->{$prop});
                $this->borrarLogo(Inst::get($clave));
                Configuracion::guardar($clave, $ruta);
                $this->{$prop} = null;
            }
        }

        $this->registrar('identidad_actualizada', 'Se actualizó la identidad institucional (nombre, colores o logos)');
        $this->guardado = true;
    }

    /** Vuelve a los colores, textos y logos originales. */
    public function restaurar(): void
    {
        abort_unless(auth()->user()->rol->value === 'admin', 403);

        foreach (['logo_escudo', 'logo_secundario'] as $clave) {
            $this->borrarLogo(Inst::get($clave));
        }
        foreach (Inst::DEFAULTS as $clave => $valor) {
            Configuracion::guardar($clave, $valor);
        }

        $this->escudo = $this->secundario = null;
        $this->cargar();
        $this->resetErrorBag();
        $this->registrar('identidad_restaurada', 'Se restauraron los valores originales de la identidad institucional');
        $this->guardado = true;
    }

    private function guardarLogo($archivo): string
    {
        $dir = public_path('img/institucional');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $nombre = 'logo_'.now()->format('YmdHis').'_'.Str::lower(Str::random(5)).'.'.strtolower($archivo->extension());
        copy($archivo->getRealPath(), $dir.DIRECTORY_SEPARATOR.$nombre);

        return 'img/institucional/'.$nombre;
    }

    /** Solo borra logos subidos desde este módulo; nunca los archivos originales. */
    private function borrarLogo(?string $ruta): void
    {
        if ($ruta && str_starts_with($ruta, 'img/institucional/')) {
            $archivo = public_path($ruta);
            if (is_file($archivo)) {
                @unlink($archivo);
            }
        }
    }

    public function render()
    {
        // Colores que se ven en la vista previa (si el que se escribe aún no es válido, se usa el original)
        $col = [];
        foreach (array_keys(Inst::COLORES) as $k) {
            $x = (string) ($this->v[$k] ?? '');
            $col[$k] = Inst::valido($x) ? $x : Inst::DEFAULTS[$k];
        }

        $avisos = [];
        if (Inst::contraste($col['color_primario'], '#FFFFFF') < 4.5) {
            $avisos[] = 'El color principal es muy claro: el texto blanco se leerá con dificultad.';
        }
        if (Inst::contraste($col['color_primario_osc'], '#FFFFFF') < 4.5) {
            $avisos[] = 'El color principal oscuro es muy claro para el texto blanco.';
        }
        if (Inst::contraste($col['color_acento_claro'], $col['color_primario_osc']) < 3) {
            $avisos[] = 'El acento claro casi no se distingue sobre el color principal oscuro.';
        }

        return view('livewire.admin.control-institucional', [
            'col' => $col,
            'avisos' => $avisos,
            'logoEsc' => Inst::logo('logo_escudo'),
            'logoSec' => Inst::logo('logo_secundario'),
        ]);
    }
}
