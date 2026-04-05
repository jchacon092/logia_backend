<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

use App\Models\Miembro;
use App\Models\User;
use Spatie\Permission\Models\Role;

class MiembroController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════════
    // MIEMBROS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * GET /api/miembros
     * Query: q, estado, page
     * Sin autenticación especial — solo auth:sanctum
     * Con manage: devuelve campos extra (soft deleted incluidos si ?con_baja=1)
     */
    public function index(Request $request)
    {
        $q      = $request->query('q');
        $estado = $request->query('estado');
        $conBaja = $request->boolean('con_baja');

        $query = Miembro::query()
            ->with('user:id,name,email')
            ->select(
                'id', 'nombre_completo', 'grado', 'email', 'telefono',
                'direccion', 'dpi', 'estado_civil', 'fecha_ingreso',
                'estado', 'motivo_baja', 'foto', 'user_id',
                'created_at', 'deleted_at'
            )
            ->when($q, fn($qq) =>
                $qq->where('nombre_completo', 'like', "%{$q}%")
            )
            ->when($estado, fn($qq) =>
                $qq->where('estado', $estado)
            )
            ->when($conBaja, fn($qq) =>
                $qq->withTrashed()
            )
            ->orderBy('nombre_completo');

        // Sin paginación para selects (límite 500), con paginación para la tabla admin
        if ($request->boolean('all')) {
            return response()->json($query->limit(500)->get());
        }

        return response()->json($query->paginate(20));
    }

    /**
     * POST /api/miembros
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:255'],
            'grado'           => ['nullable', 'string', 'max:10'],
            'email'           => ['nullable', 'email', 'unique:miembros,email'],
            'telefono'        => ['nullable', 'string', 'max:20'],
            'direccion'       => ['nullable', 'string', 'max:500'],
            'dpi'             => ['nullable', 'string', 'max:20'],
            'estado_civil'    => ['nullable', Rule::in(['soltero','casado','divorciado','viudo','otro'])],
            'fecha_ingreso'   => ['nullable', 'date'],
            'estado'          => ['nullable', Rule::in(['activo','suspendido','retirado','fallecido'])],
            'foto'            => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('miembros', 'public');
        }

        $miembro = Miembro::create($data);

        return response()->json($miembro->load('user:id,name,email'), 201);
    }

    /**
     * GET /api/miembros/{miembro}
     */
    public function show(Miembro $miembro)
    {
        return response()->json(
            $miembro->load('user:id,name,email')
        );
    }

    /**
     * PUT /api/miembros/{miembro}
     */
    public function update(Request $request, Miembro $miembro)
    {
        $data = $request->validate([
            'nombre_completo' => ['sometimes', 'required', 'string', 'max:255'],
            'grado'           => ['nullable', 'string', 'max:10'],
            'email'           => ['nullable', 'email', Rule::unique('miembros','email')->ignore($miembro->id)],
            'telefono'        => ['nullable', 'string', 'max:20'],
            'direccion'       => ['nullable', 'string', 'max:500'],
            'dpi'             => ['nullable', 'string', 'max:20'],
            'estado_civil'    => ['nullable', Rule::in(['soltero','casado','divorciado','viudo','otro'])],
            'fecha_ingreso'   => ['nullable', 'date'],
            'estado'          => ['nullable', Rule::in(['activo','suspendido','retirado','fallecido'])],
            'foto'            => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe
            if ($miembro->foto) {
                Storage::disk('public')->delete($miembro->foto);
            }
            $data['foto'] = $request->file('foto')->store('miembros', 'public');
        }

        $miembro->update($data);

        return response()->json($miembro->fresh()->load('user:id,name,email'));
    }

    /**
     * DELETE /api/miembros/{miembro}
     * Soft delete con motivo obligatorio
     */
    public function destroy(Request $request, Miembro $miembro)
    {
        $request->validate([
            'motivo_baja' => ['required', 'string', 'max:500'],
        ]);

        $miembro->update([
            'estado'      => $request->estado ?? 'retirado',
            'motivo_baja' => $request->motivo_baja,
        ]);

        $miembro->delete(); // soft delete — sets deleted_at

        return response()->json(['message' => 'Miembro dado de baja correctamente.']);
    }

    /**
     * POST /api/miembros/{miembro}/restaurar
     * Restaura un miembro dado de baja
     */
    public function restaurar(Miembro $miembro)
    {
        $miembro->withTrashed()->where('id', $miembro->id)->restore();
        $miembro->update(['estado' => 'activo', 'motivo_baja' => null]);

        return response()->json($miembro->fresh());
    }

    // ══════════════════════════════════════════════════════════════════════════
    // USUARIOS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * GET /api/miembros/usuarios
     * Lista todos los usuarios con su rol y miembro vinculado
     */
    public function usuariosIndex()
    {
        $users = User::with([
            'roles:id,name',
            'miembro:id,nombre_completo,grado,user_id',
        ])->get()->map(fn($u) => [
            'id'      => $u->id,
            'name'    => $u->name,
            'email'   => $u->email,
            'roles'   => $u->roles->pluck('name'),
            'miembro' => $u->miembro ? [
                'id'             => $u->miembro->id,
                'nombre_completo'=> $u->miembro->nombre_completo,
                'grado'          => $u->miembro->grado,
            ] : null,
        ]);

        return response()->json($users);
    }

    /**
     * POST /api/miembros/{miembro}/crear-usuario
     * Crea un usuario del sistema vinculado al miembro
     * Body: { email, password, rol }
     */
    public function crearUsuario(Request $request, Miembro $miembro)
    {
        if ($miembro->user_id) {
            return response()->json(
                ['message' => 'Este miembro ya tiene un usuario asignado.'],
                422
            );
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'rol'      => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = DB::transaction(function () use ($data, $miembro) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole($data['rol']);
            $miembro->update(['user_id' => $user->id]);

            return $user;
        });

        return response()->json([
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
            ],
            'miembro_id' => $miembro->id,
        ], 201);
    }

    /**
     * PUT /api/miembros/usuarios/{user}/rol
     * Cambia el rol de un usuario
     * Body: { rol }
     */
    public function cambiarRol(Request $request, User $user)
    {
        $data = $request->validate([
            'rol' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user->syncRoles([$data['rol']]);

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name'),
        ]);
    }

    /**
     * GET /api/miembros/roles
     * Lista todos los roles disponibles
     */
    public function roles()
    {
        return response()->json(
            Role::orderBy('name')->get(['id', 'name'])
        );
    }
}
