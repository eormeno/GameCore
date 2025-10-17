<?php

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\Enums\TextAlign;
use App\Services\UI\Enums\FontWeight;

describe('UI Builder Component Creation', function () {
    
    test('can create a button with full configuration', function () {
        $button = UIBuilder::button('test_button')
            ->label('Haz clic aquí')
            ->action('test_action', ['param' => 'value'])
            ->icon('click')
            ->style('primary')
            ->enabled(true)
            ->tooltip('Este es un botón de prueba')
            ->toJson();
        
        expect($button)->toHaveKey('test_button')
            ->and($button['test_button']['type'])->toBe('button')
            ->and($button['test_button'])->toMatchArray([
                'visible' => true,
                'enabled' => true,
                'style' => 'primary',
                'label' => 'Haz clic aquí',
                'action' => 'test_action',
                'icon' => 'click',
                'tooltip' => 'Este es un botón de prueba',
            ])
            ->and($button['test_button']['parameters'])->toBe(['param' => 'value']);
    });

    test('can create a label with style', function () {
        $label = UIBuilder::label('test_label')
            ->text('Este es un mensaje de prueba')
            ->style('warning')
            ->toJson();
        
        expect($label)->toHaveKey('test_label')
            ->and($label['test_label']['type'])->toBe('label')
            ->and($label['test_label'])->toMatchArray([
                'visible' => true,
                'text' => 'Este es un mensaje de prueba',
                'style' => 'warning',
            ]);
    });

    test('can create a table with integrated headers', function () {
        $table = UIBuilder::table('test_table')
            ->title('Mi tabla de prueba')
            ->addHeader('Nombre', 'col1', sortable: true, align: TextAlign::LEFT, width: '150px', color: '#333', tooltip: 'Haz clic para ordenar por nombre', sortDirection: 'asc')
            ->addHeader('Estado', 'col2', width: '200px', backgroundColor: '#f5f5f5')
            ->addHeader('Acciones', width: '120px', tooltip: 'Acciones disponibles para cada fila')
            ->rows([
                ['Dato 1', 'Dato 2', 'Editar'],
                ['Dato 3', 'Dato 4', 'Eliminar']
            ])
            ->toJson();
        
        expect($table)->toHaveKey('test_table')
            ->and($table['test_table']['type'])->toBe('table')
            ->and($table['test_table']['title'])->toBe('Mi tabla de prueba')
            ->and($table['test_table']['headers'])->toHaveKey('col1:tableheader')
            ->and($table['test_table']['headers'])->toHaveKey('col2:tableheader')
            ->and($table['test_table']['headers'])->toHaveKey('acciones_header:tableheader');
        
        // Verify first header configuration
        $header1 = $table['test_table']['headers']['col1:tableheader'];
        expect($header1['text'])->toBe('Nombre')
            ->and($header1['sortable'])->toBeTrue()
            ->and($header1['sort_direction'])->toBe('asc')
            ->and($header1['align'])->toBe('left')
            ->and($header1['width'])->toBe('150px')
            ->and($header1['color'])->toBe('#333')
            ->and($header1['tooltip'])->toBe('Haz clic para ordenar por nombre');
        
        // Verify second header configuration
        $header2 = $table['test_table']['headers']['col2:tableheader'];
        expect($header2['text'])->toBe('Estado')
            ->and($header2['sortable'])->toBeFalse()
            ->and($header2['width'])->toBe('200px')
            ->and($header2['background_color'])->toBe('#f5f5f5');
        
        // Verify third header with auto-generated ID
        $header3 = $table['test_table']['headers']['acciones_header:tableheader'];
        expect($header3['text'])->toBe('Acciones')
            ->and($header3['width'])->toBe('120px')
            ->and($header3['tooltip'])->toBe('Acciones disponibles para cada fila');
        
        // Verify rows
        expect($table['test_table']['rows'])->toHaveCount(2)
            ->and($table['test_table']['rows'][0])->toBe(['Dato 1', 'Dato 2', 'Editar'])
            ->and($table['test_table']['rows'][1])->toBe(['Dato 3', 'Dato 4', 'Eliminar']);
    });

    test('table header auto-generates ID from text', function () {
        $table = UIBuilder::table('test')
            ->addHeader('Nombre del Usuario')
            ->addHeader('Estado Actual')
            ->toJson();
        
        $headers = $table['test']['headers'];
        
        expect($headers)->toHaveKey('nombre_del_usuario_header:tableheader')
            ->and($headers)->toHaveKey('estado_actual_header:tableheader');
    });

    test('table headers have default values', function () {
        $table = UIBuilder::table('test')
            ->addHeader('Columna Simple')
            ->toJson();
        
        $header = $table['test']['headers']['columna_simple_header:tableheader'];
        
        expect($header['sortable'])->toBeFalse()
            ->and($header['align'])->toBe('center')
            ->and($header['font_weight'])->toBe('bold')
            ->and($header['sort_direction'])->toBeNull()
            ->and($header['visible'])->toBeTrue();
    });
});

