<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persona</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    <x-formulario.tarjeta titulo="Persona" subtitulo="Los campos marcados con * son obligatorios">
        <form id="formPersona" method="POST">
            @csrf

            <x-formulario.seccion titulo="Datos personales">
                <x-formulario.entrada name="nombre" label="Nombre *" maxlength="45" required />
                <x-formulario.entrada name="primer_apellido" label="Primer apellido *" maxlength="45" required />
                <x-formulario.entrada name="segundo_apellido" label="Segundo apellido" maxlength="45" />

                <x-formulario.entrada name="fecha_nacimiento" label="Fecha de nacimiento" type="date" />
                <x-formulario.seleccion name="genero" label="Género"
                    :options="['hombre' => 'Hombre', 'mujer' => 'Mujer', 'otro' => 'Otro']" />
                <x-formulario.entrada name="nacionalidad" label="Nacionalidad" maxlength="45" />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Documentación">
                <x-formulario.radio name="tipo_documento" label="Tipo de documento *" value="DNI"
                    :options="['DNI' => 'DNI', 'NIE' => 'NIE']" />

                {{-- Solo se muestra y se envía uno de los dos --}}
                <x-formulario.entrada name="DNI" label="DNI *" maxlength="15" placeholder="12345678Z"
                    class="uppercase" contenedor="bloque-DNI" />
                <x-formulario.entrada name="NIE" label="NIE *" maxlength="15" placeholder="X1234567L"
                    class="uppercase" contenedor="bloque-NIE hidden" disabled />

                <x-formulario.entrada name="numero_seguridad_social" label="Nº Seguridad Social" maxlength="20" />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Contacto">
                <x-formulario.entrada name="telefono" label="Teléfono" type="tel" maxlength="20" />
                <x-formulario.entrada name="email" label="Email" type="email" maxlength="100"
                    contenedor="md:col-span-2" />
                <x-formulario.entrada name="direccion" label="Dirección" maxlength="150"
                    contenedor="md:col-span-3" />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="protección de datos" columnas="2">
                <x-formulario.radio name="LOPD" label="Acepta LOPD *" required
                    :options="['SI' => 'Sí', 'NO' => 'No']" />
            </x-formulario.seccion>

            <p id="error" class="mb-4 hidden text-sm text-red-600"></p>

            <div class="flex justify-end gap-3">
                <x-formulario.boton type="reset" variante="secundario">Limpiar</x-formulario.boton>
                <x-formulario.boton type="submit">Guardar persona</x-formulario.boton>
            </div>
        </form>
    </x-formulario.tarjeta>

    <script>
        const letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
        const form = document.getElementById('formPersona');
        const inputDNI = document.getElementById('DNI');
        const inputNIE = document.getElementById('NIE');

        function dniValido(dni) {
            if (!/^\d{8}[A-Z]$/.test(dni)) return false;
            return letras[parseInt(dni.slice(0, 8)) % 23] === dni[8];
        }

        function nieValido(nie) {
            if (!/^[XYZ]\d{7}[A-Z]$/.test(nie)) return false;
            const num = 'XYZ'.indexOf(nie[0]) + nie.slice(1, 8);
            return letras[parseInt(num) % 23] === nie[8];
        }

        function tipoDocumento() {
            return (document.querySelector('input[name="tipo_documento"]:checked') || {}).value || 'DNI';
        }

        // Muestra solo el campo elegido; el otro se vacía y se desactiva (no se envía)
        function cambiarDocumento() {
            const esDNI = tipoDocumento() === 'DNI';

            document.querySelector('.bloque-DNI').classList.toggle('hidden', !esDNI);
            document.querySelector('.bloque-NIE').classList.toggle('hidden', esDNI);

            inputDNI.disabled = !esDNI;  inputDNI.required = esDNI;
            inputNIE.disabled = esDNI;   inputNIE.required = !esDNI;
            (esDNI ? inputNIE : inputDNI).value = '';
        }

        document.querySelectorAll('input[name="tipo_documento"]').forEach(r =>
            r.addEventListener('change', cambiarDocumento));
        form.addEventListener('reset', () => setTimeout(cambiarDocumento));
        cambiarDocumento(); // estado inicial (también tras volver con errores de validación)

        form.addEventListener('submit', (e) => {
            const fnac = document.getElementById('fecha_nacimiento').value;
            const error = document.getElementById('error');
            let msg = '';

            if (tipoDocumento() === 'DNI') {
                inputDNI.value = inputDNI.value.trim().toUpperCase();
                if (!dniValido(inputDNI.value)) msg = 'El DNI no es válido.';
            } else {
                inputNIE.value = inputNIE.value.trim().toUpperCase();
                if (!nieValido(inputNIE.value)) msg = 'El NIE no es válido.';
            }

            if (!msg && fnac && fnac > new Date().toISOString().slice(0, 10)) {
                msg = 'La fecha de nacimiento no puede ser futura.';
            }

            if (msg) {
                e.preventDefault();
                error.textContent = msg;
                error.classList.remove('hidden');
            } else {
                error.classList.add('hidden');
            }
        });
    </script>
</body>

</html>