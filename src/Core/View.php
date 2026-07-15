<?php

// Clase View que se encarga de renderizar las vistas HTML. Tiene un método render() que recibe el nombre de la vista y un array de datos, 
// y luego incluye el archivo de la vista dentro de un layout principal. También tiene un método partial() para incluir vistas parciales
// (como cabeceras o pies de página) y un método escape() para escapar valores antes de mostrarlos en HTML, evitando vulnerabilidades XSS.

namespace OfficeHub\Core;

class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $viewFile   = BASE_PATH . '/src/Views/' . $view . '.php';
        $layoutFile = BASE_PATH . '/src/Views/layouts/main.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Vista no encontrada: {$view}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }

    public static function partial(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = BASE_PATH . '/src/Views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Parcial no encontrado: {$view}");
        }

        require $viewFile;
    }

    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
