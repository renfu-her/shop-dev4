<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\RichEditor;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        RichEditor::configureUsing(function (RichEditor $editor): void {
            $editor->toolbarButtons([
                'bold',
                'italic',
                'underline',
                'strike',
                'link',
                'bulletList',
                'orderedList',
                'blockquote',
                'codeBlock',
                'h1',
                'h2',
                'h3',
                'undo',
                'redo',
            ]);
        });
    }
}
