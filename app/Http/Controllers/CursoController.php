<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Centro;
use App\Models\Formacion;
use App\Models\Presupuesto;
use App\Models\PeticionesLogistica;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    // Mostrar listado de cursos
    public function index()
    {
        $cursos = Curso::with(['formacion', 'centro', 'peticionLogistica', 'presupuesto'])->get();
        return view('cursos.index', compact('cursos'));
    }

    // Mostrar formulario de creación
    public function create()
    {
        $centros = Centro::all();
        $formaciones = Formacion::all();
        $presupuestos = Presupuesto::all();
        $peticiones = PeticionesLogistica::all();
        
        return view('cursos.create', compact('centros', 'formaciones', 'presupuestos', 'peticiones'));
    }

    // Guardar un nuevo curso en la base de datos
    public function store(Request $request)
    {
        $validated = $request->validate([
            'curso_idcurso' => 'required|exists:formacion,idcurso',
            'codigo_grupo' => 'required|string|max:45|unique:curso,codigo_grupo',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'centro_id' => 'required|exists:centro,id',
            'turno' => 'required',
        ]);

        Curso::create($request->all());

        return redirect()->route('cursos.index')->with('success', 'Curso creado exitosamente.');
    }

    // Mostrar detalles de un curso
    public function show($idgrupo)
    {
        $curso = Curso::with(['formacion', 'centro', 'peticionLogistica', 'presupuesto'])->findOrFail($idgrupo);
        return view('cursos.show', compact('curso'));
    }

    // Mostrar el formulario de edición con los datos actuales del curso
    public function edit($idgrupo)
    {
        $curso = Curso::findOrFail($idgrupo);
        $centros = Centro::all();
        $formaciones = Formacion::all();
        $presupuestos = Presupuesto::all();
        $peticiones = PeticionesLogistica::all();
        
        return view('cursos.edit', compact('curso', 'centros', 'formaciones', 'presupuestos', 'peticiones'));
    }

    // Procesar la actualización del curso en la base de datos
    public function update(Request $request, $idgrupo)
    {
        $curso = Curso::findOrFail($idgrupo);

        $validated = $request->validate([
            'curso_idcurso' => 'required|exists:formacion,idcurso',
            // Excluimos el ID actual de la validación unique para que no choque consigo mismo
            'codigo_grupo' => 'required|string|max:45|unique:curso,codigo_grupo,' . $idgrupo . ',idgrupo',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'centro_id' => 'required|exists:centro,id',
            'turno' => 'required',
        ]);

        $curso->update($request->all());

        return redirect()->route('cursos.index')->with('success', 'Curso actualizado exitosamente.');
    }

    // Eliminar un curso
    public function destroy($idgrupo)
    {
        $curso = Curso::findOrFail($idgrupo);
        $curso->delete();

        return redirect()->route('cursos.index')->with('success', 'Curso eliminado correctamente.');
    }
}