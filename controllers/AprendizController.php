<?php
require_once __DIR__ . '/../config/database.php';

class AprendizController
{
    public static function getFichas()
    {
        $database = new Database();
        $db = $database->getConnection();

        $fichas = [];

        if ($db) {
            $res = mysqli_query($db, "SELECT id_ficha, numero_ficha FROM ficha");

            if ($res && mysqli_num_rows($res) > 0) {
                while ($fila = mysqli_fetch_assoc($res)) {
                    $fichas[] = $fila;
                }
            }
        }

        return $fichas;
    }
}
