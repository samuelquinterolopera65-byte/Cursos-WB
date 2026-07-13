<?php
class Usuario {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    /**
     * Autenticar un usuario por correo y contraseña
     */
    public function authenticate($email, $password) {
        $stmt = $this->db->prepare("SELECT u.*, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Actualizar la marca de tiempo del último acceso al autenticar
            $this->updateLastAccess($user['id']);
            return $user;
        }
        return false;
    }

    /**
     * Obtener todos los usuarios
     */
    public function getAll() {
        $query = "SELECT u.*, r.nombre AS rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id ORDER BY u.fecha_registro DESC";
        return $this->db->query($query)->fetchAll();
    }

    /**
     * Obtener usuario por ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Obtener usuario por correo electrónico
     */
    public function getByEmail($email) {
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetchColumn();
    }

    /**
     * Crear usuario (retorna el ID de inserción o false)
     */
    public function create($nombre, $email, $password, $rol_id, $permisos = '', $asignatura = null, $foto = null) {
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, email, password, rol_id, permisos, asignatura, foto) VALUES (:nombre, :email, :password, :rol_id, :permisos, :asignatura, :foto)");
        if ($stmt->execute([
            'nombre' => $nombre,
            'email' => $email,
            'password' => $hashed_pass,
            'rol_id' => $rol_id,
            'permisos' => $permisos,
            'asignatura' => $asignatura,
            'foto' => $foto
        ])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Actualizar detalles del usuario
     */
    public function update($id, $nombre, $email, $rol_id, $password = null, $permisos = '', $asignatura = null, $foto = null) {
        if (!empty($password)) {
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE usuarios SET nombre = :nombre, email = :email, password = :password, rol_id = :rol_id, permisos = :permisos, asignatura = :asignatura, foto = :foto WHERE id = :id");
            return $stmt->execute([
                'nombre' => $nombre,
                'email' => $email,
                'password' => $hashed_pass,
                'rol_id' => $rol_id,
                'permisos' => $permisos,
                'asignatura' => $asignatura,
                'foto' => $foto,
                'id' => $id
            ]);
        } else {
            $stmt = $this->db->prepare("UPDATE usuarios SET nombre = :nombre, email = :email, rol_id = :rol_id, permisos = :permisos, asignatura = :asignatura, foto = :foto WHERE id = :id");
            return $stmt->execute([
                'nombre' => $nombre,
                'email' => $email,
                'rol_id' => $rol_id,
                'permisos' => $permisos,
                'asignatura' => $asignatura,
                'foto' => $foto,
                'id' => $id
            ]);
        }
    }

    /**
     * Eliminar usuario
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Contar usuarios
     */
    public function countAll() {
        return $this->db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    }

    /**
     * Obtener todos los roles
     */
    public function getRoles() {
        return $this->db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
    }

    /**
     * Actualizar marca de tiempo del último acceso
     */
    public function updateLastAccess($user_id) {
        $stmt = $this->db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $user_id]);
    }

    /**
     * Contar usuarios en línea (activos en los últimos 5 minutos)
     */
    public function countOnline() {
        try {
            return $this->db->query("SELECT COUNT(*) FROM usuarios WHERE ultimo_acceso >= NOW() - INTERVAL 5 MINUTE")->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Obtener el nombre del rol por ID
     */
    public function getRoleName($roleId) {
        try {
            $stmt = $this->db->prepare("SELECT nombre FROM roles WHERE id = :id");
            $stmt->execute(['id' => (int) $roleId]);
            $role = $stmt->fetch();
            return $role ? $role['nombre'] : 'Sin rol';
        } catch (PDOException $e) {
            return 'Sin rol';
        }
    }

    /**
     * Obtener permisos por rol
     */
    private function getRolePermissions($roleId) {
        $roleId = (int) $roleId;
        $permissionsByRole = [
            1 => [
                'crear_cursos', 'editar_cursos', 'eliminar_cursos', 'gestionar_usuarios', 'gestionar_servicios',
                'descargar_excel', 'gestionar_ajustes', 'instalar_complementos', 'crear_cuentas',
                'configurar_estetica', 'reportes_globales', 'ver_panel_tecnico', 'ver_administracion_sitio'
            ],
            2 => ['crear_cursos', 'editar_cursos', 'ver_categorias', 'ver_panel_cursos', 'ver_cursos', 'anadir_nuevo_curso'],
            3 => ['gestionar_materiales', 'gestionar_alumnos', 'poner_notas', 'activar_edicion', 'ver_calificaciones', 'ver_herramientas_didacticas', 'gestionar_asignaturas'],
            4 => ['ver_entregas', 'calificar_alumnos', 'ver_aulas_virtuales', 'ver_libro_calificaciones'],
            5 => ['ver_cursos', 'consumir_contenidos', 'entregar_tareas', 'ver_notas', 'ver_progreso']
        ];

        return $permissionsByRole[$roleId] ?? [];
    }

    /**
     * Determinar si el usuario puede acceder al área de gestión
     */
    public function canAccessManage($user_id) {
        try {
            $stmt = $this->db->prepare("SELECT rol_id FROM usuarios WHERE id = :id");
            $stmt->execute(['id' => $user_id]);
            $user = $stmt->fetch();
            return $user && in_array((int) $user['rol_id'], [1, 2], true);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Comprobar si el usuario tiene un permiso específico
     */
    public function hasPermission($user_id, $permission) {
        try {
            $stmt = $this->db->prepare("SELECT rol_id, permisos FROM usuarios WHERE id = :id");
            $stmt->execute(['id' => $user_id]);
            $user = $stmt->fetch();

            if (!$user) {
                return false;
            }

            $rolePermissions = $this->getRolePermissions((int) $user['rol_id']);
            if (in_array($permission, $rolePermissions, true)) {
                return true;
            }

            if (!empty($user['permisos'])) {
                $perms = array_filter(array_map('trim', explode(',', $user['permisos'])));
                return in_array($permission, $perms, true);
            }

            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
