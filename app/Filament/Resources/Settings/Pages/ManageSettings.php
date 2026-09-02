<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\Schemas\SettingForm;
use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use App\Settings\SettingsFormData;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;

class ManageSettings extends Page
{
    protected static string $resource = SettingResource::class;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(SettingsFormData $settings): void
    {
        $this->form->fill($settings->load());
    }

    public function form(Schema $schema): Schema
    {
        return SettingForm::configure($schema)->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('settings-form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label(__('Save settings'))
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ]),
                ]),
        ]);
    }

    public function save(SettingsFormData $settings): void
    {
        Gate::authorize('update', Setting::query()->first() ?? new Setting);

        $settings->save($this->form->getState());

        Notification::make()
            ->success()
            ->title(__('Settings saved successfully.'))
            ->send();
    }
}
