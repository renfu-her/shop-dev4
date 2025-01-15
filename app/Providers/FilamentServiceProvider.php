<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\RichEditor;
use Rawilk\FilamentQuill\Filament\Forms\Components\QuillEditor;
use Rawilk\FilamentQuill\Enums\ToolbarButton;

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
                'h1',
                'h2',
                'h3',
                'bold',
                'italic',
                'underline',
                'strike',
                'link',
                'bulletList',
                'orderedList',
                'blockquote',
                'codeBlock',
                'undo',
                'redo',
            ]);
        });

        QuillEditor::configureUsing(function (QuillEditor $editor): void {
            $editor->toolbarButtons([
                ToolbarButton::Font,
                ToolbarButton::Size,
                ToolbarButton::Bold,
                ToolbarButton::Italic,
                ToolbarButton::Underline,
                ToolbarButton::Strike,
                ToolbarButton::TextColor,
                ToolbarButton::BackgroundColor,
                ToolbarButton::TextAlign,
                ToolbarButton::Indent,
                ToolbarButton::Link,
                ToolbarButton::Image,
                ToolbarButton::BlockQuote,
                ToolbarButton::OrderedList,
                ToolbarButton::UnorderedList,
                ToolbarButton::Undo,
                ToolbarButton::Redo,
            ]);
        });
    }
}
