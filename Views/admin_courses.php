<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="row mb-5 align-items-center">
            <div class="col-md-8">
                <h1 class="display-6 fw-bold">Panel de administración de cursos</h1>
                <p class="text-muted">Gestiona contenido, categorías y estado de publicación desde un único panel.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= url('index.php?action=dashboard') ?>" class="btn btn-outline-secondary rounded-pill">Volver al dashboard</a>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success rounded-4 mb-4"><?= e($success) ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <h5 class="fw-bold mb-3">Crear nuevo curso</h5>
                    <form method="POST" action="<?= url('index.php?action=admin-courses') ?>">
                        <input type="hidden" name="create_course" value="1">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Título</label>
                            <input name="nombre" type="text" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Código</label>
                            <input name="codigo" type="text" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Categoría</label>
                            <select name="categoria_id" class="form-select form-select-sm">
                                <option value="">Seleccionar categoría</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= e($category['id']) ?>"><?= e($category['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Instructor</label>
                            <input name="instructor" type="text" class="form-control form-control-sm" placeholder="Ej. María Pérez">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Duración</label>
                            <input name="duracion" type="text" class="form-control form-control-sm" placeholder="Ej. 6 semanas">
                        </div>
                        <button class="btn btn-primary-premium w-100">Guardar curso</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Cursos existentes</h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary py-2 px-3">Total: <?= count($courses) ?></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="text-muted small text-uppercase">
                                <tr>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Categoría</th>
                                    <th scope="col">Instructor</th>
                                    <th scope="col">Duración</th>
                                    <th scope="col">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td><?= e($course['nombre']) ?></td>
                                        <td><?= e($course['categoria_nombre'] ?? 'Sin categoría') ?></td>
                                        <td><?= e($course['instructor'] ?: 'Sin definir') ?></td>
                                        <td><?= e($course['duracion'] ?: 'N/A') ?></td>
                                        <td>
                                            <span class="badge <?= $course['estado'] === 'publicado' ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' ?>">
                                                <?= e(ucfirst($course['estado'])) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