describe('UI Builder Container with Tree API', function () {
    
    test('can create a container and add elements using tree API', function () {
        $container = UIBuilder::container('test_container')
            ->slot('main')
            ->layout(LayoutType::VERTICAL)
            ->title('Mi contenedor');
        
        $container->add(
            UIBuilder::button('btn1')
                ->label('Button 1')
                ->action('action1')
        );
        
        $container->add(
            UIBuilder::label('lbl1')
                ->text('Label 1')
        );
        
        $json = $container->build();
        
        expect($json)->toHaveKey('test_container')
            ->and($json['test_container']['type'])->toBe('container')
            ->and($json['test_container']['slot'])->toBe('main')
            ->and($json['test_container']['layout'])->toBe('vertical')
            ->and($json['test_container']['title'])->toBe('Mi contenedor')
            ->and($json['test_container']['elements'])->toHaveKey('btn1')
            ->and($json['test_container']['elements'])->toHaveKey('lbl1');
    });

    test('can create nested containers with tree API', function () {
        $root = UIBuilder::container('root')
            ->layout(LayoutType::VERTICAL);
        
        $child = UIBuilder::container('child')
            ->layout(LayoutType::HORIZONTAL);
        
        $child->add(
            UIBuilder::button('btn1')->label('Button in child')
        );
        
        $root->add($child->getContainer());
        
        $json = $root->build();
        
        expect($json)->toHaveKey('root')
            ->and($json['root']['type'])->toBe('container')
            ->and($json['root']['elements'])->toHaveKey('child')
            ->and($json['root']['elements']['child']['type'])->toBe('container')
            ->and($json['root']['elements']['child']['elements'])->toHaveKey('btn1')
            ->and($json['root']['elements']['child']['elements']['btn1']['label'])->toBe('Button in child');
    });

    test('can create complex nested structure like GameLobbyScreen', function () {
        $screen = UIBuilder::container('game_lobby_screen')
            ->slot('canvas')
            ->layout(LayoutType::VERTICAL)
            ->title('Game Lobby');
        
        // Add button
        $screen->add(
            UIBuilder::button('new_game')
                ->label('New Game')
                ->action('create_game')
                ->icon('plus')
                ->style('primary')
                ->enabled(true)
        );
        
        // Add warning label
        $screen->add(
            UIBuilder::label('warning_message')
                ->text('You have reached the maximum number of games')
                ->style('warning')
                ->visible(false)
        );
        
        // Add table
        $table = UIBuilder::table('saved_games')
            ->title('Saved Games')
            ->addHeader('Number', width: '80px')
            ->addHeader('Name', sortable: true, align: TextAlign::LEFT)
            ->addHeader('Actions', width: '200px')
            ->rows([
                [1, 'Game 1', 'Actions'],
                [2, 'Game 2', 'Actions'],
            ]);
        
        $screen->add($table);
        
        $json = $screen->build();
        
        // Verify structure
        expect($json)->toHaveKey('game_lobby_screen')
            ->and($json['game_lobby_screen']['slot'])->toBe('canvas')
            ->and($json['game_lobby_screen']['layout'])->toBe('vertical')
            ->and($json['game_lobby_screen']['title'])->toBe('Game Lobby')
            ->and($json['game_lobby_screen']['elements'])->toHaveKey('new_game')
            ->and($json['game_lobby_screen']['elements'])->toHaveKey('warning_message')
            ->and($json['game_lobby_screen']['elements'])->toHaveKey('saved_games');
        
        // Verify button
        $button = $json['game_lobby_screen']['elements']['new_game'];
        expect($button['label'])->toBe('New Game')
            ->and($button['action'])->toBe('create_game')
            ->and($button['enabled'])->toBeTrue();
        
        // Verify label
        $label = $json['game_lobby_screen']['elements']['warning_message'];
        expect($label['text'])->toBe('You have reached the maximum number of games')
            ->and($label['visible'])->toBeFalse();
        
        // Verify table
        $table = $json['game_lobby_screen']['elements']['saved_games'];
        expect($table['title'])->toBe('Saved Games')
            ->and($table['headers'])->toHaveCount(3)
            ->and($table['rows'])->toHaveCount(2);
    });

    test('can add table with action containers in cells', function () {
        $table = UIBuilder::table('games')
            ->addHeader('ID')
            ->addHeader('Name')
            ->addHeader('Actions');
        
        // Build row with action container
        $actionsContainer = UIBuilder::container('game_1_actions')
            ->layout(LayoutType::HORIZONTAL);
        
        $actionsContainer->add(
            UIBuilder::button('play_1')->label('Play')->style('success')
        );
        $actionsContainer->add(
            UIBuilder::button('delete_1')->label('Delete')->style('danger')
        );
        
        $rows = [
            [1, 'Game One', $actionsContainer->build()],
        ];
        
        $table->rows($rows);
        
        $json = $table->toJson();
        
        $row = $json['games']['rows'][0];
        
        expect($row[0])->toBe(1)
            ->and($row[1])->toBe('Game One')
            ->and($row[2])->toHaveKey('game_1_actions')
            ->and($row[2]['game_1_actions']['type'])->toBe('container')
            ->and($row[2]['game_1_actions']['elements'])->toHaveKey('play_1')
            ->and($row[2]['game_1_actions']['elements'])->toHaveKey('delete_1');
    });
});

