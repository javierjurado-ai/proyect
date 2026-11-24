<?php

namespace App\Services\Imagen;

use Illuminate\Http\Response;
use InvalidArgumentException;
use RuntimeException;

/**
 * Convierte el texto guardado en la base de datos (base64 / data URI) otra vez en imagen PNG.
 * Usa la extensión GD de PHP para convertir cualquier formato original a PNG.
 */
class TextoAImagen
{
    /**
     * Devuelve el contenido binario de un PNG.
     */
    public function aPng(string $texto): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('La extensión GD de PHP no está activada.');
        }

        // Quita el prefijo "data:image/xxx;base64," si lo tiene.
        $base64 = preg_replace('/^data:image\/[a-z+.-]+;base64,/i', '', trim($texto));

        $binario = base64_decode($base64, true);
        if ($binario === false) {
            throw new InvalidArgumentException('El texto no es una imagen en base64 válida.');
        }

        $imagen = @imagecreatefromstring($binario);
        if ($imagen === false) {
            throw new InvalidArgumentException('El texto no contiene una imagen válida.');
        }

        imagealphablending($imagen, false);
        imagesavealpha($imagen, true); // conserva la transparencia

        ob_start();
        imagepng($imagen);
        $png = ob_get_clean();
        imagedestroy($imagen);

        return $png;
    }

    /**
     * Guarda la imagen como archivo .png y devuelve la ruta.
     */
    public function aArchivo(string $texto, string $destino): string
    {
        if (strtolower(pathinfo($destino, PATHINFO_EXTENSION)) !== 'png') {
            $destino .= '.png';
        }

        if (file_put_contents($destino, $this->aPng($texto)) === false) {
            throw new RuntimeException("No se pudo guardar la imagen en: {$destino}");
        }

        return $destino;
    }

    /**
     * Respuesta HTTP que muestra la imagen en el navegador (para usar en una ruta/controlador).
     * Con $descargar = true fuerza la descarga como archivo .png.
     */
    public function aRespuesta(string $texto, string $nombre = 'imagen.png', bool $descargar = false): Response
    {
        return response($this->aPng($texto), 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => ($descargar ? 'attachment' : 'inline') . '; filename="' . $nombre . '"',
        ]);
    }
}