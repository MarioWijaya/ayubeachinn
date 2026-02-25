<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Instantiate the Livewire component and render
$component = new App\Livewire\Pegawai\Dashboard();
$component->mount();
$view = $component->render();
// $view is a \Illuminate\View\View
echo $view->render();

echo "\n\n--- FINISHED ---\n";
