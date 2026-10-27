<?php
/**
 * Configuração central da aplicação.
 *
 * Os valores são lidos, por esta ordem:
 *   1. do ficheiro config.local.php (não versionado; copiar de config.example.php);
 *   2. das variáveis de ambiente (ex.: definidas no docker-compose.yml);
 *   3. dos valores por omissão abaixo (nunca contêm credenciais).
 */

if (!function_exists('obterConfig')) {
    function obterConfig($chave)
    {
        static $config = null;

        if ($config === null) {
            $padrao = [
                'DB_HOST'          => 'localhost',
                'DB_PORT'          => '3306',
                'DB_NAME'          => 'gm_helpdesk',
                'DB_USER'          => '',
                'DB_PASS'          => '',

                'SMTP_HOST'        => '',
                'SMTP_PORT'        => '465',
                'SMTP_SECURE'      => 'ssl',  // 'ssl', 'tls' ou '' (sem encriptação)
                'SMTP_AUTH'        => 'true',
                'SMTP_USER'        => '',
                'SMTP_PASS'        => '',
                'MAIL_FROM'        => '',
                'MAIL_FROM_NAME'   => 'GM HelpDesk',

                'APP_URL'          => '',     // ex.: http://localhost:8080 (usado nos links dos emails)
                'APP_EMAIL_DOMAIN' => 'escola-demo.example',
            ];

            $local = file_exists(__DIR__ . '/config.local.php') ? require __DIR__ . '/config.local.php' : [];

            $config = [];
            foreach ($padrao as $nome => $valor) {
                $ambiente = getenv($nome);
                if (isset($local[$nome])) {
                    $config[$nome] = $local[$nome];
                } elseif ($ambiente !== false) {
                    $config[$nome] = $ambiente;
                } else {
                    $config[$nome] = $valor;
                }
            }
        }

        return $config[$chave] ?? null;
    }
}
