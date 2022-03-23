<?php

namespace Shelton\Firebase\Google\Firestore;

use Google\Cloud\Core\GeoPoint;
use Google\Cloud\Core\Timestamp;
use Google\Cloud\Firestore\DocumentReference;
use Google\Cloud\Firestore\DocumentSnapshot;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;

class Model implements Arrayable
{
    public string $id;
    public bool $exists;
    public ?Carbon $createdAt;
    public ?Carbon $updatedAt;

    protected static array $hidden = ['id', 'exists', 'createdAt', 'updatedAt'];

    public function __construct(DocumentSnapshot $snapshot)
    {
        $this->id = $snapshot->id();
        $this->exists = $snapshot->exists();
        $this->updatedAt = $this->cast($snapshot->updateTime());
        $this->createdAt = $this->cast($snapshot->createTime());
        $this->castData($snapshot->data());
    }

    public function toArray() : array
    {
        return array_filter(get_object_vars($this), function ($key) {
            return ! in_array($key, static::$hidden);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function cast($value)
    {
        if ($value instanceof Timestamp) {
            return Carbon::instance($value->get());
        } else if ($value instanceof DocumentReference) {
            return $value->id();
        } else if ($value instanceof GeoPoint) {
            return $value->point();
        }
        return $value;
    }

    protected function castData(array $data)
    {
        foreach ($data as $key => $value) {
            if (! in_array($key, static::$hidden) && property_exists($this, $key)) {
                $this->{$key} = $this->cast($value);
            }
        }
    }
}
