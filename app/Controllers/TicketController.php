<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

class TicketController extends Controller {
    public function index(): void {
        require __DIR__ . '/../../tickets/index.php';
    }

    public function show(): void {
        require __DIR__ . '/../../tickets/show.php';
    }

    public function create(): void {
        require __DIR__ . '/../../tickets/create.php';
    }

    public function edit(): void {
        require __DIR__ . '/../../tickets/edit.php';
    }
}
