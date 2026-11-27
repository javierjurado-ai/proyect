<?php

use App\Events\EnviarMensaje;
use Illuminate\Support\Facades\Route;
use App\Models\PeticionesLogistica;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\CentroController;
use App\Http\Controllers\ComunicacionFundaeController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FormacionController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\CreditoFormativoController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\PeticionesLogisticaController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\MovimientoAlmacenController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\FacturaEmpresaProveedoraController;
use App\Http\Controllers\MensajeController;
use App\Models\PeticionMensajes;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Rutas públicas (solo para quien NO ha iniciado sesión)
|--------------------------------------------------------------------------
*/

Route::get('/persona/iniciarSesion', [AuthController::class, 'mostrarInicioSesion'])->name('login');
Route::post('/persona/iniciarSesion', [AuthController::class, 'iniciarSesion']);


Route::post('/persona/crearUsuario', [AuthController::class, 'crearUsuario'])->name('persona.crearUsuario');


/*
|--------------------------------------------------------------------------
| Rutas protegidas (si no hay sesión, redirige a la ruta "login")
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::view('/chat', 'chat')->middleware('auth')->name('chat');

    Route::post('/mensajes', function (Request $request) {
        $datos = $request->validate(['mensaje' => 'required|string|max:500']);

        $peticion = PeticionMensajes::create([
            'mensaje'      => $datos['mensaje'],
            'fecha_creada' => now()->toDateString(),
            'hora_creada'  => now()->toTimeString(),
            'persona_id'   => $request->user()->persona_id,
        ]);

        EnviarMensaje::dispatch($peticion);

        return response()->noContent();
    })->middleware('auth')->name('mensajes');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // La portada lleva al inicio de cada rol
    Route::get('/', fn() => redirect()->route('inicio.index'));

    // Inicio: cada rol ve el suyo
    Route::get('/view/inicio', [InicioController::class, 'mostrarPaginaInicio'])->name('inicio.index');

    Route::get('/cursos/{curso}/tickets/{ticket?}', [MensajeController::class, 'visualizarTicket'])
        ->name('ticket.index');

    Route::post('/cursos/{curso}/tickets/{ticket}/mensajes', [MensajeController::class, 'enviarMensaje'])
        ->name('ticket.mensajes.store');
    /*
    |----------------------------------------------------------------------
    | Formación, empresas y administración (todos menos LOGISTA)
    |----------------------------------------------------------------------
    */
    Route::middleware('rol:ADMINISTRADOR,COORDINADOR,COMERCIAL')->group(function () {
        Route::get('/probar-logistica', function () {
            return PeticionesLogistica::all();
        });




        // Rutas para el controlador de persona
        Route::get('/persona/listar', [PersonaController::class, 'listarPersona']);
        Route::get('/persona/{id}', [PersonaController::class, 'obtenerUsuarioPorID']);

        Route::put('/persona/{id}', [PersonaController::class, 'actualizarPersona']);
        Route::delete('/persona/{id}', [PersonaController::class, 'eliminarPersona']);
        Route::post('/persona', [PersonaController::class, 'agregarPersona'])->name('persona.crearPersona');
        Route::get('/persona/{curso}/crear', [PersonaController::class, 'create'])->name('persona.crear');

        // Rutas para el controlador de comunicaciones FUNDAE
        Route::get('/comunicacionFundae/listar', [ComunicacionFundaeController::class, 'listarComunicacionesFundae']);
        Route::get('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'obtenerComunicacionFundaePorID']);
        Route::post('/comunicacionFundae', [ComunicacionFundaeController::class, 'agregarComunicacionFundae']);
        Route::put('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'actualizarComunicacionFundae']);
        Route::delete('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'eliminarComunicacionFundae']);

        // Rutas para el controlador de créditos formativos
        Route::get('/credito', [CreditoFormativoController::class, 'listarCredito']);
        Route::get('/credito/{id}', [CreditoFormativoController::class, 'obtenerCredito']);
        Route::post('/credito', [CreditoFormativoController::class, 'crearCredito']);
        Route::put('/credito/{id}', [CreditoFormativoController::class, 'actualizarCredito']);
        Route::delete('/credito/{id}', [CreditoFormativoController::class, 'eliminarCredito']);


        // Rutas para el controlador de curso
        Route::get('/curso/listar', [CursoController::class, 'listarCurso']);
        Route::get('/curso/{id}', [CursoController::class, 'obtenerCursoPorID']);

        Route::put('/curso/{id}', [CursoController::class, 'actualizarCurso']);

        Route::post('/curso/rolDocente/{id}', [CursoController::class, 'agregarRolDocente']);
        Route::post('/curso/CursoDocente/{id}', [CursoController::class, 'agregarCursoDocente']);
        Route::post('/curso/rolAlumno/{id}', [CursoController::class, 'agregarRolAlumno']);
        Route::post('/curso/CursoAlumno/{id}', [CursoController::class, 'agregarCursoAlumno']);

        // API JSON de Contactos de Centro
        Route::get('/centro-contactos/listar', [CentroController::class, 'listarContactos']);
        Route::get('/centro-contactos/{id}', [CentroController::class, 'obtenerContactoPorID']);
        Route::post('/api/centro-contactos', [CentroController::class, 'agregarContacto']);
        Route::put('/centro-contactos/{id}', [CentroController::class, 'actualizarContacto']);
        Route::delete('/centro-contactos/{id}', [CentroController::class, 'eliminarContacto']);

        Route::post('/rol/{id}', [CentroController::class, 'agregarRolCoordinador']);

        Route::get('/view/persona', [PersonaController::class, 'index'])->name('persona.index');
        Route::get('/view/formularioPersona', [PersonaController::class, 'create'])->name('persona.crearFormulario');
        Route::put('/persona/{id}', [PersonaController::class, 'update'])->name('persona.update');


        Route::get('/view/formularioPersona/crear', [PersonaController::class, 'create'])->name('persona.create');
        Route::post('/view/formularioPersona', [PersonaController::class, 'store'])->name('persona.store');

        // Views

        Route::get('/view/formulario/crearTablaDocente/{id}', [CursoController::class, 'crearTablaPorDocente'])->name('curso.crearTablaPorDocente');
        Route::get('/view/formulario/crearTablaAlumno/{id}', [CursoController::class, 'crearTablaPorAlumno'])->name('curso.crearTablaPorAlumno');

        // Formulario web de Contactos de Centro
        Route::get('/view/formulario/crearContactoCentro', [CentroController::class, 'crearContactoFormulario'])->name('centro_contactos.create');
        Route::post('/view/formularioContactoCentro', [CentroController::class, 'storeContacto'])->name('centro_contactos.store');

        // Rutas web para la gestión de Cursos (Editar, Actualizar y Eliminar)
        Route::get('/view/formulario/crearTablaCurso', [CursoController::class, 'crearTabla'])->name('curso.crearTablaCurso');
        Route::get('/view/curso/{curso}', [CursoController::class, 'visualizarCurso'])->name('curso.visualizar');
        Route::get('/view/formulario/editarCurso/{id}', [CursoController::class, 'editarCursoFormulario'])->name('curso.edit')->whereNumber('id');
        Route::put('/view/formulario/actualizarCurso/{id}', [CursoController::class, 'actualizarCursoWeb'])->name('curso.update')->whereNumber('id');
        Route::delete('/curso/{id}', [CursoController::class, 'eliminarCursoWeb'])->name('curso.destroy')->whereNumber('id');
        Route::get('/view/formulario/crearCurso', [CursoController::class, 'crearFormulario'])->name('curso.crearFormulario');
        Route::post('/view/formularioCurso', [CursoController::class, 'agregarCurso'])->name('curso.agregarCurso');
        Route::delete('/curso/{curso}/alumno/{cursoAlumno}', [CursoController::class, 'eliminarCursoAlumno'])->name('curso.alumno.destroy');
        Route::get('/curso/{curso}/alumnos', [CursoController::class, 'agregarAlumnos'])->name('curso.alumno.create');
        Route::post('/curso/{curso}/alumnos', [CursoController::class, 'guardarAlumnos'])->name('curso.alumno.store');
        Route::get('/curso/{curso}/docentes', [CursoController::class, 'agregarDocentes'])->name('curso.docente.create');
        Route::post('/curso/{curso}/docentes', [CursoController::class, 'guardarDocentes'])->name('curso.docente.store');
        Route::get('/curso/{curso}/calendario/crear', [CursoController::class, 'crearFormularioDia'])->name('curso.dia.create');
        Route::post('/curso/{curso}/calendario', [CursoController::class, 'guardarDia'])->name('curso.calendario.store');
        Route::post('/curso/{curso}/calendario/generar', [CursoController::class, 'generarDias'])->name('curso.calendario.generar');
        Route::get('/curso/{curso}/calendario/{dia}/editar', [CursoController::class, 'editarFormularioDia'])->name('curso.calendario.edit')->whereNumber('dia');
        Route::put('/curso/{curso}/calendario/{dia}', [CursoController::class, 'actualizarDia'])->name('curso.calendario.update')->whereNumber('dia');
        Route::delete('/curso/{curso}/calendario/{dia}', [CursoController::class, 'cancelarDia'])->name('curso.calendario.destroy')->whereNumber('dia');

        // Rutas web para la gestión de Centros (Editar, Actualizar y Eliminar)
        Route::get('/view/listadoCentro', [CentroController::class, 'index'])->name('centro.index');
        Route::get('/view/formulario/crearCentro', [CentroController::class, 'crearFormulario'])->name('centro.crearFormulario');
        Route::post('/view/formulario/crearCentro', [CentroController::class, 'store'])->name('centro.store');
        Route::get('/view/formulario/editarCentro/{id}', [CentroController::class, 'editarCentroFormulario'])->name('centro.edit')->whereNumber('id');
        Route::put('/view/formulario/actualizarCentro/{id}', [CentroController::class, 'actualizarCentroWeb'])->name('centro.update')->whereNumber('id');
        Route::delete('/centro/{id}', [CentroController::class, 'eliminarCentroWeb'])->name('centro.destroy')->whereNumber('id');

        // Rutas para la visualización detallada y pestañas de Centros
        Route::get('/view/centro/{centro}', [CentroController::class, 'visualizarCentro'])->name('centro.visualizar');
        Route::get('/view/centro/{centro}/aulas', [CentroController::class, 'crearTablaPorAula'])->name('centro.aulas');

        // Rutas para el controlador de comunicaciones FUNDAE
        Route::get('/comunicacionFundae/listar', [ComunicacionFundaeController::class, 'listarComunicacionesFundae']);
        Route::get('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'obtenerComunicacionFundaePorID']);
        Route::post('/comunicacionFundae', [ComunicacionFundaeController::class, 'agregarComunicacionFundae']);
        Route::put('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'actualizarComunicacionFundae']);
        Route::delete('/comunicacionFundae/{id}', [ComunicacionFundaeController::class, 'eliminarComunicacionFundae']);

        // Rutas para el controlador de créditos formativos
        Route::get('/credito', [CreditoFormativoController::class, 'listarCredito']);
        Route::get('/credito/{id}', [CreditoFormativoController::class, 'obtenerCredito']);
        Route::post('/credito', [CreditoFormativoController::class, 'crearCredito']);
        Route::put('/credito/{id}', [CreditoFormativoController::class, 'actualizarCredito']);
        Route::delete('/credito/{id}', [CreditoFormativoController::class, 'eliminarCredito']);

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

        // API JSON de Contactos de Centro
        Route::get('/centro-contactos/listar', [CentroController::class, 'listarContactos']);
        Route::get('/centro-contactos/{id}', [CentroController::class, 'obtenerContactoPorID']);
        Route::post('/api/centro-contactos', [CentroController::class, 'agregarContacto']);
        Route::put('/centro-contactos/{id}', [CentroController::class, 'actualizarContacto']);
        Route::delete('/centro-contactos/{id}', [CentroController::class, 'eliminarContacto']);

        // Rutas web para el controlador de persona
        Route::get('/persona/listar', [PersonaController::class, 'listarPersona']);
        Route::get('/persona/{id}', [PersonaController::class, 'obtenerUsuarioPorID']);
        Route::put('/persona/{id}', [PersonaController::class, 'update'])->name('persona.update');
        Route::delete('/persona/{id}', [PersonaController::class, 'eliminarPersona']);

        Route::get('/view/persona', [PersonaController::class, 'index'])->name('persona.index');
        Route::get('/view/formularioPersona', [PersonaController::class, 'create'])->name('persona.crearFormulario');
        Route::get('/view/formularioPersona/crear', [PersonaController::class, 'create'])->name('persona.create');
        Route::post('/view/formularioPersona', [PersonaController::class, 'store'])->name('persona.store');

        Route::post('/rol/{id}', [CentroController::class, 'agregarRolCoordinador']);



        // Views

        Route::get('/view/formulario/crearTablaDocente/{id}', [CursoController::class, 'crearTablaPorDocente'])->name('curso.crearTablaPorDocente');
        Route::get('/view/formulario/crearTablaAlumno/{id}', [CursoController::class, 'crearTablaPorAlumno'])->name('curso.crearTablaPorAlumno');



        // Formulario web de Contactos de Centro
        Route::get('/view/formulario/crearContactoCentro', [CentroController::class, 'crearContactoFormulario'])->name('centro_contactos.create');
        Route::post('/view/formularioContactoCentro', [CentroController::class, 'storeContacto'])->name('centro_contactos.store');

        // Rutas web para la gestión de Cursos (Editar, Actualizar y Eliminar)
        Route::get('/view/formulario/crearTablaCurso', [CursoController::class, 'crearTabla'])->name('curso.crearTablaCurso');
        Route::get('/view/curso/{curso}', [CursoController::class, 'visualizarCurso'])->name('curso.visualizar');
        Route::get('/view/formulario/editarCurso/{id}', [CursoController::class, 'editarCursoFormulario'])->name('curso.edit')->whereNumber('id');
        Route::put('/view/formulario/actualizarCurso/{id}', [CursoController::class, 'actualizarCursoWeb'])->name('curso.update')->whereNumber('id');
        Route::delete('/curso/{id}', [CursoController::class, 'eliminarCursoWeb'])->name('curso.destroy')->whereNumber('id');
        Route::get('/view/formulario/crearCurso', [CursoController::class, 'crearFormulario'])->name('curso.crearFormulario');
        Route::post('/view/formularioCurso', [CursoController::class, 'agregarCurso'])->name('curso.agregarCurso');
        Route::delete('/curso/{curso}/alumno/{cursoAlumno}', [CursoController::class, 'eliminarCursoAlumno'])->name('curso.alumno.destroy');
        Route::get('/curso/{curso}/alumnos', [CursoController::class, 'agregarAlumnos'])->name('curso.alumno.create');
        Route::post('/curso/{curso}/alumnos', [CursoController::class, 'guardarAlumnos'])->name('curso.alumno.store');
        Route::get('/curso/{curso}/docentes', [CursoController::class, 'agregarDocentes'])->name('curso.docente.create');
        Route::post('/curso/{curso}/docentes', [CursoController::class, 'guardarDocentes'])->name('curso.docente.store');
        Route::get('/curso/{curso}/calendario/crear', [CursoController::class, 'crearFormularioDia'])->name('curso.dia.create');
        Route::post('/curso/{curso}/calendario', [CursoController::class, 'store'])->name('curso.calendario.store');
        Route::get('/curso/{curso}/formularioLogistica', [CursoController::class, 'crearFormularioLogistica'])->name('curso.logistica.store');
        Route::post('/curso/{curso}/carrito', [CursoController::class, 'crearPeticionLogistica'])->name('curso.logistica.create');
        Route::post('/curso/{curso}/logistica/enviar', [CursoController::class, 'enviarPeticionesLogistica'])->name('curso.logistica.enviar');

        // Rutas web para la gestión de Centros (Editar, Actualizar y Eliminar)
        Route::get('/view/listadoCentro', [CentroController::class, 'index'])->name('centro.index');
        Route::get('/view/formulario/crearCentro', [CentroController::class, 'crearFormulario'])->name('centro.crearFormulario');
        Route::post('/view/formulario/crearCentro', [CentroController::class, 'store'])->name('centro.store');
        Route::get('/view/formulario/editarCentro/{id}', [CentroController::class, 'editarCentroFormulario'])->name('centro.edit')->whereNumber('id');
        Route::put('/view/formulario/actualizarCentro/{id}', [CentroController::class, 'actualizarCentroWeb'])->name('centro.update')->whereNumber('id');
        Route::delete('/centro/{id}', [CentroController::class, 'eliminarCentroWeb'])->name('centro.destroy')->whereNumber('id');

        // Rutas para la visualización detallada y pestañas de Centros
        Route::get('/view/centro/{centro}', [CentroController::class, 'visualizarCentro'])->name('centro.visualizar');
        Route::get('/view/centro/{centro}/aulas', [CentroController::class, 'crearTablaPorAula'])->name('centro.aulas');



        // Rutas web para la gestión de Empresas (Editar, Actualizar y Eliminar)
        Route::get('/view/listadoEmpresa', [EmpresaController::class, 'index'])->name('empresa.index');
        Route::get('/view/empresa/{empresa}', [EmpresaController::class, 'visualizarEmpresa'])->name('empresa.visualizar')->whereNumber('empresa');
        Route::get('/view/formulario/crearEmpresa', [EmpresaController::class, 'mostrarFormulario'])->name('empresa.create');
        Route::post('/view/formulario/crearEmpresa', [EmpresaController::class, 'agregarEmpresa'])->name('empresa.store');
        Route::get('/view/formulario/editarEmpresa/{id}', [EmpresaController::class, 'vistaEditar'])->name('empresa.editar')->whereNumber('id');
        Route::put('/view/formulario/actualizarEmpresa/{id}', [EmpresaController::class, 'actualizarEmpresaWeb'])->name('empresa.actualizar')->whereNumber('id');
        Route::delete('/empresa/{id}', [EmpresaController::class, 'eliminarEmpresaWeb'])->name('empresa.eliminar')->whereNumber('id');

        // Rutas web para la gestión de Formaciones (Editar, Actualizar y Eliminar)
        Route::get('/view/listadoFormacion', [FormacionController::class, 'index'])->name('formacion.index');
        Route::get('/view/formulario/crearFormacion', [FormacionController::class, 'crearFormulario'])->name('formacion.crearFormulario');
        Route::post('/view/formulario/crearFormacion', [FormacionController::class, 'store'])->name('formacion.store');
        Route::get('/view/formulario/editarFormacion/{id}', [FormacionController::class, 'editarFormacionFormulario'])->name('formacion.edit')->whereNumber('id');
        Route::put('/view/formulario/actualizarFormacion/{id}', [FormacionController::class, 'actualizarFormacionWeb'])->name('formacion.update')->whereNumber('id');
        Route::delete('/formacion/{id}', [FormacionController::class, 'eliminarFormacionWeb'])->name('formacion.destroy')->whereNumber('id');

        // Rutas web para la gestión de Presupuestos (Editar, Actualizar y Eliminar)
        Route::get('/view/listadoPresupuesto', [PresupuestoController::class, 'index'])->name('presupuesto.index');
        Route::get('/view/formulario/crearPresupuesto', [PresupuestoController::class, 'create'])->name('presupuesto.create');
        Route::post('/view/formulario/crearPresupuesto', [PresupuestoController::class, 'store'])->name('presupuesto.store');
        Route::get('/view/formulario/editarPresupuesto/{id}', [PresupuestoController::class, 'edit'])->name('presupuesto.edit')->whereNumber('id');
        Route::put('/view/formulario/actualizarPresupuesto/{id}', [PresupuestoController::class, 'update'])->name('presupuesto.update')->whereNumber('id');
        Route::delete('/presupuesto/{id}', [PresupuestoController::class, 'destroy'])->name('presupuesto.destroy')->whereNumber('id');

        // Rutas para el módulo de Aulas
        Route::get('/aulas', [CursoController::class, 'crearTablaAula'])->name('aula.index');



        // Rutas web para la gestión de Empresas (Editar, Actualizar y Eliminar)
        Route::get('/view/listadoEmpresa', [EmpresaController::class, 'index'])->name('empresa.index');
        Route::get('/view/empresa/{empresa}', [EmpresaController::class, 'visualizarEmpresa'])->name('empresa.visualizar')->whereNumber('empresa');
        Route::get('/view/formulario/crearEmpresa', [EmpresaController::class, 'mostrarFormulario'])->name('empresa.create');
        Route::post('/view/formulario/crearEmpresa', [EmpresaController::class, 'agregarEmpresa'])->name('empresa.store');
        Route::get('/view/formulario/editarEmpresa/{id}', [EmpresaController::class, 'vistaEditar'])->name('empresa.editar')->whereNumber('id');
        Route::put('/view/formulario/actualizarEmpresa/{id}', [EmpresaController::class, 'actualizarEmpresaWeb'])->name('empresa.actualizar')->whereNumber('id');
        Route::delete('/empresa/{id}', [EmpresaController::class, 'eliminarEmpresaWeb'])->name('empresa.eliminar')->whereNumber('id');

        // Rutas web para la gestión de Formaciones (Editar, Actualizar y Eliminar)
        Route::get('/view/listadoFormacion', [FormacionController::class, 'index'])->name('formacion.index');
        Route::get('/view/formulario/crearFormacion', [FormacionController::class, 'crearFormulario'])->name('formacion.crearFormulario');
        Route::post('/view/formulario/crearFormacion', [FormacionController::class, 'store'])->name('formacion.store');
        Route::get('/view/formulario/editarFormacion/{id}', [FormacionController::class, 'editarFormacionFormulario'])->name('formacion.edit')->whereNumber('id');
        Route::put('/view/formulario/actualizarFormacion/{id}', [FormacionController::class, 'actualizarFormacionWeb'])->name('formacion.update')->whereNumber('id');
        Route::delete('/formacion/{id}', [FormacionController::class, 'eliminarFormacionWeb'])->name('formacion.destroy')->whereNumber('id');

        // Rutas web para la gestión de Presupuestos (Editar, Actualizar y Eliminar)
        Route::get('/view/listadoPresupuesto', [PresupuestoController::class, 'index'])->name('presupuesto.index');
        Route::get('/view/formulario/crearPresupuesto', [PresupuestoController::class, 'create'])->name('presupuesto.create');
        Route::post('/view/formulario/crearPresupuesto', [PresupuestoController::class, 'store'])->name('presupuesto.store');
        Route::get('/view/formulario/editarPresupuesto/{id}', [PresupuestoController::class, 'edit'])->name('presupuesto.edit')->whereNumber('id');
        Route::put('/view/formulario/actualizarPresupuesto/{id}', [PresupuestoController::class, 'update'])->name('presupuesto.update')->whereNumber('id');
        Route::delete('/presupuesto/{id}', [PresupuestoController::class, 'destroy'])->name('presupuesto.destroy')->whereNumber('id');

        // Rutas para el módulo de Aulas
        Route::get('/aulas', [CursoController::class, 'crearTablaAula'])->name('aula.index');

        // Rutas para editar, actualizar y eliminar aulas
        Route::get('/aula/{id}/editar', [CursoController::class, 'editarAulaFormulario'])->name('aula.edit');
        Route::put('/aula/{id}', [CursoController::class, 'actualizarAula'])->name('aula.update');
        Route::delete('/aula/{id}', [CursoController::class, 'eliminarAula'])->name('aula.destroy');

        // Crear y guardar un aula desde el centro (el centro va en la URL)
        Route::get('/view/centro/{centro_id}/aula/crear', [CursoController::class, 'crearFormularioAula'])->name('aula.create')->whereNumber('centro_id');
        Route::post('/view/centro/{centro_id}/aula/guardar', [CursoController::class, 'guardarAula'])->name('aula.store')->whereNumber('centro_id');


        // Alumnos y docentes (solo administrador y coordinador; lo comprueba el controlador)
        Route::get('/view/alumnos', [AlumnoController::class, 'index'])->name('alumno.index');
        Route::get('/view/alumnos/{persona}', [AlumnoController::class, 'visualizar'])->name('alumno.visualizar')->whereNumber('persona');
        Route::get('/view/docentes', [DocenteController::class, 'index'])->name('docente.index');
        Route::get('/view/docentes/{persona}', [DocenteController::class, 'visualizar'])->name('docente.visualizar')->whereNumber('persona');

        // Comunicaciones FUNDAE
        Route::get('/view/comunicacionFundae', [ComunicacionFundaeController::class, 'index'])->name('fundae.index');
    });

    /*
    |----------------------------------------------------------------------
    | Peticiones de logística (COMERCIAL se mantiene porque ya tenía acceso)
    |----------------------------------------------------------------------
    */
    Route::middleware('rol:ADMINISTRADOR,COORDINADOR,COMERCIAL,LOGISTA')->group(function () {
        Route::get('/view/logistica', [PeticionesLogisticaController::class, 'index'])->name('logistica.index');
        Route::get('/peticionesLogistica/listar', [PeticionesLogisticaController::class, 'listarPeticionesLogistica']);
        Route::get('/peticionesLogistica/{id}', [PeticionesLogisticaController::class, 'obtenerPeticionLogisticaPorID']);
        Route::post('/peticionesLogistica', [PeticionesLogisticaController::class, 'agregarPeticionLogistica']);
        Route::put('/peticionesLogistica/{id}', [PeticionesLogisticaController::class, 'actualizarPeticionLogistica']);
        Route::delete('/peticionesLogistica/{id}', [PeticionesLogisticaController::class, 'eliminarPeticionLogistica']);
    });

    /*
    |----------------------------------------------------------------------
    | Almacén: pedidos de cursos, traspasos, stock, artículos y movimientos (solo LOGISTA y ADMINISTRADOR)
    |----------------------------------------------------------------------
    */
    Route::middleware('rol:ADMINISTRADOR,LOGISTA')->group(function () {
        // Vistas
        Route::get('/view/pedidosLogistica', [PeticionesLogisticaController::class, 'vistaPedidos'])->name('pedidosLogistica.index');
        Route::get('/view/pedidosLogistica/{id}', [PeticionesLogisticaController::class, 'vistaPeticion'])->name('pedidosLogistica.visualizar')->whereNumber('id');
        Route::get('/view/traspasos', [PeticionesLogisticaController::class, 'vistaTraspasos'])->name('traspaso.index');
        Route::get('/view/traspasos/crear', [PeticionesLogisticaController::class, 'formularioTraspaso'])->name('traspaso.create');
        Route::post('/view/traspasos', [PeticionesLogisticaController::class, 'guardarTraspaso'])->name('traspaso.store');
        Route::get('/view/almacen', [AlmacenController::class, 'index'])->name('almacen.index');

        // Servir y devolver: desde los formularios (/view/...) y por la API (/peticionesLogistica/..., responde JSON)
        Route::post('/view/pedidosLogistica/{id}/servir', [PeticionesLogisticaController::class, 'servirPeticion'])->name('pedidosLogistica.servir')->whereNumber('id');
        Route::post('/view/pedidosLogistica/{id}/devolver', [PeticionesLogisticaController::class, 'devolverPeticion'])->name('pedidosLogistica.devolver')->whereNumber('id');
        Route::post('/peticionesLogistica/{id}/servir', [PeticionesLogisticaController::class, 'servirPeticion'])->whereNumber('id');
        Route::post('/peticionesLogistica/{id}/devolver', [PeticionesLogisticaController::class, 'devolverPeticion'])->whereNumber('id');

        // API JSON
        Route::get('/almacen/listar', [AlmacenController::class, 'listarAlmacenes']);
        Route::get('/almacen/{id}', [AlmacenController::class, 'obtenerAlmacenPorID'])->whereNumber('id');
        Route::get('/almacen/{id}/stock', [AlmacenController::class, 'stockAlmacen'])->whereNumber('id');
        Route::post('/almacen', [AlmacenController::class, 'agregarAlmacen']);
        Route::put('/almacen/{id}', [AlmacenController::class, 'actualizarAlmacen'])->whereNumber('id');
        Route::delete('/almacen/{id}', [AlmacenController::class, 'eliminarAlmacen'])->whereNumber('id');

        Route::get('/articulo/listar', [ArticuloController::class, 'listarArticulos']);
        Route::get('/articulo/bajoMinimo', [ArticuloController::class, 'articulosBajoMinimo']);
        Route::get('/articulo/{id}', [ArticuloController::class, 'obtenerArticuloPorID'])->whereNumber('id');
        Route::post('/articulo', [ArticuloController::class, 'agregarArticulo']);
        Route::put('/articulo/{id}', [ArticuloController::class, 'actualizarArticulo'])->whereNumber('id');
        Route::delete('/articulo/{id}', [ArticuloController::class, 'eliminarArticulo'])->whereNumber('id');
        Route::get('/articulo/{id}/unidades', [ArticuloController::class, 'listarUnidades'])->whereNumber('id');
        Route::put('/articulo/{id}/unidades/{unidadId}/estado', [ArticuloController::class, 'cambiarEstadoUnidad'])->whereNumber(['id', 'unidadId']);

        Route::get('/movimientoAlmacen/listar', [MovimientoAlmacenController::class, 'listarMovimientos']);
        Route::get('/movimientoAlmacen/{id}', [MovimientoAlmacenController::class, 'obtenerMovimientoPorID'])->whereNumber('id');
        Route::post('/movimientoAlmacen', [MovimientoAlmacenController::class, 'registrarMovimiento']);
    });

    // Facturas emitidas a clientes (LOGISTA no las ve)
    Route::middleware('rol:ADMINISTRADOR,COMERCIAL')->group(function () {
        Route::get('/factura/listar', [FacturaController::class, 'listarFacturas']);
        Route::get('/factura/pendientesFacturar', [FacturaController::class, 'pendientesFacturar']);
        Route::get('/factura/series', [FacturaController::class, 'listarSeries']);
        Route::post('/factura/series', [FacturaController::class, 'agregarSerie']);
        Route::get('/factura/{id}', [FacturaController::class, 'obtenerFacturaPorID'])->whereNumber('id');
        Route::post('/factura', [FacturaController::class, 'agregarFactura']);
        Route::put('/factura/{id}', [FacturaController::class, 'actualizarFactura'])->whereNumber('id');
        Route::delete('/factura/{id}', [FacturaController::class, 'eliminarFactura'])->whereNumber('id');
        Route::post('/factura/{id}/emitir', [FacturaController::class, 'emitirFactura'])->whereNumber('id');
        Route::post('/factura/{id}/rectificar', [FacturaController::class, 'rectificarFactura'])->whereNumber('id');
        Route::post('/factura/{id}/cobros', [FacturaController::class, 'registrarCobro'])->whereNumber('id');
        Route::delete('/factura/{id}/cobros/{cobroId}', [FacturaController::class, 'eliminarCobro'])->whereNumber(['id', 'cobroId']);

        // Créditos formativos FUNDAE (pantalla web). Van en /view/creditos para no chocar con la API JSON /credito
        Route::get('/view/creditos', [CreditoFormativoController::class, 'index'])->name('credito.index');
        Route::get('/view/creditos/crear', [CreditoFormativoController::class, 'crearFormulario'])->name('credito.create');
        Route::post('/view/creditos', [CreditoFormativoController::class, 'guardarCredito'])->name('credito.store');
        Route::get('/view/creditos/{id}/editar', [CreditoFormativoController::class, 'editarFormulario'])->name('credito.edit')->whereNumber('id');
        Route::put('/view/creditos/{id}', [CreditoFormativoController::class, 'actualizarCreditoWeb'])->name('credito.update')->whereNumber('id');
        Route::delete('/view/creditos/{id}', [CreditoFormativoController::class, 'eliminarCreditoWeb'])->name('credito.destroy')->whereNumber('id');
    });

    // Compras (facturas de proveedores): solo LOGISTA (las suyas) y ADMINISTRADOR (todas)
    Route::middleware('rol:ADMINISTRADOR,LOGISTA')->group(function () {
        Route::get('/view/compras', [FacturaEmpresaProveedoraController::class, 'index'])->name('compra.index');
        Route::get('/view/compras/crear', [FacturaEmpresaProveedoraController::class, 'crearFormulario'])->name('compra.create');
        Route::post('/view/compras', [FacturaEmpresaProveedoraController::class, 'agregarFacturaProveedora'])->name('compra.store');

        Route::get('/facturaProveedora/listar', [FacturaEmpresaProveedoraController::class, 'listarFacturasProveedora']);
        Route::get('/facturaProveedora/{id}', [FacturaEmpresaProveedoraController::class, 'obtenerFacturaProveedoraPorID'])->whereNumber('id');
        Route::post('/facturaProveedora', [FacturaEmpresaProveedoraController::class, 'agregarFacturaProveedora']);
        Route::put('/facturaProveedora/{id}', [FacturaEmpresaProveedoraController::class, 'actualizarFacturaProveedora'])->whereNumber('id');
        Route::delete('/facturaProveedora/{id}', [FacturaEmpresaProveedoraController::class, 'eliminarFacturaProveedora'])->whereNumber('id');
        Route::post('/facturaProveedora/{id}/pagar', [FacturaEmpresaProveedoraController::class, 'marcarPagada'])->name('compra.pagar')->whereNumber('id');
        Route::post('/facturaProveedora/{id}/anular', [FacturaEmpresaProveedoraController::class, 'anularFacturaProveedora'])->whereNumber('id');
    });
});
