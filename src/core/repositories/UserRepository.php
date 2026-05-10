<?php 

    class UserRepository {

        public static function findIdByEmail(string $email){
            $mysqli = Database::connect();

            $row = Database::query($mysqli, 
                "SELECT id FROM user WHERE email = ?", 
                true, "s", [$email], true
            );

            $mysqli->close();
            return $row;
        }

        public static function getUserConfig(mysqli $mysqli, ?int $user_id = null, ?string $email = null){

            if ($user_id !== null) {
                return Database::query(
                    $mysqli,
                    "SELECT * FROM users_config WHERE user_id = ?",
                    true,
                    "i",
                    [$user_id],
                    true
                );
            }

            if ($email !== null) {
                return Database::query(
                    $mysqli,
                    "SELECT * FROM users_config 
                    WHERE user_id = (SELECT id FROM user WHERE email = ?)",
                    true,
                    "s",
                    [$email],
                    true
                );
            }

            return null;
        }

    }