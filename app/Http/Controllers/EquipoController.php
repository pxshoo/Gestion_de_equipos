<?php

namespace App\Http\Controllers;

use App\Mail\EquipoNotificationMail;
use App\Models\Equipo;
use App\Models\NotificationEmail;
use App\Models\Reasignacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EquipoController extends Controller
{
    public function store(Request $request)
    {
        abort_unless($request->user()?->puedeCrear(), 403, 'No tienes permisos para crear equipos.');

        $data = $this->validateEquipo($request);
        $destinatarios = $this->resolveDestinatariosSeleccionados($request);

        $equipo = new Equipo();
        $this->fillEquipo($equipo, $data, true);
        $equipo->save();

        $this->notificar($equipo, 'creado', [], $destinatarios);

        return redirect()
            ->back()
            ->with('status', 'Equipo creado correctamente.');
    }

    public function update(Request $request, Equipo $equipo)
    {
        abort_unless($request->user()?->puedeEditar(), 403, 'No tienes permisos para editar equipos.');

        $data = $this->validateEquipo($request, $equipo->id);
        $destinatarios = $this->resolveDestinatariosSeleccionados($request);

        $original = $equipo->getOriginal();

        $this->fillEquipo($equipo, $data, false);
        $equipo->save();

        $cambios = $this->buildCambios($original, $equipo->getChanges());
        $accion = $this->esReasignacion($equipo->getChanges()) ? 'reasignado' : 'actualizado';

        if ($accion === 'reasignado') {
            Reasignacion::create([
                'equipo_id' => $equipo->id,
                'codigo_inventario' => $equipo->codigo_inventario,
                'equipo_nombre' => $equipo->nombre,
                'tipo' => $equipo->tipo,
                'marca' => $equipo->marca,
                'modelo' => $equipo->modelo,
                'asignado_anterior' => $original['asignado_a'] ?? null,
                'asignado_nuevo' => $equipo->asignado_a,
                'equipo_reasignado_anterior' => $original['equipo_reasignado_a'] ?? null,
                'equipo_reasignado_nuevo' => $equipo->equipo_reasignado_a,
                'ubicacion' => $equipo->ubicacion,
                'fecha_reasignacion' => now(),
            ]);
        }

        $this->notificar($equipo, $accion, $cambios, $destinatarios);

        return redirect()
            ->back()
            ->with('status', 'Equipo actualizado correctamente.');
    }

    public function destroy(Request $request, Equipo $equipo)
    {
        abort_unless($request->user()?->puedeEliminar(), 403, 'No tienes permisos para eliminar equipos.');

        // Se notifica antes de borrar para poder incluir todos los datos del equipo en el correo.
        $this->notificar($equipo, 'eliminado');

        $equipo->delete();

        return redirect()
            ->back()
            ->with('status', 'Equipo eliminado correctamente.');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $estado = $request->query('estado', '');
        $tipo = $request->query('tipo', '');

        $tiposDisponibles = Equipo::query()
            ->select('tipo')
            ->whereNotNull('tipo')
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo');

        $query = Equipo::query();

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('nombre', 'like', "%{$search}%")
                    ->orWhere('asignado_a', 'like', "%{$search}%")
                    ->orWhere('codigo_inventario', 'like', "%{$search}%")
                    ->orWhere('marca', 'like', "%{$search}%")
                    ->orWhere('modelo', 'like', "%{$search}%")
                    ->orWhere('numero_serie', 'like', "%{$search}%");
            });
        }

        if ($estado !== '') {
            $query->where('estado', $estado);
        }

        if ($tipo !== '') {
            $query->where('tipo', $tipo);
        }

        // Orden principal por nombre asignado para que el listado sea más útil.
        $equipos = $query
            ->orderByRaw("COALESCE(NULLIF(nombre, ''), NULLIF(asignado_a, ''), codigo_inventario) asc")
            ->orderBy('codigo_inventario')
            ->get();

        // Conteos para las tarjetas del Dashboard
        $totalEquipos = $equipos->count();
        $excelentes = $equipos->where('estado', 'Excelente')->count();
        $buenos = $equipos->where('estado', 'Bueno')->count();
        $revision = $equipos->whereIn('estado', ['Regular', 'Malo', 'De Baja'])->count();
        $telefonos = $equipos->where('categoria', 'Telefonos')->count();
        $computadores = $equipos->where('categoria', 'Equipos')->count();
        $valorTotal = (float) $equipos->sum('valoracion_equipo_actual_numero');

        // Datos agrupados por tipo para gráficos JavaScript (Chart.js)
        $tiposData = $equipos->groupBy('tipo')->map->count();
        $nextCodigoComputador = $this->generateCodigoInventario('Computador');
        $nextCodigoTelefono = $this->generateCodigoInventario('Telefono');
        $notificationEmails = NotificationEmail::orderBy('email')->get();
        $specOptions = $this->getSpecOptions();

        return view('equipos.index', compact(
            'equipos',
            'totalEquipos',
            'excelentes',
            'buenos',
            'revision',
            'telefonos',
            'computadores',
            'valorTotal',
            'tiposData',
            'nextCodigoComputador',
            'nextCodigoTelefono',
            'notificationEmails',
            'specOptions'
        ))->with([
            'search' => $search,
            'estadoSeleccionado' => $estado,
            'tipoSeleccionado' => $tipo,
            'tiposDisponibles' => $tiposDisponibles,
        ]);
    }

    private function validateEquipo(Request $request, ?int $equipoId = null): array
    {
        return $request->validate([
            'nombre' => ['nullable', 'string', 'max:255'],
            'asignado_a' => ['nullable', 'string', 'max:255'],
            'tipo' => ['required', 'in:Computador,Telefono'],
            'marca' => ['required', 'string', 'max:255'],
            'modelo' => ['nullable', 'string', 'max:255'],
            'numero_serie' => ['nullable', 'string', 'max:255'],
            'usuario_pc' => ['nullable', 'string', 'max:255'],
            'procesador' => ['nullable', 'string', 'max:255'],
            'tipo_disco_duro' => ['nullable', 'string', 'max:255'],
            'ram_instalada' => ['nullable', 'string', 'max:255'],
            'pantalla_externa' => ['nullable', 'in:SI,NO'],
            'marca_monitor' => ['nullable', 'string', 'max:255'],
            'modelo_monitor' => ['nullable', 'string', 'max:255'],
            'numero_serie_monitor' => ['nullable', 'string', 'max:255'],
            'teclado' => ['nullable', 'in:SI,NO'],
            'mouse' => ['nullable', 'in:SI,NO'],
            'base_notebook' => ['nullable', 'in:SI,NO'],
            'onedrive_funcionando' => ['nullable', 'in:SI,NO'],
            'respaldo_onedrive' => ['nullable', 'in:SI,NO'],
            'equipo_reasignado_a' => ['nullable', 'string', 'max:255'],
            'valoracion_equipo' => ['nullable', 'string', 'max:255'],
            'valoracion_monitor' => ['nullable', 'string', 'max:255'],
            'valoracion_equipo_actual' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', 'in:Excelente,Bueno,Regular,Malo,De Baja'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'mantencion_realizada' => ['nullable', 'in:SI,NO'],
            'observaciones' => ['nullable', 'string'],
        ]);
    }

    private function fillEquipo(Equipo $equipo, array $data, bool $isNew): void
    {
        if ($isNew) {
            $equipo->codigo_inventario = $this->generateCodigoInventario($data['tipo']);
        }

        $asignado = trim((string) ($data['asignado_a'] ?? ''));
        $nombre = trim((string) ($data['nombre'] ?? ''));

        if ($asignado === '' && $nombre !== '') {
            $asignado = $nombre;
        }

        if ($nombre === '' && $asignado !== '') {
            $nombre = $asignado;
        }

        $equipo->categoria = $data['tipo'] === 'Telefono' ? 'Telefonos' : 'Equipos';
        $equipo->nombre = $nombre !== '' ? $nombre : null;
        $equipo->asignado_a = $asignado !== '' ? $asignado : null;
        $equipo->tipo = $data['tipo'];
        $equipo->marca = $data['marca'];
        $equipo->modelo = $data['modelo'] ?? null;
        $equipo->numero_serie = $data['numero_serie'] ?? null;
        $equipo->usuario_pc = $data['usuario_pc'] ?? null;
        $equipo->procesador = $data['procesador'] ?? null;
        $equipo->tipo_disco_duro = $data['tipo_disco_duro'] ?? null;
        $equipo->ram_instalada = $data['ram_instalada'] ?? null;
        $equipo->pantalla_externa = $data['pantalla_externa'] ?? null;
        $equipo->marca_monitor = $data['marca_monitor'] ?? null;
        $equipo->modelo_monitor = $data['modelo_monitor'] ?? null;
        $equipo->numero_serie_monitor = $data['numero_serie_monitor'] ?? null;
        $equipo->teclado = $data['teclado'] ?? null;
        $equipo->mouse = $data['mouse'] ?? null;
        $equipo->base_notebook = $data['base_notebook'] ?? null;
        $equipo->onedrive_funcionando = $data['onedrive_funcionando'] ?? null;
        $equipo->respaldo_onedrive = $data['respaldo_onedrive'] ?? null;
        $equipo->equipo_reasignado_a = $data['equipo_reasignado_a'] ?? null;
        $equipo->valoracion_equipo = $data['valoracion_equipo'] ?? null;
        $equipo->valoracion_monitor = $data['valoracion_monitor'] ?? null;
        $equipo->valoracion_equipo_actual = $data['valoracion_equipo_actual'] ?? null;
        $equipo->valoracion_equipo_actual_numero = $this->normalizeMoneyToNumber($data['valoracion_equipo_actual'] ?? null);
        $equipo->estado = $data['estado'];
        $equipo->ubicacion = $data['ubicacion'] ?? null;
        $equipo->mantencion_realizada = $data['mantencion_realizada'] ?? null;
        $equipo->observaciones = $data['observaciones'] ?? null;
    }

    private function generateCodigoInventario(string $tipo): string
    {
        $prefix = $tipo === 'Telefono' ? 'TEL-' : 'EQ-';
        $maxNumber = 0;

        Equipo::query()
            ->where('codigo_inventario', 'like', $prefix . '%')
            ->pluck('codigo_inventario')
            ->each(function (string $codigo) use (&$maxNumber, $prefix) {
                if (! str_starts_with($codigo, $prefix)) {
                    return;
                }

                if (preg_match('/(\d+)$/', $codigo, $matches)) {
                    $maxNumber = max($maxNumber, (int) $matches[1]);
                }
            });

        return $prefix . str_pad((string) ($maxNumber + 1), 4, '0', STR_PAD_LEFT);
    }

    private function normalizeMoneyToNumber(?string $value): ?float
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $numeric = preg_replace('/[^0-9]/', '', $value);

        return $numeric === '' ? null : (float) $numeric;
    }

    /**
     * Obtiene los valores distintos ya registrados en la BDD para poblar los
     * selects de especificaciones (con opción de agregar uno nuevo con "Otro").
     *
     * @return array<string, array<int, string>>
     */
    private function getSpecOptions(): array
    {
        $campos = ['marca', 'modelo', 'procesador', 'tipo_disco_duro', 'ram_instalada'];
        $opciones = [];

        foreach ($campos as $campo) {
            $opciones[$campo] = Equipo::query()
                ->select($campo)
                ->whereNotNull($campo)
                ->where($campo, '!=', '')
                ->distinct()
                ->orderBy($campo)
                ->pluck($campo)
                ->values()
                ->all();
        }

        return $opciones;
    }

    private function esReasignacion(array $cambios): bool
    {
        return array_key_exists('asignado_a', $cambios) || array_key_exists('equipo_reasignado_a', $cambios);
    }

    /**
     * @return array<int, array{label: string, before: string, after: string}>
     */
    private function buildCambios(array $original, array $cambios): array
    {
        $labels = [
            'nombre' => 'Nombre',
            'asignado_a' => 'Asignado a',
            'tipo' => 'Tipo',
            'marca' => 'Marca',
            'modelo' => 'Modelo',
            'numero_serie' => 'Nº Serie',
            'usuario_pc' => 'Usuario PC',
            'procesador' => 'Procesador',
            'tipo_disco_duro' => 'Tipo disco duro',
            'ram_instalada' => 'RAM instalada',
            'pantalla_externa' => 'Pantalla externa',
            'marca_monitor' => 'Marca monitor',
            'modelo_monitor' => 'Modelo monitor',
            'numero_serie_monitor' => 'Nº Serie monitor',
            'teclado' => 'Teclado',
            'mouse' => 'Mouse',
            'base_notebook' => 'Base notebook',
            'onedrive_funcionando' => 'OneDrive funciona',
            'respaldo_onedrive' => 'Respaldo OneDrive',
            'equipo_reasignado_a' => 'Equipo reasignado a',
            'valoracion_equipo' => 'Valoración equipo',
            'valoracion_monitor' => 'Valoración monitor',
            'valoracion_equipo_actual' => 'Valor actual',
            'estado' => 'Estado',
            'ubicacion' => 'Ubicación',
            'mantencion_realizada' => 'Mantención realizada',
            'observaciones' => 'Observaciones',
        ];

        $ignorar = ['updated_at', 'created_at', 'valoracion_equipo_actual_numero', 'codigo_inventario', 'categoria'];

        $resultado = [];

        foreach ($cambios as $campo => $despues) {
            if (in_array($campo, $ignorar, true)) {
                continue;
            }

            $resultado[] = [
                'label' => $labels[$campo] ?? ucfirst(str_replace('_', ' ', $campo)),
                'before' => $original[$campo] ?? null ? (string) $original[$campo] : '—',
                'after' => $despues !== null && $despues !== '' ? (string) $despues : '—',
            ];
        }

        return $resultado;
    }

    /**
     * Obtiene la lista de correos a notificar según lo marcado en el formulario
     * (checkboxes "notificar_a[]"), y agrega/crea un nuevo destinatario si el
     * usuario completó los campos "nuevo_destinatario_email" / "..._descripcion".
     *
     * @return array<int, string>
     */
    private function resolveDestinatariosSeleccionados(Request $request): array
    {
        $seleccionados = array_values(array_filter((array) $request->input('notificar_a', [])));

        $nuevoEmail = trim((string) $request->input('nuevo_destinatario_email', ''));

        if ($nuevoEmail !== '') {
            $request->validate([
                'nuevo_destinatario_email' => ['email', 'max:255'],
                'nuevo_destinatario_descripcion' => ['nullable', 'string', 'max:255'],
            ]);

            NotificationEmail::firstOrCreate(
                ['email' => $nuevoEmail],
                ['descripcion' => $request->input('nuevo_destinatario_descripcion') ?: null]
            );

            $seleccionados[] = $nuevoEmail;
        }

        return array_values(array_unique(array_filter($seleccionados)));
    }

    private function notificar(Equipo $equipo, string $accion, array $cambios = [], ?array $destinatarios = null): void
    {
        if ($destinatarios === null) {
            $destinatarios = NotificationEmail::pluck('email')->all();

            if (empty($destinatarios)) {
                $destinatarios = config('equipos.notification_emails', []);
            }
        }

        if (empty($destinatarios)) {
            return;
        }

        // Se envía un snapshot (copia) de los datos, no el modelo Eloquent vivo:
        // el correo se procesa de forma asíncrona en la cola, y para la acción
        // "eliminado" el registro ya no existiría en la base de datos cuando
        // el worker intente procesarlo.
        $snapshot = (object) $equipo->toArray();

        try {
            Mail::to($destinatarios)->queue(new EquipoNotificationMail($snapshot, $accion, $cambios));
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el correo de notificación de equipo: ' . $e->getMessage());
        }
    }
}