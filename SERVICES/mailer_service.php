<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../../../../PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../../../../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../../../../PHPMailer-master/src/SMTP.php';

function crear_mailer_activaciones()
{
    $config = require __DIR__ . '/../CONFIG/mail_config.php';

    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    $mail->Host = $config['host'];
    $mail->Port = $config['port'];
    $mail->SMTPAuth = $config['smtp_auth'];
    $mail->SMTPSecure = $config['smtp_secure'];
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];

    $mail->setFrom($config['from_email'], $config['from_name']);

    return $mail;
}

function enviar_correo_canje_premio(array $data)
{
    $config = require __DIR__ . '/../CONFIG/mail_config.php';

    try {
        $mail = crear_mailer_activaciones();

        foreach ($config['destinatarios_canje'] as $destinatario) {
            $mail->addAddress($destinatario);
        }

        $asunto = 'Canje de premio - Activaciones 2.0';

        $mensaje = ''
            . "Se generó un canje de premio en Activaciones 2.0.\n\n"
            . 'Ejecutivo: ' . $data['nombre_empleado'] . "\n"
            . 'ID empleado: ' . $data['id_empleado'] . "\n"
            . 'Campaña: ' . $data['campania'] . "\n"
            . 'Modalidad: ' . $data['modalidad'] . "\n"
            . 'Incentivo: ' . $data['incentivo'] . "\n"
            . 'Folio de canje: ' . $data['folio_canje'] . "\n"
            . 'Ventas utilizadas: ' . $data['cantidad_ventas_usadas'] . "\n"
            . 'Fecha del canje: ' . $data['fecha_canje'] . "\n";

        $mail->Subject = $asunto;
        $mail->Body = $mensaje;

        $mail->send();

        return [
            'ok' => true
        ];
    } catch (Exception $e) {
        return [
            'ok' => false,
            'mensaje' => $e->getMessage()
        ];
    }
}