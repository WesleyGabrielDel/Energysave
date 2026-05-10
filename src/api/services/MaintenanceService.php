<?php 
    /**
     * Código de manutenção e limpeza do banco de dados. Isso garante que somente 
     * os dados necessários estarão guardados.
     */

    require "../../bootstrap.php";
    
    $mysqli = Database::connect();

    // Deleta todos os tokens de sessão que estão expirados
    Database::query(
        $mysqli,
        "DELETE FROM remember_tokens WHERE exp < ?",
        false,
        [time()]
    );

    // Deleta todos os códigos de email que estão expirados
    Database::query(
        $mysqli,
        "DELETE FROM email_codes WHERE time_exp < ?",
        false,
        [time()]
    );