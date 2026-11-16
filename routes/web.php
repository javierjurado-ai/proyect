<?php

use Illuminate\Support\Facades\Route;
use App\Models\PeticionesLogistica;
use App\Http\Controllers\CentroController;
use App\Http\Controllers\ComunicacionFundaeController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FormacionController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\CreditoFormativoController;
use App\Http\Controllers\CursoFormularioController;
use App\Http\Controllers\PresupuestoController;



use App\Http\Controllers\PeticionesLogisticaController;
Route::get('/', function () {
    return view('curso.formulario');
});

Route::get('/probar-logistica', function () {
    return PeticionesLogistica::all();
});


Route::get('/empresa', function () {
    return view('empresa.formulario');
});


Route::resource('cursos', CursoController::class);

// Rutas para el controlador de persona
Route::get('/persona/listar', [PersonaController::class, 'listarPersona']);
Route::get('/persona/{id}', [PersonaController::class, 'obtenerUsuarioPorID']);
Route::post('/persona', [PersonaController::class, 'agregarPersona']);
Route::put('/persona/{id}', [PersonaController::class, 'actualizarPersona']);
Route::delete('/persona/{id}', [PersonaController::class, 'eliminarPersona']);

// Rutas para el controlador de comunicaciones FUNDAE
Route::get('/comunicacionFundae/listar', [ComunicacionFundaeController::class, 'listarComunicacionesFundae']);
Route::get('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'obtenerComunicacionFundaePorID']);
Route::post('/comunicacionFundae', [ComunicacionFundaeController::class, 'agregarComunicacionFundae']);
Route::put('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'actualizarComunicacionFundae']);
Route::delete('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'eliminarComunicacionFundae']);

// Formulario de creación
Route::get('/empresa', [EmpresaController::class, 'mostrarFormulario']);
Route::post('/empresa', [EmpresaController::class, 'agregarEmpresa']);

// Vista general (Listado con tabla)
Route::get('/empresas/listar', [EmpresaController::class, 'listarEmpresa'])->name('empresa.index');

// Vista de detalle / consulta por ID (ej: /empresa/1)
Route::get('/empresa/{id}', [EmpresaController::class, 'obtenerEmpresaPorID'])->name('empresa.show');
Route::get('/empresa/{id}/editar', [EmpresaController::class, 'vistaEditar'])->name('empresa.editar');

// Rutas de actualización y eliminación web
Route::put('/empresa/{id}', [EmpresaController::class, 'actualizarEmpresa'])->name('empresa.actualizar');
Route::delete('/empresa/{id}', [EmpresaController::class, 'eliminarEmpresa'])->name('empresa.eliminar');

// Rutas para el controlador de formacion
Route::get('/formacion', [FormacionController::class, 'listarFormacion']);
Route::get('/formacion/{id}', [FormacionController::class, 'obtenerFormacionPorID']);
Route::post('/formacion', [FormacionController::class, 'crearFormacion'])->name('formacion.agregarFormacion');
Route::put('/formacion/{id}', [FormacionController::class, 'actualizarFormacion']);
Route::delete('/formacin/{id}', [FormacionController::class, 'eliminarFormacion']);

// Rutas para el controlador de centro
Route::get('/centro/listar', [CentroController::class, 'listarCentro']);
Route::get('/centro/{id}', [CentroController::class, 'obtenerCentroPorID']);
Route::post('/centro', [CentroController::class, 'agregarCentro'])->name('centro.agregarCentro');
Route::put('/centro/{id}', [CentroController::class, 'actualizarCentro']);
Route::delete('/centro/{id}', [CentroController::class, 'eliminarCentro']);

// Rutas para el controlador de créditos formativos
Route::get('/credito', [CreditoFormativoController::class, 'listarCredito']);
Route::get('/credito/{id}', [CreditoFormativoController::class, 'obtenerCreditoPorID']);
Route::post('/credito', [CreditoFormativoController::class, 'crearCredito']);
Route::put('/credito/{id}', [CreditoFormativoController::class, 'actualizarCredito']);
Route::delete('/credito/{id}', [CreditoFormativoController::class, 'eliminarcredito']);


// Rutas para el controlador de peticiones logistica
Route::get('/peticionesLogistica/listar', [PeticionesLogisticaController::class, 'listarPeticionesLogistica']);
Route::get('/peticionesLogistica/{id}', [PeticionesLogisticaController::class, 'obtenerPeticionLogisticaPorID']);
Route::post('/peticionesLogistica', [PeticionesLogisticaController::class, 'agregarPeticionLogistica']);
Route::put('/peticionesLogistica/{id}', [PeticionesLogisticaController::class, 'actualizarPeticionLogistica']);
Route::delete('/peticionesLogistica/{id}', [PeticionesLogisticaController::class, 'eliminarPeticionLogistica']);

// Rutas para el controlador de curso
Route::get('/curso/listar', [CursoController::class, 'listarCurso']);
Route::get('/curso/{id}', [CursoController::class, 'obtenerCursoPorID']);
Route::post('/curso', [CursoController::class, 'agregarCurso'])->name('curso.agregarCurso');
Route::put('/curso/{id}', [CursoController::class, 'actualizarCurso']);
Route::delete('/curso/{id}', [CursoController::class, 'eliminarCurso']);

//Rutas para el controlador de presupuesto
Route::get('/presupuesto/listar', [PresupuestoController::class, 'listarPresupuesto']);
Route::get('/presupuesto/{id}', [PresupuestoController::class, 'obtenerPresupuestoPorID'])->whereNumber('id');
Route::post('/presupuesto', [PresupuestoController::class, 'agregarPresupuesto']);
Route::put('/presupuesto/{id}', [PresupuestoController::class, 'actualizarPresupuesto'])->whereNumber('id');
Route::delete('/presupuesto/{id}', [PresupuestoController::class, 'eliminarPresupuesto'])->whereNumber('id');




Route::post('/view/formularioPersona', [PersonaController::class, 'store'])->name('persona.store');

//Views//


Route::post('/view/formularioPresupuesto', [PresupuestoController::class, 'store'])->name('presupuesto.store');
Route::get('/view/formularioPresupuesto/crear', [PresupuestoController::class, 'create'])->name('presupuesto.create');
Route::get('/view/formularioPersona/crear', [PersonaController::class, 'create'])->name('persona.create');

Route::get('/view/formulario/crearCurso', [CursoController::class, 'crearFormulario'])->name('curso.crearFormulario');
Route::get('/view/formulario/crearTablaCurso', [CursoController::class, 'crearTabla'])->name('curso.crearTablaCurso');

Route::get('/view/formulario/crearCentro', [CentroController::class, 'crearFormulario'])->name('centro.crearFormulario');

Route::get('/view/formulario/crearFormacion', [FormacionController::class, 'crearFormulario'])->name('formacion.crearFormulario');

Route::get('/view/listadoFormacion', [FormacionController::class, 'index'])->name('formacion.index');
Route::get('/view/listadoFormacion', [FormacionController::class, 'index'])->name('formacion.index');
Route::post('/view/formularioFormacion', [FormacionController::class, 'store'])->name('formacion.store');

Route::get('/view/listadoPresupuesto', [PresupuestoController::class, 'index'])->name('presupuesto.index');

