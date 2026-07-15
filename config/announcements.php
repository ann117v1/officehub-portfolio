<?php

return [
    'active' => true,
    'id' => '2026-07-novedades-visibles',
    'eyebrow' => 'Actualizacion de OfficeHub',
    'version' => 'v1.2.0',
    'title' => 'Nuevas herramientas para tablero, documentacion y avisos',
    'description' => 'OfficeHub suma mejoras para organizar tareas, documentar procedimientos con imagenes y recibir avisos importantes desde la web y por correo.',
    'sections' => [
        [
            'label' => 'Nuevo',
            'tone' => 'blue',
            'items' => [
                'Restablecimiento de password desde el login.',
                'Notificaciones por correo para tareas asignadas y commits.',
                'Boton fijo de novedades para consultar cambios recientes.',
            ],
        ],
        [
            'label' => 'Mejorado',
            'tone' => 'purple',
            'items' => [
                'Tablero Kanban con detalle de tarjeta, comentarios, adjuntos y trazabilidad.',
                'Historial de tareas completadas con vista de auditoria.',
                'Documentacion con imagenes pegadas por Ctrl+V y adjuntos acumulables.',
            ],
        ],
        [
            'label' => 'Corregido',
            'tone' => 'green',
            'items' => [
                'Prevencion de tarjetas duplicadas por multiples envios.',
                'Visibilidad de acciones compactas en las cards del tablero.',
                'Respuestas de comentarios conservan la cita del mensaje respondido.',
            ],
        ],
    ],
    'previous_versions' => [
        [
            'version' => 'v1.1.0',
            'date' => '2026-06-28',
            'title' => 'Gestion de soporte y documentacion',
            'tag' => 'Consultar',
        ],
        [
            'version' => 'v1.0.0',
            'date' => '2026-06-15',
            'title' => 'Lanzamiento del tablero Kanban',
            'tag' => 'Consultar',
        ],
    ],
    'refresh_required' => true,
];
