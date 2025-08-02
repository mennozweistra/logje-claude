@props(['width' => 'w-full', 'height' => 'h-4', 'class' => ''])

<div {{ $attributes->merge(['class' => 'animate-pulse bg-gray-200 rounded ' . $width . ' ' . $height . ' ' . $class]) }}></div>
