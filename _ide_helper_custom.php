<?php

/**
 * IDE Helper for Filament & Livewire Custom Macros and Properties
 */

namespace Filament\Tables {
    /**
     * @method self mobileCards()
     * @method self mobileCardFeatured(string $column, string $color = 'primary')
     */
    class Table {}
}

namespace Filament\Tables\Columns {
    /**
     * @method static static make(string|null $name = null)
     * @method self color(string|\Closure|array|null $color)
     * @method self weight(string|\Closure|null $weight)
     * @method self size(string|\Closure|null $size)
     * @method self badge()
     * @method self boolean()
     * @method self label(string|\Closure|null $label)
     * @method self alignment(string|\Closure|null $alignment)
     * @method self sortable(bool|\Closure $condition = true)
     * @method self searchable(bool|\Closure|array $condition = true)
     * @method self toggleable(bool|\Closure $condition = true, bool $isToggledHiddenByDefault = false)
     * @method self limit(int|\Closure $length = 100, string|\Closure|null $end = '...')
     * @method self wrap(bool|\Closure $condition = true)
     * @method self extraAttributes(array|\Closure $attributes, bool $merge = false)
     * @method self openUrlInNewTab(bool|\Closure $condition = true)
     * @method self grow(bool|\Closure $condition = true)
     */
    class Column {}

    /**
     * @method self money(string|\Closure $currency = null, bool $shouldConvert = true)
     * @method self dateTime(string|\Closure|null $format = null, string|null $timezone = null)
     * @method self date(string|\Closure|null $format = null, string|null $timezone = null)
     * @method self time(string|\Closure|null $format = null, string|null $timezone = null)
     */
    class TextColumn extends Column {}

    /**
     * @method self width(int|string|\Closure|null $width)
     * @method self height(int|string|\Closure|null $height)
     * @method self square()
     * @method self circular()
     * @method self extraImgAttributes(array|\Closure $attributes, bool $merge = false)
     */
    class ImageColumn extends Column {}

    /**
     * @method self trueIcon(string|\Closure|null $icon)
     * @method self falseIcon(string|\Closure|null $icon)
     * @method self trueColor(string|\Closure|null $color)
     * @method self falseColor(string|\Closure|null $color)
     */
    class IconColumn extends Column {}
}

namespace Filament\Forms {
    class Form
    {
        // Add form-specific helper methods if needed
    }
}

namespace Filament\Forms\Components {
    /**
     * @method static static make(string|null $name = null)
     * @method self label(string|\Closure|null $label)
     * @method self required(bool|\Closure $condition = true)
     * @method self maxLength(int|\Closure $length)
     * @method self columnSpan(array|string|int|\Closure $span)
     * @method self reactive()
     * @method self live(bool $onBlur = false, bool $debounce = false)
     * @method self afterStateUpdated(\Closure $callback)
     * @method self placeholder(string|\Closure|null $placeholder)
     * @method self default(mixed $state)
     * @method self options(array|\Closure|string $options)
     * @method self relationship(string $relationshipName, string $titleAttribute, \Closure $modifyQueryUsing = null)
     * @method self searchable(bool|\Closure $condition = true)
     * @method self preload(bool|\Closure $condition = true)
     * @method self visible(bool|\Closure|null $condition = true)
     * @method self hidden(bool|\Closure|null $condition = true)
     * @method self disabled(bool|\Closure $condition = true)
     * @method self readOnly(bool|\Closure $condition = true)
     * @method self hint(string|\Closure|null $hint)
     * @method self helperText(string|\Closure|null $text)
     * @method self extraAttributes(array|\Closure $attributes, bool $merge = false)
     */
    class Component {}

    /**
     * @method self minValue(int|float|string|\Closure $value)
     * @method self maxValue(int|float|string|\Closure $value)
     * @method self numeric(bool|\Closure $condition = true)
     * @method self prefix(string|\Closure|null $prefix)
     * @method self suffix(string|\Closure|null $suffix)
     * @method self type(string|\Closure|null $type)
     */
    class TextInput extends Component {}

    /**
     * @method static static make(string $name)
     * @method self schema(array|\Closure $components)
     * @method self description(string|\Closure|null $description = null)
     * @method self columns(array|int|string $columns = 2)
     * @method self collapsed(bool|\Closure $condition = true)
     * @method self collapsible(bool|\Closure $condition = true)
     */
    class Section extends Component {}

    /**
     * @method self multiple(bool|\Closure $condition = true)
     * @method self image(bool|\Closure $condition = true)
     * @method self directory(string|\Closure|null $directory)
     * @method self visibility(string|\Closure|null $visibility)
     * @method self acceptedFileTypes(array|\Closure $types)
     * @method self disk(string|\Closure|null $disk)
     */
    class FileUpload extends Component {}
}

namespace Filament\Resources {
    use Illuminate\Database\Eloquent\Model;

    /**
     * @property-read Model $record
     * @property-read \Filament\Resources\Resource $resource
     *
     * @mixin \Eloquent
     */
    class Resource {}
}

namespace Filament\Resources\Pages {
    use Illuminate\Database\Eloquent\Model;

    /**
     * @property-read Model $record
     * @property-read \Filament\Resources\Resource $resource
     *
     * @mixin \Eloquent
     */
    class EditRecord {}

    /**
     * @property-read Model $record
     * @property-read \Filament\Resources\Resource $resource
     *
     * @mixin \Eloquent
     */
    class ViewRecord {}

    /**
     * @property-read \Filament\Resources\Resource $resource
     *
     * @mixin \Eloquent
     */
    class ListRecords {}
}

namespace Livewire {
    use Illuminate\Http\Request;
    use Illuminate\Session\SessionManager;

    /**
     * @property SessionManager $session
     * @property Request $request
     *
     * @method void dispatch(string $event, ...$params)
     * @method void js(string $expression)
     *
     * @mixin \Eloquent
     */
    class Component {}
}

namespace Illuminate\Database\Eloquent {
    class Builder
    {
        /**
         * @return $this
         */
        public function role($roles, $guard = null, $without = false): self
        {
            return $this;
        }

        /**
         * @return $this
         */
        public function permission($permissions, $without = false): self
        {
            return $this;
        }
    }
}

namespace App\Models {
    use Illuminate\Database\Eloquent\Builder;

    /**
     * @mixin \Eloquent
     * @mixin Builder
     */
    class User extends \Eloquent
    {
        /**
         * @return Builder
         */
        public function allConversations()
        {
            return $this->query();
        }
    }
}
