<?php

namespace App\Filament\Resources\StationResource\Pages;

trait DisplaysStationHeading
{
    protected function getStationHeadingParts(): array
    {
        if (! property_exists($this, 'record') || ! $this->record) {
            return [];
        }

        $code = (string) ($this->record->code ?? '');
        $name = (string) ($this->record->name ?? '');
        $region = (string) ($this->record->region ?? '');

        $parts = [];

        if ($code !== '') {
            $parts[] = $code;
        }

        if ($name !== '') {
            $parts[] = $name;
        }

        if ($region !== '') {
            $parts[] = $region;
        }

        return $parts;
    }

    public function getHeading(): string
    {
        $parts = $this->getStationHeadingParts();

        if ($parts !== []) {
            return implode(' — ', $parts);
        }

        return parent::getHeading();
    }

    public function getSubheading(): ?string
    {
        $defaultHeading = parent::getHeading();

        if ($defaultHeading !== '') {
            return $defaultHeading;
        }

        $parentClass = get_parent_class($this);

        if ($parentClass && method_exists($parentClass, 'getSubheading')) {
            return parent::getSubheading();
        }

        return null;
    }
}
