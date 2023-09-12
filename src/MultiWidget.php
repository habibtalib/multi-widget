<?php

namespace Kenepa\MultiWidget;

use Exception;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Kenepa\MultiWidget\Concerns\HasTabs;

class MultiWidget extends Widget
{
    use HasTabs;

    protected static string $view = 'multi-widget::multi-widget';

    public int $currentWidget = 0;

    public ?Model $record = null;

    /**
     * Fully qualified class names of the widgets.
     */
    public array $widgets = [];

    public function mount()
    {
        $tabSessionKey = $this->getMultiWidgetTabSessionKey();

        if (session()->has($tabSessionKey) && $this->shouldPersistMultiWidgetTabsInSession()) {
            $this->currentWidget = session()->get($tabSessionKey);
        }

        if (count($this->widgets) < 1) {
            throw new Exception('A multi widget must have at least 1 widget.');
        }
    }

    /**
     * Selects a widget by its index.
     */
    public function selectWidget(int $index): void
    {
        $this->currentWidget = $index;

        if ($this->shouldPersistMultiWidgetTabsInSession()) {
            session()->put(
                $this->getMultiWidgetTabSessionKey(),
                $this->currentWidget
            );
        }
    }

    /**
     * Returns the HTML of the currently selected widget.
     */
    public function getWidgetHTMLProperty(): string
    {
        return Blade::render(
            "@livewire('" . $this->widgets[$this->currentWidget] . "', ['record' => \$record])",
            ['record' => $this->record]
        );
    }

    /**
     * Get the display name for a widget.
     *
     * @param  string  $widget The fully qualified class name of the widget.
     * @return string The display name of the widget.
     */
    public function getWidgetDisplayName($widget): string
    {
        $widget = new $widget;

        try {
            return $widget->getDisplayName();
        } catch (Exception $e) {
            return Str::of($widget::class)
                ->afterLast('\\')
                ->kebab()
                ->replace('-', ' ')
                ->title();
        }
    }
}
