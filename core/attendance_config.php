<?php
/**
 * Configuración del módulo de asistencia docente.
 * Edite estos valores según la infraestructura de su institución.
 */

return [

    // ── IPs permitidas (rango de la red interna del colegio) ──────────
    // Solo desde estas IPs el docente podrá marcar su asistencia.
    // Puede agregar IPs individuales o rangos con comodines.
    'ip_permitidas' => [
        '192.168.1.*',      // Rango WiFi/LAN del colegio (edite según su red)
        '127.0.0.1',        // Localhost (para pruebas)
        '::1',              // IPv6 localhost (para pruebas)
    ],

    // ── Horario laboral ──────────────────────────────────────────────
    'hora_entrada'       => '07:00',   // Hora de inicio de labores
    'hora_limite_tardanza' => '07:15', // Después de esta hora se marca TARDANZA
    'hora_cierre'        => '14:00',   // Después de esta hora NO se puede marcar

    // ── Mensajes personalizados ──────────────────────────────────────
    'mensaje_ip_invalida'   => 'Solo puede marcar asistencia desde las instalaciones del colegio (IP no autorizada).',
    'mensaje_fuera_horario' => 'El horario de marcación ha finalizado. Contacte a Dirección.',
    'mensaje_ya_registrado' => 'Ya registró su asistencia el día de hoy.',
];