describe('UI Builder Type Safety with Enums', function () {
    
    test('layout enum generates correct string value', function () {
        $container = UIBuilder::container('test')
            ->layout(LayoutType::HORIZONTAL)
            ->build();
        
        expect($container['test']['layout'])->toBe('horizontal');
    });

    test('text align enum generates correct string value', function () {
        $table = UIBuilder::table('test')
            ->addHeader('Left', 'left', align: TextAlign::LEFT)
            ->addHeader('Center', 'center', align: TextAlign::CENTER)
            ->addHeader('Right', 'right', align: TextAlign::RIGHT)
            ->toJson();
        
        $headers = $table['test']['headers'];
        
        expect($headers['left:tableheader']['align'])->toBe('left')
            ->and($headers['center:tableheader']['align'])->toBe('center')
            ->and($headers['right:tableheader']['align'])->toBe('right');
    });

    test('font weight enum generates correct string value', function () {
        $table = UIBuilder::table('test')
            ->addHeader('Normal', 'normal', fontWeight: FontWeight::NORMAL)
            ->addHeader('Bold', 'bold', fontWeight: FontWeight::BOLD)
            ->addHeader('Light', 'light', fontWeight: FontWeight::LIGHT)
            ->toJson();
        
        $headers = $table['test']['headers'];
        
        expect($headers['normal:tableheader']['font_weight'])->toBe('normal')
            ->and($headers['bold:tableheader']['font_weight'])->toBe('bold')
            ->and($headers['light:tableheader']['font_weight'])->toBe('light');
    });
});

describe('UI Builder Component Structure', function () {
    
    test('button includes type field', function () {
        $button = UIBuilder::button('my_button')->toJson();
        
        expect($button)->toHaveKey('my_button')
            ->and($button['my_button']['type'])->toBe('button');
    });

    test('label includes type field', function () {
        $label = UIBuilder::label('my_label')->toJson();
        
        expect($label)->toHaveKey('my_label')
            ->and($label['my_label']['type'])->toBe('label');
    });

    test('container includes type field', function () {
        $container = UIBuilder::container('my_container')->build();
        
        expect($container)->toHaveKey('my_container')
            ->and($container['my_container']['type'])->toBe('container');
    });

    test('table includes type field', function () {
        $table = UIBuilder::table('my_table')->toJson();
        
        expect($table)->toHaveKey('my_table')
            ->and($table['my_table']['type'])->toBe('table');
    });

    test('all components have required fields', function () {
        $button = UIBuilder::button('btn')->label('Test')->toJson();
        $label = UIBuilder::label('lbl')->text('Test')->toJson();
        $container = UIBuilder::container('cnt')->build();
        $table = UIBuilder::table('tbl')->toJson();
        
        expect($button['btn'])->toHaveKeys(['type', 'visible', 'label'])
            ->and($label['lbl'])->toHaveKeys(['type', 'visible', 'text'])
            ->and($container['cnt'])->toHaveKeys(['type', 'visible', 'layout', 'elements'])
            ->and($table['tbl'])->toHaveKeys(['type', 'visible', 'title', 'headers', 'rows']);
    });
});
