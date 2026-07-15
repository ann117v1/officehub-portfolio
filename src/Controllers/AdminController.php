<?php

//Es el controlador del panel de administración. Por ahora maneja la gestión de usuarios: listar todos los usuarios, crear uno nuevo y activar/desactivar cuentas.
// Solo pueden acceder usuarios con rol admin.

namespace OfficeHub\Controllers;

use OfficeHub\Core\Controller;
use OfficeHub\Core\Session;
use OfficeHub\Models\SupportCategory;
use OfficeHub\Models\User;

class AdminController extends Controller
{
    // GET /admin/users
    public function users(array $params = []): void
    {
        $this->requireAdmin();

        $this->view('admin/users', [
            'title'   => 'Administración de usuarios',
            'users'   => User::all(),
            'error'   => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
        ]);
    }

    // POST /admin/users
    public function createUser(array $params = []): void
    {
        $this->requireAdmin();

        $username = trim($this->request->input('username', ''));
        $email    = trim($this->request->input('email', ''));
        $displayTitle = trim($this->request->input('display_title', ''));
        $password = $this->request->input('password', '');
        $role     = $this->request->input('role', 'developer');
        $repoAccess = $this->request->input('repo_access', '0') === '1' ? 1 : 0;
        $supportRole = $this->request->input('support_role', 'none');
        $notificationsEnabled = $this->request->input('notifications_enabled', '0') === '1' ? 1 : 0;
        $commitNotificationsEnabled = $this->request->input('commit_notifications_enabled', '0') === '1' ? 1 : 0;
        $boardEmailNotificationsEnabled = $this->request->input('board_email_notifications_enabled', '0') === '1' ? 1 : 0;
        $commitEmailNotificationsEnabled = $this->request->input('commit_email_notifications_enabled', '0') === '1' ? 1 : 0;

        if (empty($username) || empty($email) || empty($password)) {
            Session::flash('error', 'Todos los campos son obligatorios.');
            $this->redirect('/admin/users');
        }

        if (!in_array($role, ['admin', 'developer', 'viewer'], true)) {
            $role = 'developer';
        }

        if (!in_array($supportRole, ['none', 'support_admin', 'support_viewer'], true)) {
            $supportRole = 'none';
        }

        if ($role === 'admin') {
            $repoAccess = 1;
            $supportRole = 'support_admin';
        }

        if (User::findByUsername($username)) {
            Session::flash('error', "El usuario \"{$username}\" ya existe.");
            $this->redirect('/admin/users');
        }

        if (!$notificationsEnabled) {
            $commitNotificationsEnabled = 0;
        }

        User::create(
            $username,
            $email,
            $displayTitle,
            $password,
            $role,
            $repoAccess,
            $supportRole,
            $notificationsEnabled,
            $commitNotificationsEnabled,
            $boardEmailNotificationsEnabled,
            $commitEmailNotificationsEnabled
        );

        Session::flash('success', "Usuario \"{$username}\" creado correctamente.");
        $this->redirect('/admin/users');
    }

    // POST /admin/users/{id}/toggle
    public function toggleUser(array $params = []): void
    {
        $this->requireAdmin();

        $id   = (int)$params['id'];
        $self = $this->currentUser();

        if ($id === $self['id']) {
            Session::flash('error', 'No podés desactivar tu propia cuenta.');
            $this->redirect('/admin/users');
        }

        User::toggleActive($id);
        Session::flash('success', 'Estado del usuario actualizado correctamente.');
        $this->redirect('/admin/users');
    }

    // GET /admin/areas
    public function areas(array $params = []): void
    {
        $this->requireAdmin();
        $this->view('admin/areas', [
            'title' => 'Gestión de áreas',
            'areas' => \OfficeHub\Models\Area::allWithRepoCount(),
        ]);
    }

