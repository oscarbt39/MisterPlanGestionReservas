<?php

namespace App\View;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class View {
    public static function render(string $template, array $data = []): void {
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $twig = new Environment($loader, [
            'autoescape' => 'html', 
        ]);

        echo $twig->render($template, $data);
    }
}