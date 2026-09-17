@props(['name'])
<x-dynamic-component :component="'heroicon-o-'.$name" {{ $attributes->class(['icon']) }} aria-hidden="true" />
