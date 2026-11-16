<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Curso;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use App\Models\Centro;

class CursoFormularioController extends Controller
{
    public function create()
    {
        $centros = Centro::orderBy('nombre')->pluck('nombre', 'id');
        return view('curso.formulario', compact('centros')); // la vista de tu formulario
    }
    public function store(Request $request)
    {
        $datos = $request->validate([
            'codigoCurso'      => ['required', 'integer'],
            'empresaFormativa' => ['required', 'string', 'max:255'],
            // ...resto de reglas
        ]);

        $curso = Curso::create($datos);

        if (! $this->enviarCurso($curso)) {
            return back()
                ->withInput()
                ->withErrors(['api' => 'El curso se guardó, pero no se pudo enviar.']);
        }

        return redirect()->route('cursos.create')->with('status', 'Curso guardado y enviado');
    }
    private function enviarCurso(Curso $curso): bool
    {
        try {
            $respuesta = Http::acceptJson()
                ->timeout(10)
                ->post('http://127.0.0.1:8000/curso', $curso->toArray());
        } catch (ConnectionException $e) {
            return false;
        }
        return $respuesta->successful();
    }
}
