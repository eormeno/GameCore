<?php

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\Enums\TextAlign;
use App\Services\UI\Enums\FontWeight;
use App\Services\UI\Support\UIIdGenerator;

beforeEach(function () {
    UIIdGenerator::reset();
});

describe('UI Builder Component Creation', function () {
    
    test('can create a button with full configuration', function () {
        $button = UIBuilder::button('test_button')
            ->label('Haz clic aquí')
            ->action('test_action', ['param' => 'value'])
            ->icon('click')
            ->style('primary')
            ->enabled(true)
            ->tooltip('Este es un botón de prueba');
        
        $id = $button->getId();
        $json = $button->toJson();
        
        expect($id)->toBeInt()
            ->and($json)->toHaveKey($id)
            ->and($json[$id]['type'])->toBe('button')
            ->and($json[$id]['name'])->toBe('test_button');
    });

    test('components have auto-incremental numeric IDs', function () {
        $button1 = UIBuilder::button('btn1');
        $button2 = UIBuilder::button('btn2');
        $label1 = UIBuilder::label('lbl1');
        
        $id1 = $button1->getId();
        $id2 = $button2->getId();
        $id3 = $label1->getId();
        
        expect($id1)->toBeInt()
            ->and($id2)->toBeInt()
            ->and($id3)->toBeInt()
            ->and($id2)->toBe($id1 + 1)
            ->and($id3)->toBe($id2 + 1);
    });
});
