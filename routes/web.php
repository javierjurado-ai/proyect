<?php

use Illuminate\Support\Facades\Route;
use App\Models\PeticionesLogistica;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CentroController;
use App\Http\Controllers\ComunicacionFundaeController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FormacionController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\CreditoFormativoController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\PeticionesLogisticaController;

/*
|--------------------------------------------------------------------------
| Rutas públicas (solo para quien NO ha iniciado sesión)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/persona/iniciarSesion', [AuthController::class, 'mostrarInicioSesion'])->name('login');
Route::post('/persona/iniciarSesion', [AuthController::class, 'iniciarSesion']);
});


      


/*
|--------------------------------------------------------------------------
| Rutas protegidas (si no hay sesión, redirige a la ruta "login")
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return view('curso.formulario');
    });

    Route::get('/probar-logistica', function () {
        return PeticionesLogistica::all();
    });

    Route::resource('cursos', CursoController::class);

 

    // Rutas para el controlador de persona
    Route::get('/persona/listar', [PersonaController::class, 'listarPersona']);
    Route::get('/persona/{id}', [PersonaController::class, 'obtenerUsuarioPorID']);
    
    Route::put('/persona/{id}', [PersonaController::class, 'actualizarPersona']);
    Route::delete('/persona/{id}', [PersonaController::class, 'eliminarPersona']);

    // Rutas para el controlador de comunicaciones FUNDAE
    Route::get('/comunicacionFundae/listar', [ComunicacionFundaeController::class, 'listarComunicacionesFundae']);
    Route::get('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'obtenerComunicacionFundaePorID']);
    Route::post('/comunicacionFundae', [ComunicacionFundaeController::class, 'agregarComunicacionFundae']);
    Route::put('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'actualizarComunicacionFundae']);
    Route::delete('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'eliminarComunicacionFundae']);



    // Rutas para el controlador de formacion
    Route::get('/formacion', [FormacionController::class, 'listarFormacion']);
    Route::get('/formacion/{id}', [FormacionController::class, 'obtenerFormacionPorID']);
    Route::post('/formacion', [FormacionController::class, 'crearFormacion'])->name('formacion.agregarFormacion');
    Route::put('/formacion/{id}', [FormacionController::class, 'actualizarFormacion']);
    Route::delete('/formacion/{id}', [FormacionController::class, 'eliminarFormacion']);

    
    
   
    

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
    
    Route::put('/curso/{id}', [CursoController::class, 'actualizarCurso']);
   
    Route::post('/curso/rolDocente/{id}', [CursoController::class, 'agregarRolDocente']);
    Route::post('/curso/CursoDocente/{id}', [CursoController::class, 'agregarCursoDocente']);
    Route::post('/curso/rolAlumno/{id}', [CursoController::class, 'agregarRolAlumno']);
    Route::post('/curso/CursoAlumno/{id}', [CursoController::class, 'agregarCursoAlumno']);

    // Rutas para el controlador de presupuesto
    Route::get('/presupuesto/listar', [PresupuestoController::class, 'listarPresupuesto']);
    Route::get('/presupuesto/{id}', [PresupuestoController::class, 'obtenerPresupuestoPorID'])->whereNumber('id');
    Route::post('/presupuesto', [PresupuestoController::class, 'agregarPresupuesto']);
    Route::put('/presupuesto/{id}', [PresupuestoController::class, 'actualizarPresupuesto'])->whereNumber('id');
    Route::delete('/presupuesto/{id}', [PresupuestoController::class, 'eliminarPresupuesto'])->whereNumber('id');

    // API JSON de Contactos de Centro
    Route::get('/centro-contactos/listar', [CentroController::class, 'listarContactos']);
    Route::get('/centro-contactos/{id}', [CentroController::class, 'obtenerContactoPorID']);
    Route::post('/api/centro-contactos', [CentroController::class, 'agregarContacto']);
    Route::put('/centro-contactos/{id}', [CentroController::class, 'actualizarContacto']);
    Route::delete('/centro-contactos/{id}', [CentroController::class, 'eliminarContacto']);

    Route::post('/view/formularioPersona', [PersonaController::class, 'store'])->name('persona.store');

    // Views
    Route::get('/view/listadoPresupuesto', [PresupuestoController::class, 'index'])->name('presupuesto.index');
    Route::post('/view/formularioPresupuesto', [PresupuestoController::class, 'store'])->name('presupuesto.store');
    Route::get('/view/formulario/crearPresupuesto', [PresupuestoController::class, 'create'])->name('presupuesto.create');
    Route::get('/view/formularioPersona/crear', [PersonaController::class, 'create'])->name('persona.create');

    
    
    Route::get('/view/formulario/crearTablaDocente/{id}', [CursoController::class, 'crearTablaPorDocente'])->name('curso.crearTablaPorDocente');
    Route::get('/view/formulario/crearTablaAlumno/{id}', [CursoController::class, 'crearTablaPorAlumno'])->name('curso.crearTablaPorAlumno');
    Route::get('/view/formulario/crearAula', [CursoController::class, 'crearFormularioAula'])->name('aula.create');
    Route::post('/view/formularioAula', [CursoController::class, 'guardarAula'])->name('aula.store');
    
    

    
    Route::get('/view/formulario/crearFormacion', [FormacionController::class, 'crearFormulario'])->name('formacion.crearFormulario');

    Route::get('/view/listadoFormacion', [FormacionController::class, 'index'])->name('formacion.index');
    Route::post('/view/formularioFormacion', [FormacionController::class, 'store'])->name('formacion.store');




    

    // Formulario web de Contactos de Centro
    Route::get('/view/formulario/crearContactoCentro', [CentroController::class, 'crearContactoFormulario'])->name('centro_contactos.create');
    Route::post('/view/formularioContactoCentro', [CentroController::class, 'storeContacto'])->name('centro_contactos.store');

    // Rutas web para la gestión de Cursos (Editar, Actualizar y Eliminar)
    Route::get('/view/formulario/crearTablaCurso', [CursoController::class, 'crearTabla'])->name('curso.crearTablaCurso');
    Route::get('/view/formulario/editarCurso/{id}', [CursoController::class, 'editarCursoFormulario'])->name('curso.edit')->whereNumber('id');
    Route::put('/view/formulario/actualizarCurso/{id}', [CursoController::class, 'actualizarCursoWeb'])->name('curso.update')->whereNumber('id');
    Route::delete('/curso/{id}', [CursoController::class, 'eliminarCursoWeb'])->name('curso.destroy')->whereNumber('id');
    Route::get('/view/formulario/crearCurso', [CursoController::class, 'crearFormulario'])->name('curso.crearFormulario');
    Route::post('/view/formularioCurso', [CursoController::class, 'agregarCurso'])->name('curso.agregarCurso');
    

    // Rutas web para la gestión de Centros (Editar, Actualizar y Eliminar)
    Route::get('/view/listadoCentro', [CentroController::class, 'index'])->name('centro.index');
    Route::get('/view/formulario/crearCentro', [CentroController::class, 'crearFormulario'])->name('centro.crearFormulario');
    Route::post('/view/formulario/crearCentro', [CentroController::class, 'store'])->name('centro.store');
    Route::get('/view/formulario/editarCentro/{id}', [CentroController::class, 'editarCentroFormulario'])->name('centro.edit')->whereNumber('id');
    Route::put('/view/formulario/actualizarCentro/{id}', [CentroController::class, 'actualizarCentroWeb'])->name('centro.update')->whereNumber('id');
    Route::delete('/centro/{id}', [CentroController::class, 'eliminarCentroWeb'])->name('centro.destroy')->whereNumber('id');

    
    // Rutas web para la gestión de Empresas (Editar, Actualizar y Eliminar)
    Route::get('/view/listadoEmpresa', [EmpresaController::class, 'index'])->name('empresa.index');
    Route::get('/view/formulario/crearEmpresa', [EmpresaController::class, 'mostrarFormulario'])->name('empresa.create');
    Route::post('/view/formulario/crearEmpresa', [EmpresaController::class, 'agregarEmpresa'])->name('empresa.store');
    Route::get('/view/formulario/editarEmpresa/{id}', [EmpresaController::class, 'vistaEditar'])->name('empresa.editar')->whereNumber('id');
    Route::put('/view/formulario/actualizarEmpresa/{id}', [EmpresaController::class, 'actualizarEmpresaWeb'])->name('empresa.actualizar')->whereNumber('id');
    Route::delete('/empresa/{id}', [EmpresaController::class, 'eliminarEmpresaWeb'])->name('empresa.eliminar')->whereNumber('id');

});