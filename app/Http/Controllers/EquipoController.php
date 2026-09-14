<?php

namespace App\Http\Controllers;

use App\Exports\EquiposExport;
use App\Mail\EquipoNotificationMail;
use App\Models\Equipo;
use App\Models\NotificationEmail;
use App\Models\Reasignacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

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
                'cambiado_por_user_id' => $request->user()?->id,
                'cambiado_por_nombre' => $request->user()?->name ?? 'Sistema',
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
        $ordenarPor = $request->query('ordenar_por', 'codigo');
        $ordenDireccion = $request->query('orden_direccion', 'asc');
        $columnasOrdenables = [
            'ubicacion' => 'ubicacion',
            'estado' => 'estado',
            'codigo' => 'codigo_inventario',
        ];
        $ordenarPor = array_key_exists($ordenarPor, $columnasOrdenables) ? $ordenarPor : 'codigo';
        $ordenDireccion = $ordenDireccion === 'desc' ? 'desc' : 'asc';

        $tiposDisponibles = Equipo::query()
            ->select('tipo')
            ->whereNotNull('tipo')
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo');

        $equipos = $this->buildFilteredQuery($request)
            ->orderBy($columnasOrdenables[$ordenarPor], $ordenDireccion)
            ->orderBy('codigo_inventario')
            ->get();

        $metrics = $this->computeMetrics($equipos);

        $totalEquipos = $metrics['total'];
        $excelentes = $metrics['excelentes'];
        $buenos = $metrics['buenos'];
        $revision = $metrics['revision'];
        $telefonos = $metrics['telefonos'];
        $computadores = $metrics['computadores'];
        $valorTotal = $metrics['valor_total'];
        $valorActual = $metrics['valor_actual'];
        $tiposData = $metrics['tipos'];

        $nextCodigoComputador = $this->generateCodigoInventario('Computador');
        $nextCodigoTelefono = $this->generateCodigoInventario('Telefono');
        $notificationEmails = NotificationEmail::orderBy('email')->get();
        $specOptions = $this->getSpecOptions();
        $assetVersion = time();
        $exportColumns = self::exportableColumns();

        return view('equipos.index', compact(
            'equipos',
            'totalEquipos',
            'excelentes',
            'buenos',
            'revision',
            'telefonos',
            'computadores',
            'valorTotal',
            'valorActual',
            'tiposData',
            'nextCodigoComputador',
            'nextCodigoTelefono',
            'notificationEmails',
            'specOptions',
            'assetVersion',
            'exportColumns',
            'ordenarPor',
            'ordenDireccion'
        ))->with([
            'search' => $search,
            'estadoSeleccionado' => $estado,
            'tipoSeleccionado' => $tipo,
            'tiposDisponibles' => $tiposDisponibles,
        ]);
    }

    /**
     * Métricas del dashboard en JSON, respetando los mismos filtros que index(), para el polling en tiempo real.
     */
    public function metrics(Request $request)
    {
        $equipos = $this->buildFilteredQuery($request)->get();

        return response()->json($this->computeMetrics($equipos));
    }

    public function export(Request $request)
    {
        $available = array_keys(self::exportableColumns());
        $requested = array_filter((array) $request->query('columns', $available));
        $columns = array_values(array_intersect($available, $requested));

        if (empty($columns)) {
            $columns = $available;
        }

        $scope = $request->query('scope') === 'all' ? 'all' : 'filtered';
        $sortBy = in_array($request->query('sort_by'), $available, true) ? $request->query('sort_by') : 'codigo_inventario';
        $sortDir = $request->query('sort_dir') === 'desc' ? 'desc' : 'asc';

        $query = $scope === 'all' ? Equipo::query() : $this->buildFilteredQuery($request);

        return Excel::download(
            new EquiposExport($query, $columns, self::exportableColumns(), $sortBy, $sortDir),
            'inventario-equipos-' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }

    private function buildFilteredQuery(Request $request): Builder
    {
        $search = trim((string) $request->query('search', ''));
        $estado = $request->query('estado', '');
        $tipo = $request->query('tipo', '');

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

        return $query;
    }

    private function computeMetrics($equipos): array
    {
        return [
            'total' => $equipos->count(),
            'excelentes' => $equipos->where('estado', 'Excelente')->count(),
            'buenos' => $equipos->where('estado', 'Bueno')->count(),
            'revision' => $equipos->whereIn('estado', ['Regular', 'Malo', 'De Baja'])->count(),
            'telefonos' => $equipos->where('categoria', 'Telefonos')->count(),
            'computadores' => $equipos->where('categoria', 'Equipos')->count(),
            'valor_total' => (float) $equipos->sum(fn ($equipo) => $this->normalizeMoneyToNumber($equipo->valoracion_equipo) ?? 0),
            'valor_actual' => (float) $equipos->sum(fn ($equipo) => (float) ($equipo->valoracion_equipo_actual_numero ?? 0)),
            'tipos' => $equipos->groupBy('tipo')->map->count(),
            'actualizado' => now()->format('d/m/Y H:i:s'),
        ];
    }

    public static function exportableColumns(): array
    {
        return [
            'codigo_inventario' => 'Código',
            'nombre' => 'Nombre',
            'asignado_a' => 'Asignado a',
            'categoria' => 'Categoría',
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
            'marca_monitor2' => 'Marca monitor 2',
            'modelo_monitor2' => 'Modelo monitor 2',
            'numero_serie_monitor2' => 'Nº Serie monitor 2',
            'teclado' => 'Teclado',
            'mouse' => 'Mouse',
            'base_notebook' => 'Base notebook',
            'onedrive_funcionando' => 'OneDrive funciona',
            'respaldo_onedrive' => 'Respaldo OneDrive',
            'equipo_reasignado_a' => 'Reasignado a',
            'valoracion_equipo' => 'Valoración equipo',
            'valoracion_monitor' => 'Valoración monitor',
            'valoracion_equipo_actual' => 'Valor actual',
            'estado' => 'Estado',
            'ubicacion' => 'Ubicación',
            'mantencion_realizada' => 'Mantención realizada',
            'observaciones' => 'Observaciones',
            'created_at' => 'Fecha de creación',
            'updated_at' => 'Última actualización',
        ];
    }

    private function validateEquipo(Request $request, ?int $equipoId = null): array
    {
        if ($request->has('pantalla_externa')) {
            $request->merge([
                'pantalla_externa' => $this->normalizePantallaExterna($request->input('pantalla_externa')),
            ]);
        }

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
            'pantalla_externa' => ['nullable', Rule::in(['SI', 'SI2', 'NO'])],
            'marca_monitor' => ['nullable', 'string', 'max:255'],
            'modelo_monitor' => ['nullable', 'string', 'max:255'],
            'numero_serie_monitor' => ['nullable', 'string', 'max:255'],
            'marca_monitor2' => ['nullable', 'string', 'max:255'],
            'modelo_monitor2' => ['nullable', 'string', 'max:255'],
            'numero_serie_monitor2' => ['nullable', 'string', 'max:255'],
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

    private function normalizePantallaExterna(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtoupper(trim((string) $value));
        $normalized = str_replace(' ', '', $normalized);

        if ($normalized === 'SI,2' || $normalized === 'SI2') {
            return 'SI2';
        }

        return in_array($normalized, ['SI', 'NO'], true) ? $normalized : null;
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

        $reasignadoA = trim((string) ($data['equipo_reasignado_a'] ?? ''));

        if ($reasignadoA === '' && $asignado !== '') {
            $reasignadoA = $asignado;
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
        $equipo->marca_monitor2 = $data['marca_monitor2'] ?? null;
        $equipo->modelo_monitor2 = $data['modelo_monitor2'] ?? null;
        $equipo->numero_serie_monitor2 = $data['numero_serie_monitor2'] ?? null;
        $equipo->teclado = $data['teclado'] ?? null;
        $equipo->mouse = $data['mouse'] ?? null;
        $equipo->base_notebook = $data['base_notebook'] ?? null;
        $equipo->onedrive_funcionando = $data['onedrive_funcionando'] ?? null;
        $equipo->respaldo_onedrive = $data['respaldo_onedrive'] ?? null;
        $equipo->equipo_reasignado_a = $reasignadoA !== '' ? $reasignadoA : null;
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
        return array_key_exists('equipo_reasignado_a', $cambios)
            || array_key_exists('asignado_a', $cambios);
    }

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
            'marca_monitor2' => 'Marca monitor 2',
            'modelo_monitor2' => 'Modelo monitor 2',
            'numero_serie_monitor2' => 'Nº Serie monitor 2',
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

    private function resolveDestinatariosSeleccionados(Request $request): array
    {
        $seleccionados = array_values(array_filter((array) $request->input('notificar_a', [])));

        return array_values(array_unique(array_filter($seleccionados)));
    }

    private function notificar(Equipo $equipo, string $accion, array $cambios = [], ?array $destinatarios = null): void
    {
        if (empty($destinatarios)) {
            $destinatarios = NotificationEmail::pluck('email')->all();

            if (empty($destinatarios)) {
                $destinatarios = config('equipos.notification_emails', []);

                if (empty($destinatarios)) {
                    $envEmails = env('EQUIPO_NOTIFICATION_EMAILS', 'fgonzalez@pcgeek.cl,vvegas@pcgeek.cl');
                    $destinatarios = array_filter(array_map('trim', explode(',', $envEmails)));
                }
            }
        }

        if (empty($destinatarios)) {
            return;
        }

        $snapshot = (object) $equipo->toArray();

        try {
            Mail::to($destinatarios)->send(new EquipoNotificationMail($snapshot, $accion, $cambios));
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el correo de notificación de equipo: ' . $e->getMessage());
        }
    }
}