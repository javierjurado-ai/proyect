<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Centro;
use App\Models\ComunicacionFundae;
use App\Models\CreditoFormativo;
use App\Models\Curso;
use App\Models\Empresa;
use App\Models\Formacion;
use App\Models\Persona;
use App\Models\PeticionesLogistica;
use App\Models\Presupuesto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InicioController extends Controller
{
    public function mostrarPaginaInicio(Request $request)
    {
        $personaId = Auth::user()->persona_id;

        $rol     = Rol::where('persona_id', $personaId)->value('rol')?->value;
        $persona = Persona::select('nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);

        if (! $rol || ! $persona) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['usuario' => 'No tienes acceso a la página.']);
        }

        $nombre = collect([$persona->nombre, $persona->primer_apellido, $persona->segundo_apellido])
            ->filter()
            ->implode(' ');

        // El administrador tiene su propio inicio: views/administrador/index.blade.php
        if ($rol === 'ADMINISTRADOR') {
            return view('administrador.index', array_merge(
                compact('rol', 'nombre'),
                ['primerNombre' => $persona->nombre],
                $this->datosAdministrador()
            ));
        }

        return view('panel.inicio', compact('rol', 'nombre'));
    }

    /**
     * Datos del inicio del ADMINISTRADOR: resumen de toda la plataforma.
     */
    private function datosAdministrador(): array
    {
        $hoy  = today();
        $anio = $hoy->year;

        // Cursos
        $cursosEnMarcha = Curso::whereDate('fecha_inicio', '<=', $hoy)
            ->whereDate('fecha_fin', '>=', $hoy)
            ->count();

        $cursosEstaSemana = Curso::whereBetween('fecha_inicio', [
            $hoy->copy()->startOfWeek()->toDateString(),
            $hoy->copy()->endOfWeek()->toDateString(),
        ])->count();

        // Crédito del año
        $creditosAnio      = CreditoFormativo::with('empresa')->where('año', $anio)->get();
        $creditoTotal      = (float) $creditosAnio->sum('importe_total');
        $creditoConsumido  = (float) $creditosAnio->sum('importe_consumido');
        $porcentajeCredito = $creditoTotal > 0 ? round(100 * $creditoConsumido / $creditoTotal) : 0;

        $creditoPorEmpresa = $creditosAnio
            ->sortByDesc(fn ($c) => (float) $c->importe_total)
            ->take(6)
            ->map(fn ($c) => [
                'empresa'    => $c->empresa?->nombre ?? 'Empresa #' . $c->empresa_id,
                'consumido'  => (float) $c->importe_consumido,
                'total'      => (float) $c->importe_total,
                'porcentaje' => (float) $c->importe_total > 0
                    ? round(100 * (float) $c->importe_consumido / (float) $c->importe_total)
                    : 0,
            ])
            ->values();

        // FUNDAE y logística
        $fundaePendientes = ComunicacionFundae::whereNull('fecha_envio')->count();
        $fundaeVencidas   = ComunicacionFundae::whereNull('fecha_envio')
            ->whereDate('fecha_limite', '<', $hoy)
            ->count();
        $logisticaPendiente = PeticionesLogistica::where('estado', 'PENDIENTE')->count();

        // Personal con acceso
        $rolesDeAcceso      = ['ADMINISTRADOR', 'COORDINADOR', 'COMERCIAL'];
        $personasConUsuario = Usuario::pluck('persona_id');

        // Cosas que revisar
        $personasSinUsuario = Rol::whereIn('rol', $rolesDeAcceso)
            ->whereNotIn('persona_id', $personasConUsuario)
            ->distinct()
            ->count('persona_id');

        $usuariosSinRol = Usuario::whereNotIn('persona_id', Rol::pluck('persona_id'))->count();

        $empresasSinCredito = Empresa::whereNotIn('id', $creditosAnio->pluck('empresa_id'))
            ->orderBy('nombre')
            ->pluck('nombre');

        return [
            'anio'               => $anio,
            'totalUsuarios'      => Usuario::count(),
            'cursosEnMarcha'     => $cursosEnMarcha,
            'cursosEstaSemana'   => $cursosEstaSemana,
            'totalEmpresas'      => Empresa::count(),
            'creditoTotal'       => $creditoTotal,
            'creditoConsumido'   => $creditoConsumido,
            'porcentajeCredito'  => $porcentajeCredito,
            'creditoPorEmpresa'  => $creditoPorEmpresa,

            'totalCursos'        => Curso::count(),
            'totalFormaciones'   => Formacion::count(),
            'totalAulas'         => Aula::count(),
            'totalCentros'       => Centro::count(),
            'totalAlumnos'       => Rol::where('rol', 'ALUMNO')->distinct()->count('persona_id'),
            'totalDocentes'      => Rol::where('rol', 'DOCENTE')->distinct()->count('persona_id'),

            'fundaePendientes'   => $fundaePendientes,
            'fundaeVencidas'     => $fundaeVencidas,
            'logisticaPendiente' => $logisticaPendiente,
            'totalPresupuestos'  => Presupuesto::count(),

            'totalPersonal'      => Rol::whereIn('rol', $rolesDeAcceso)->distinct()->count('persona_id'),
            'totalCreditos'      => $creditosAnio->count(),

            'personasSinUsuario' => $personasSinUsuario,
            'usuariosSinRol'     => $usuariosSinRol,
            'empresasSinCredito' => $empresasSinCredito,
        ];
    }
}