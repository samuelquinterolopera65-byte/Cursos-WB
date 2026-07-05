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
    public function create($nombre, $email, $password, $rol_id, $permisos = '') {
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, email, password, rol_id, permisos) VALUES (:nombre, :email, :password, :rol_id, :permisos)");
        if ($stmt->execute([
            'nombre' => $nombre,
            'email' => $email,
            'password' => $hashed_pass,
            'rol_id' => $rol_id,
            'permisos' => $permisos
        ])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Actualizar detalles del usuario
     */
    public function update($id, $nombre, $email, $rol_id, $password = null, $permisos = '') {
        if (!empty($password)) {
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE usuarios SET nombre = :nombre, email = :email, password = :password, rol_id = :rol_id, permisos = :permisos WHERE id = :id");
            return $stmt->execute([
                'nombre' => $nombre,
                'email' => $email,
                'password' => $hashed_pass,
                'rol_id' => $rol_id,
                'permisos' => $permisos,
                'id' => $id
            ]);
        } else {
            $stmt = $this->db->prepare("UPDATE usuarios SET nombre = :nombre, email = :email, rol_id = :rol_id, permisos = :permisos WHERE id = :id");
            return $stmt->execute([
                'nombre' => $nombre,
                'email' => $email,
                'rol_id' => $rol_id,
                'permisos' => $permisos,
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
            
            // Si son Administrador (rol_id = 1), verificar cadena de permisos
            if ($user['rol_id'] == 1) {
                if (empty($user['permisos'])) {
                    return false;
                }
                $perms = explode(',', $user['permisos']);
                return in_array($permission, $perms);
            }
            
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
