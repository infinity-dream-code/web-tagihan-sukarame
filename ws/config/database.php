<?php

if (!class_exists('Database')) {
    class Database
    {
        public function getConnection()
        {
            $pick = static function (array $keys, $default = '') {
                foreach ($keys as $key) {
                    if (function_exists('env')) {
                        $value = env($key);
                        if ($value !== null && $value !== '') {
                            return $value;
                        }
                    }
                    $value = getenv($key);
                    if ($value !== false && $value !== '') {
                        return $value;
                    }
                }

                return $default;
            };

            $host = $pick(['WS_DB_HOST', 'DB_HOST_DATA_MYSQL'], '127.0.0.1');
            $port = $pick(['WS_DB_PORT', 'DB_PORT_DATA_MYSQL'], '3306');
            $name = $pick(['WS_DB_DATABASE', 'DB_DATABASE_DATA_MYSQL']);
            $user = $pick(['WS_DB_USERNAME', 'DB_USERNAME_DATA_MYSQL'], 'root');
            $pass = $pick(['WS_DB_PASSWORD', 'DB_PASSWORD_DATA_MYSQL'], '');

            if ($name === '' || $name === null) {
                throw new Exception('Database billing belum dikonfigurasi (WS_DB_DATABASE)');
            }

            return new PDO(
                "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }
    }
}
