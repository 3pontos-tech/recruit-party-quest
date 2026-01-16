<?php

declare(strict_types=1);

namespace He4rt\Location;

use App\Models\BaseModel;
use He4rt\Location\Database\Factories\AddressFactory;
use He4rt\Teams\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $addressable_type
 * @property string $addressable_id
 * @property string $full_address
 * @property string $street_number
 * @property string $route
 * @property string $sub_locality
 * @property string $city
 * @property string $state
 * @property string $country
 * @property string $postal_code
 * @property float $latitude
 * @property float $longitude
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read array{lat: float, lng: float} $location
 * @property-read Model $addressable
 *
 * @extends BaseModel<AddressFactory>
 */
#[UseFactory(AddressFactory::class)]
#[UsePolicy(AddressPolicy::class)]
class Address extends BaseModel
{
    use BelongsToTeam;

    protected $table = 'addresses';

    protected $appends = [
        'location',
    ];

    /**
     * Get the lat and lng attribute/field names used on this table
     *
     * Used by the Filament Google Maps package.
     *
     * @return string[]
     */
    public static function getLatLngAttributes(): array
    {
        return [
            'lat' => 'latitude',
            'lng' => 'longitude',
        ];
    }

    /**
     * Get the name of the computed location attribute
     *
     * Used by the Filament Google Maps package.
     */
    public static function getComputedLocation(): string
    {
        return 'location';
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return Attribute<string, string>
     */
    protected function label(): Attribute
    {
        return Attribute::make(get: fn ($value) => sprintf('%s, Estado de %s', $this->city, $this->state));
    }

    /**
     * @return Attribute<array{lat: float, lng: float}, array{lat: float, lng: float}>
     */
    protected function location(): Attribute
    {
        return Attribute::make(
            get: fn (array $value): array => [
                'lat' => $this->latitude,
                'lng' => $this->longitude,
            ],
            set: function (?array $value, array $attributes): void {
                if (is_array($value)) {
                    $this->attributes['latitude'] = $attributes['lat'];
                    $this->attributes['longitude'] = $attributes['lng'];
                    unset($this->attributes['location']);
                }
            }
        );
    }
}
