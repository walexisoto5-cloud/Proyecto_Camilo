<?php
require_once __DIR__ . '/AprendizController.php';
require_once __DIR__ . '/InstructorController.php';
require_once __DIR__ . '/FichaController.php';
require_once __DIR__ . '/ExcusaController.php';
require_once __DIR__ . '/ComiteController.php';

/**
 * GestionController - Fachada y delegador central para los módulos de gestión (SRP)
 */
class GestionController
{
    // 1. Delegación de Aprendices
    public function viewAprendices(): void
    {
        (new AprendizController())->viewAprendices();
    }

    public function crearAprendiz(): void
    {
        (new AprendizController())->crearAprendiz();
    }

    public function importarAprendicesCsv(): void
    {
        (new AprendizController())->importarAprendicesCsv();
    }

    public function actualizarAprendiz(): void
    {
        (new AprendizController())->actualizarAprendiz();
    }

    public function eliminarAprendiz(): void
    {
        (new AprendizController())->eliminarAprendiz();
    }

    // 2. Delegación de Instructores
    public function viewInstructores(): void
    {
        (new InstructorController())->viewInstructores();
    }

    public function crearInstructor(): void
    {
        (new InstructorController())->crearInstructor();
    }

    public function actualizarInstructor(): void
    {
        (new InstructorController())->actualizarInstructor();
    }

    public function eliminarInstructor(): void
    {
        (new InstructorController())->eliminarInstructor();
    }

    // 3. Delegación de Fichas
    public function viewFichas(): void
    {
        (new FichaController())->viewFichas();
    }

    public function crearFicha(): void
    {
        (new FichaController())->crearFicha();
    }

    public function actualizarFicha(): void
    {
        (new FichaController())->actualizarFicha();
    }

    public function eliminarFicha(): void
    {
        (new FichaController())->eliminarFicha();
    }

    // 4. Delegación de Excusas
    public function viewExcusas(): void
    {
        (new ExcusaController())->viewExcusas();
    }

    public function procesarExcusa(string $nuevoEstado): void
    {
        (new ExcusaController())->procesarExcusa($nuevoEstado);
    }

    // 5. Delegación de Comité
    public function viewComite(): void
    {
        (new ComiteController())->viewComite();
    }

    public function actualizarEstadoComite(): void
    {
        (new ComiteController())->actualizarEstadoComite();
    }
}
