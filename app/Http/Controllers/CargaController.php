<?php

namespace App\Http\Controllers;

use App\Models\Carga;
use App\Models\CargaContacto;
use App\Models\Cedente;
use Illuminate\Http\Request;
use League\Csv\Reader;
use League\Csv\Exception as CsvException;

class CargaController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $cargas = Carga::with('cedente')
            ->when(!$user->esAdmin(), fn($q) => $q->where('cedente_id', $user->cedente_id))
            ->latest()
            ->paginate(20);

        return view('cargas.index', compact('cargas'));
    }

    public function create()
    {
        $cedentes = $this->cedentesDisponibles();
        return view('cargas.create', compact('cedentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'     => 'required|string|max:150',
            'cedente_id' => 'required|exists:cedentes,id',
            'archivo'    => 'required|file|mimes:csv,txt|max:20480',
        ], [
            'archivo.mimes' => 'El archivo debe ser formato CSV.',
            'archivo.max'   => 'El archivo no puede superar los 20 MB.',
        ]);

        $user = auth()->user();

        if (!$user->esAdmin() && $request->cedente_id != $user->cedente_id) {
            abort(403);
        }

        // Procesar CSV
        $path = $request->file('archivo')->getRealPath();

        try {
            $csv = Reader::createFromPath($path, 'r');
            $csv->setDelimiter(';');

            // Detectar delimitador automáticamente probando con la primera línea
            $firstLine = file($path)[0] ?? '';
            if (substr_count($firstLine, ',') > substr_count($firstLine, ';')) {
                $csv->setDelimiter(',');
            }

            $records = iterator_to_array($csv->getRecords());
        } catch (CsvException $e) {
            return back()->withInput()->with('error', 'Error al leer el archivo CSV: ' . $e->getMessage());
        }

        if (empty($records)) {
            return back()->withInput()->with('error', 'El archivo CSV está vacío.');
        }

        // Validar estructura: necesita al menos 2 columnas (RUT, Teléfono)
        $primeraFila = array_values(reset($records));
        if (count($primeraFila) < 2 ) {
            return back()->withInput()->with('error', 'El CSV debe tener al menos 2 columnas: RUT (col A) y Teléfono (col B).');
        }

        if (strtolower(trim($primeraFila[1])) !== 'telefono' && strtolower(trim($primeraFila[1])) !== 'fono' && strtolower(trim($primeraFila[1])) !== 'celular') {
            return back()->withInput()->with('error', 'La columna B debe tener encabezado "Teléfono", "Fono" o "Celular".');
        }

        // // NUEVA VALIDACIÓN: Verificar que la columna A sea un RUT válido
        // $posibleRut = trim($primeraFila[0]);

        // // Expresión regular que acepta: 12345678-9, 12.345.678-9, 12345678K, etc.
        // $regexRut = '/^[0-9\.]{7,10}-[0-9kK]{1}$|^[0-9]{7,8}[0-9kK]{1}$/';

        // if (!preg_match($regexRut, $posibleRut)) {
        //     return back()->withInput()->with('error', 'Estructura incorrecta: La columna A debe contener un RUT válido (ej: 12345678-9 o 12.345.678-k).');
        // }

        // Procesar filas
        $contactos    = [];
        $errores      = [];
        $fila         = 0;

        foreach ($records as $record) {
            $fila++;
            $row = array_values($record);

            $rut      = isset($row[0]) ? trim($row[0]) : '';
            $telefono = isset($row[1]) ? trim($row[1]) : '';

            // Saltar encabezados (si la primera fila tiene texto no numérico en col B)
            if ($fila === 1 && !is_numeric(preg_replace('/\D/', '', $telefono))) {
                continue;
            }

            if ($telefono === '') {
                $errores[] = "Fila {$fila}: teléfono vacío.";
                continue;
            }

            // Normalizar: quitar todo lo que no sea dígito
            $telefonoLimpio = preg_replace('/\D/', '', $telefono);

            // Si viene con prefijo 56 (11 dígitos), quitar prefijo
            if (strlen($telefonoLimpio) === 11 && substr($telefonoLimpio, 0, 2) === '56') {
                $telefonoLimpio = substr($telefonoLimpio, 2);
            }

            // Validar que queden exactamente 9 dígitos
            if (strlen($telefonoLimpio) !== 9) {
                $errores[] = "Fila {$fila}: teléfono '{$telefono}' inválido (debe tener 9 dígitos sin prefijo 56).";
                continue;
            }

            $contactos[] = [
                'rut'      => $rut ?: null,
                'telefono' => $telefonoLimpio,
            ];
        }

        if (empty($contactos)) {
            $detalle = !empty($errores) ? ' Errores: ' . implode(' | ', array_slice($errores, 0, 3)) : '';
            return back()->withInput()->with('error', 'No se encontraron contactos válidos en el archivo.' . $detalle);
        }

        // Guardar carga
        $carga = Carga::create([
            'nombre'          => $request->nombre,
            'cedente_id'      => $request->cedente_id,
            'total_registros' => count($contactos),
            'created_by'      => $user->id,
        ]);

        // Insertar contactos en chunks
        $rows = array_map(fn($c) => array_merge($c, ['carga_id' => $carga->id]), $contactos);
        foreach (array_chunk($rows, 500) as $chunk) {
            CargaContacto::insert($chunk);
        }

        $msg = "Carga '{$carga->nombre}' creada con {$carga->total_registros} contactos.";
        if (!empty($errores)) {
            $msg .= ' Se omitieron ' . count($errores) . ' filas con errores.';
        }

        return redirect()->route('cargas.index')->with('success', $msg);
    }

    public function show($id)
    {
        $carga = $this->findAuthorized($id);
        $contactos = $carga->contactos()->paginate(50);
        return view('cargas.show', compact('carga', 'contactos'));
    }

    public function destroy($id)
    {
        $carga = $this->findAuthorized($id);
        $carga->delete();
        return redirect()->route('cargas.index')->with('success', 'Carga eliminada.');
    }

    private function findAuthorized(int $id): Carga
    {
        $user  = auth()->user();
        $carga = Carga::findOrFail($id);

        if (!$user->esAdmin() && $carga->cedente_id !== $user->cedente_id) {
            abort(403);
        }

        return $carga;
    }

    private function cedentesDisponibles()
    {
        $user = auth()->user();
        return $user->esAdmin()
            ? Cedente::orderBy('nombre')->get()
            : Cedente::where('id', $user->cedente_id)->get();
    }
}
