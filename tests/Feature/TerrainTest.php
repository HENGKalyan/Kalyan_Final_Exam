
<?php

test('terrain model exists', function () {
    expect(class_exists(\App\Models\Terrain::class))->toBeTrue();
});

test('terrain has required attributes', function () {
    $terrain = new \App\Models\Terrain();
    expect($terrain->getFillable())->toContain('title', 'location', 'area_size', 'price_per_day');
});