    public function storeArea(array $params = []): void
    {
        $this->requireAdmin();
        $name  = trim($this->request->input('name', ''));
        $color = $this->request->input('color', '#58a6ff');
        $slug  = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $name)));

        if (empty($name)) {
            Session::flash('error', 'El nombre es obligatorio.');
            $this->redirect('/admin/areas');
        }

        \OfficeHub\Models\Area::create($name, $slug, $color);
        Session::flash('success', "Área \"{$name}\" creada.");
        $this->redirect('/admin/areas');
    }

    public function updateArea(array $params = []): void
    {
        $this->requireAdmin();
        $id    = (int)$params['id'];
        $name  = trim($this->request->input('name', ''));
        $color = $this->request->input('color', '#58a6ff');

        if (empty($name)) {
            Session::flash('error', 'El nombre es obligatorio.');
            $this->redirect('/admin/areas');
        }

        \OfficeHub\Models\Area::update($id, $name, $color);
        Session::flash('success', 'Área actualizada.');
        $this->redirect('/admin/areas');
    }

    public function deleteArea(array $params = []): void
    {
        $this->requireAdmin();
        $id   = (int)$params['id'];
        $area = \OfficeHub\Models\Area::findById($id);

        if (!$area) {
            Session::flash('error', 'Área no encontrada.');
            $this->redirect('/admin/areas');
        }

        \OfficeHub\Models\Repository::clearArea($id);
        \OfficeHub\Models\Area::delete($id);
        Session::flash('success', "Área \"{$area['name']}\" eliminada.");
        $this->redirect('/admin/areas');
    }
    // POST /admin/users/{id}/role
    public function changeRole(array $params = []): void
    {
        $this->requireAdmin();

        $id   = (int)$params['id'];
        $role = $this->request->input('role', '');
        $self = $this->currentUser();

        if ($id === $self['id']) {
            Session::flash('error', 'No podés cambiar tu propio rol.');
            $this->redirect('/admin/users');
        }

        if (!in_array($role, ['admin', 'developer', 'viewer'], true)) {
            Session::flash('error', 'Rol inválido.');
            $this->redirect('/admin/users');
        }

        User::updateRole($id, $role);
        Session::flash('success', 'Rol actualizado correctamente.');
        $this->redirect('/admin/users');
    }

    public function changeAccess(array $params = []): void
    {
        $this->requireAdmin();

        $id = (int)$params['id'];
        $role = $this->request->input('role', 'viewer');
        $displayTitle = trim($this->request->input('display_title', ''));
        $repoAccess = $this->request->input('repo_access', '0') === '1' ? 1 : 0;
        $supportRole = $this->request->input('support_role', 'none');
        $notificationsEnabled = $this->request->input('notifications_enabled', '0') === '1' ? 1 : 0;
        $commitNotificationsEnabled = $this->request->input('commit_notifications_enabled', '0') === '1' ? 1 : 0;
        $boardEmailNotificationsEnabled = $this->request->input('board_email_notifications_enabled', '0') === '1' ? 1 : 0;
        $commitEmailNotificationsEnabled = $this->request->input('commit_email_notifications_enabled', '0') === '1' ? 1 : 0;
        $self = $this->currentUser();

        if (!in_array($role, ['admin', 'developer', 'viewer'], true)) {
            $role = 'viewer';
        }

        if (!in_array($supportRole, ['none', 'support_admin', 'support_viewer'], true)) {
            $supportRole = 'none';
        }

        if ($id === (int)$self['id'] && $role !== 'admin') {
            Session::flash('error', 'No podes quitarte tu propio rol de admin total desde esta pantalla.');
            $this->redirect('/admin/users');
        }

        if ($role === 'admin') {
            $repoAccess = 1;
            $supportRole = 'support_admin';
        }

        if (!$notificationsEnabled) {
            $commitNotificationsEnabled = 0;
        }

        User::updatePermissions(
            $id,
            $displayTitle,
            $role,
            $repoAccess,
            $supportRole,
            $notificationsEnabled,
            $commitNotificationsEnabled,
            $boardEmailNotificationsEnabled,
            $commitEmailNotificationsEnabled
        );
        Session::flash(
            'success',
            $role === 'admin'
                ? 'Permisos actualizados. Admin total conserva acceso a todos los modulos.'
                : 'Permisos actualizados correctamente.'
        );
        $this->redirect('/admin/users');
    }

    public function deleteUser(array $params = []): void
    {
        $this->requireAdmin();

        $id = (int)$params['id'];
        $self = $this->currentUser();

        if ($id === (int)$self['id']) {
            Session::flash('error', 'No podes eliminar tu propia cuenta.');
            $this->redirect('/admin/users');
        }

        try {
            User::delete($id);
            Session::flash('success', 'Usuario eliminado correctamente.');
        } catch (\Throwable $exception) {
            Session::flash('error', 'No se pudo eliminar el usuario porque tiene actividad, repositorios o articulos asociados. Podes desactivarlo.');
        }

        $this->redirect('/admin/users');
    }

    public function support(array $params = []): void
    {
        $this->requireSupportAdmin();

        $this->view('admin/support', [
            'title'      => 'Secciones de base de conocimiento',
            'categories' => SupportCategory::allWithArticleCount(),
            'error'      => Session::getFlash('error'),
            'success'    => Session::getFlash('success'),
        ]);
    }

    public function storeSupportCategory(array $params = []): void
    {
        $this->requireSupportAdmin();

        $name = trim($this->request->input('name', ''));
        $color = $this->request->input('color', '#58a6ff');

        if ($name === '') {
            Session::flash('error', 'El nombre es obligatorio.');
            $this->redirect('/admin/support');
        }

        SupportCategory::create($name, $this->uniqueSupportCategorySlug($name), $color);
        Session::flash('success', 'Seccion creada.');
        $this->redirect('/admin/support');
    }

    public function updateSupportCategory(array $params = []): void
    {
        $this->requireSupportAdmin();

        $id = (int)$params['id'];
        $category = SupportCategory::findById($id);
        $name = trim($this->request->input('name', ''));
        $color = $this->request->input('color', '#58a6ff');

        if (!$category) {
            Session::flash('error', 'Seccion no encontrada.');
            $this->redirect('/admin/support');
        }

        if ($name === '') {
            Session::flash('error', 'El nombre es obligatorio.');
            $this->redirect('/admin/support');
        }

        $slug = $name === $category['name'] ? $category['slug'] : $this->uniqueSupportCategorySlug($name);
        SupportCategory::update($id, $name, $slug, $color);
        Session::flash('success', 'Seccion actualizada.');
        $this->redirect('/admin/support');
    }

    public function deleteSupportCategory(array $params = []): void
    {
        $this->requireSupportAdmin();

        $id = (int)$params['id'];
        SupportCategory::delete($id);
        Session::flash('success', 'Seccion eliminada.');
        $this->redirect('/admin/support');
    }

    private function uniqueSupportCategorySlug(string $name): string
    {
        $base = $this->slugify($name) ?: 'categoria';
        $slug = $base;
        $i = 2;

        while (SupportCategory::findBySlug($slug)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function slugify(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = strtolower((string)$value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        return trim((string)$value, '-');
    }
}
