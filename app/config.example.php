<?php
/**
 * Modelo de configuração local (para alojamento sem variáveis de ambiente).
 *
 * Copiar para config.local.php e preencher. O config.local.php está no .gitignore
 * e nunca deve ser enviado para o repositório.
 * Com Docker não é preciso: os valores vêm do ficheiro .env (ver .env.example).
 */

return [
    'DB_HOST'          => 'localhost',
    'DB_PORT'          => '3306',
    'DB_NAME'          => 'gm_helpdesk',
    'DB_USER'          => '',
    'DB_PASS'          => '',

    'SMTP_HOST'        => '',
    'SMTP_PORT'        => '465',
    'SMTP_SECURE'      => 'ssl',
    'SMTP_AUTH'        => 'true',
    'SMTP_USER'        => '',
    'SMTP_PASS'        => '',
    'MAIL_FROM'        => '',
    'MAIL_FROM_NAME'   => 'GM HelpDesk',

    'APP_URL'          => '',
    'APP_EMAIL_DOMAIN' => 'escola-demo.example',
];
