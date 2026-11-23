<!DOCTYPE html>
<html lang="es">
@php
    $presupuesto = $presupuesto ?? null;
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($presupuesto) ? 'Editar Presupuesto' : 'Crear Presupuesto' }}</title>
    <script>        
        const expira = Number(sessionStorage.getItem('expira')); 

        if (!token || Date.now() / 1000 > expira) {
            sessionStorage.clear();
            window.location.href = '/persona/iniciarSesion';
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    <x-formulario.tarjeta titulo="Presupuesto" subtitulo="Los campos marcados con * son obligatorios">
        
        <form id="formPresupuesto" method="POST" action="{{ isset($presupuesto) ? route('presupuesto.update', $presupuesto->id) : route('presupuesto.store') }}">
            @csrf
            @if(isset($presupuesto))
                @method('PUT')
            @endif

            <x-formulario.seccion titulo="Formación">
                <x-formulario.entrada name="nombre_formacion" label="Nombre de la formación *" maxlength="45"
                    contenedor="md:col-span-2" :value="$presupuesto?->nombre_formacion ?? old('nombre_formacion')" />
                <x-formulario.entrada name="horas_totales" label="Horas totales *" type="number" min="1" step="0.5" 
                    :value="$presupuesto?->horas_totales ?? old('horas_totales')" />

                <x-formulario.entrada name="numero_alumnos" label="Número de alumnos *" type="number" min="1" step="1" 
                    :value="$presupuesto?->numero_alumnos ?? old('numero_alumnos')" />
                <x-formulario.entrada name="ubicacion_formacion" label="Ubicación de la formación *" maxlength="90"
                    contenedor="md:col-span-2" :value="$presupuesto?->ubicacion_formacion ?? old('ubicacion_formacion')" />
            </x-formulario.seccion>

            {{-- Objetivos y contenidos ya no van en el presupuesto: ahora están en el curso --}}

            <x-formulario.seccion titulo="Bonificación" columnas="2">
                <x-formulario.entrada name="importe_bonificado" label="Importe bonificado (€) *" type="number"
                    min="0" max="99999999.99" step="0.01" placeholder="0.00" 
                    :value="$presupuesto?->importe_bonificado ?? old('importe_bonificado')" />
                <x-formulario.entrada name="fecha_bonificado" label="Fecha de bonificación *" type="date"
                    :min="isset($presupuesto) ? null : now()->toDateString()" 
                    :value="$presupuesto?->fecha_bonificado ? \Illuminate\Support\Carbon::parse($presupuesto->fecha_bonificado)->format('Y-m-d') : old('fecha_bonificado')" />
            </x-formulario.seccion>

            <!-- Zona de Botones Adaptativa (Crear / Editar / Eliminar) -->
            <div class="flex items-center justify-between mt-8 pt-4 border-t border-slate-100">
                @if(isset($presupuesto))
                    <button type="button" 
                            onclick="if(confirm('¿Estás seguro de que deseas eliminar este presupuesto?')) { document.getElementById('delete-form').submit(); }"
                            class="px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50 transition text-sm font-medium">
                        Eliminar
                    </button>
                @else
                    <div></div>
                @endif

                <div class="flex items-center gap-3">
                    <x-formulario.boton type="reset" variante="secundario">Limpiar</x-formulario.boton>
                    <x-formulario.boton type="submit">{{ isset($presupuesto) ? 'Actualizar presupuesto' : 'Guardar presupuesto' }}</x-formulario.boton>
                </div>
            </div>
        </form>

        {{-- Formulario oculto para procesar la petición DELETE --}}
        @if(isset($presupuesto))
            <form id="delete-form" action="{{ route('presupuesto.destroy', $presupuesto->id) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </x-formulario.tarjeta>

    @if (session('ok'))
    <script>
        document.getElementById('formPresupuesto').addEventListener('focusin', () => {
            document.getElementById('success-alert')?.remove();
        }, { once: true });
    </script>
    @endif

</body>

</html>