<?php

use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\ButtonBuilder;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\UIBuilder;

describe('UIContainer Tree Structure', function () {
    
    test('can create a container with basic configuration', function () {
        $container = new UIContainer('test_container');
        
        expect($container->getId())->toBe('test_container')
            ->and($container->getType())->toBe('container')
            ->and($container->isVisible())->toBeTrue()
            ->and($container->count())->toBe(0);
    });

    test('can set container properties', function () {
        $container = new UIContainer('test');
        $container
            ->slot('canvas')
            ->layout(LayoutType::HORIZONTAL)
            ->title('Test Title')
            ->visible(false);
        
        $json = $container->toJson();
        $config = $json['test'];
        
        expect($config['type'])->toBe('container')
            ->and($config['slot'])->toBe('canvas')
            ->and($config['layout'])->toBe('horizontal')
            ->and($config['title'])->toBe('Test Title')
            ->and($config['visible'])->toBeFalse();
    });

    test('can add child elements to container', function () {
        $container = new UIContainer('parent');
        $button = new ButtonBuilder('btn1');
        $label = new LabelBuilder('lbl1');
        
        $container->add($button);
        $container->add($label);
        
        expect($container->count())->toBe(2)
            ->and($container->has('btn1'))->toBeTrue()
            ->and($container->has('lbl1'))->toBeTrue();
    });

    test('can add multiple elements at once', function () {
        $container = new UIContainer('parent');
        $button1 = new ButtonBuilder('btn1');
        $button2 = new ButtonBuilder('btn2');
        
        $container->addMany([$button1, $button2]);
        
        expect($container->count())->toBe(2);
    });

    test('throws exception when adding element with duplicate ID', function () {
        $container = new UIContainer('parent');
        $button1 = new ButtonBuilder('btn1');
        $button2 = new ButtonBuilder('btn1');
        
        $container->add($button1);
        
        expect(fn() => $container->add($button2))
            ->toThrow(InvalidArgumentException::class);
    });

    test('can remove child element by ID', function () {
        $container = new UIContainer('parent');
        $button = new ButtonBuilder('btn1');
        
        $container->add($button);
        expect($container->count())->toBe(1);
        
        $container->remove('btn1');
        expect($container->count())->toBe(0)
            ->and($container->has('btn1'))->toBeFalse();
    });

    test('throws exception when removing non-existent element', function () {
        $container = new UIContainer('parent');
        
        expect(fn() => $container->remove('nonexistent'))
            ->toThrow(InvalidArgumentException::class);
    });

    test('tryRemove returns true when element exists', function () {
        $container = new UIContainer('parent');
        $button = new ButtonBuilder('btn1');
        
        $container->add($button);
        $result = $container->tryRemove('btn1');
        
        expect($result)->toBeTrue()
            ->and($container->count())->toBe(0);
    });

    test('tryRemove returns false when element does not exist', function () {
        $container = new UIContainer('parent');
        $result = $container->tryRemove('nonexistent');
        
        expect($result)->toBeFalse();
    });

    test('can update child element', function () {
        $container = new UIContainer('parent');
        $button1 = (new ButtonBuilder('btn1'))->label('Original');
        $button2 = (new ButtonBuilder('btn1'))->label('Updated');
        
        $container->add($button1);
        $container->update('btn1', $button2);
        
        $found = $container->find('btn1');
        $json = $found->toJson();
        
        expect($json['btn1']['label'])->toBe('Updated');
    });

    test('throws exception when updating non-existent element', function () {
        $container = new UIContainer('parent');
        $button = new ButtonBuilder('btn1');
        
        expect(fn() => $container->update('nonexistent', $button))
            ->toThrow(InvalidArgumentException::class);
    });

    test('throws exception when update element ID does not match', function () {
        $container = new UIContainer('parent');
        $button1 = new ButtonBuilder('btn1');
        $button2 = new ButtonBuilder('btn2');
        
        $container->add($button1);
        
        expect(fn() => $container->update('btn1', $button2))
            ->toThrow(InvalidArgumentException::class);
    });

    test('can find direct child element', function () {
        $container = new UIContainer('parent');
        $button = new ButtonBuilder('btn1');
        
        $container->add($button);
        $found = $container->find('btn1');
        
        expect($found)->not->toBeNull()
            ->and($found->getId())->toBe('btn1');
    });

    test('can find nested child element recursively', function () {
        $root = new UIContainer('root');
        $child = new UIContainer('child');
        $button = new ButtonBuilder('btn1');
        
        $child->add($button);
        $root->add($child);
        
        $found = $root->find('btn1');
        
        expect($found)->not->toBeNull()
            ->and($found->getId())->toBe('btn1');
    });

    test('returns null when element not found', function () {
        $container = new UIContainer('parent');
        $found = $container->find('nonexistent');
        
        expect($found)->toBeNull();
    });

    test('can get all children', function () {
        $container = new UIContainer('parent');
        $button = new ButtonBuilder('btn1');
        $label = new LabelBuilder('lbl1');
        
        $container->add($button);
        $container->add($label);
        
        $children = $container->getChildren();
        
        expect($children)->toHaveCount(2)
            ->and($children[0])->toBeInstanceOf(ButtonBuilder::class)
            ->and($children[1])->toBeInstanceOf(LabelBuilder::class);
    });

    test('can clear all children', function () {
        $container = new UIContainer('parent');
        $button = new ButtonBuilder('btn1');
        $label = new LabelBuilder('lbl1');
        
        $container->add($button);
        $container->add($label);
        expect($container->count())->toBe(2);
        
        $container->clear();
        expect($container->count())->toBe(0);
    });

    test('toJson serializes empty container correctly', function () {
        $container = new UIContainer('test');
        $json = $container->toJson();
        
        expect($json)->toHaveKey('test')
            ->and($json['test']['elements'])->toBe([]);
    });

    test('toJson serializes container with children recursively', function () {
        $container = new UIContainer('parent');
        $button = (new ButtonBuilder('btn1'))
            ->label('Click Me')
            ->action('test_action');
        
        $container->add($button);
        $json = $container->toJson();
        
        expect($json)->toHaveKey('parent')
            ->and($json['parent']['elements'])->toHaveKey('btn1')
            ->and($json['parent']['elements']['btn1']['label'])->toBe('Click Me')
            ->and($json['parent']['elements']['btn1']['action'])->toBe('test_action');
    });

    test('toJson serializes nested containers recursively', function () {
        $root = new UIContainer('root');
        $child = new UIContainer('child');
        $button = (new ButtonBuilder('btn1'))->label('Test');
        
        $child->add($button);
        $root->add($child);
        
        $json = $root->toJson();
        
        expect($json)->toHaveKey('root')
            ->and($json['root']['elements'])->toHaveKey('child')
            ->and($json['root']['elements']['child']['elements'])->toHaveKey('btn1')
            ->and($json['root']['elements']['child']['elements']['btn1']['label'])->toBe('Test');
    });

    test('build method returns same as toJson', function () {
        $container = new UIContainer('test');
        $button = new ButtonBuilder('btn1');
        $container->add($button);
        
        expect($container->build())->toBe($container->toJson());
    });
});

describe('UIContainer with ContainerBuilder', function () {
    
    test('ContainerBuilder creates UIContainer instance', function () {
        $builder = UIBuilder::container('test');
        $container = $builder->getContainer();
        
        expect($container)->toBeInstanceOf(UIContainer::class)
            ->and($container->getId())->toBe('test');
    });

    test('ContainerBuilder can add elements', function () {
        $builder = UIBuilder::container('test');
        $button = UIBuilder::button('btn1');
        
        $builder->add($button);
        $container = $builder->getContainer();
        
        expect($container->count())->toBe(1);
    });

    test('ContainerBuilder build returns JSON', function () {
        $builder = UIBuilder::container('test')
            ->add(UIBuilder::button('btn1')->label('Test'));
        
        $json = $builder->build();
        
        expect($json)->toHaveKey('test')
            ->and($json['test']['elements'])->toHaveKey('btn1');
    });
});

describe('UI Component Tree Integration', function () {
    
    test('can build complex nested UI structure', function () {
        $screen = UIBuilder::container('game_lobby')
            ->slot('canvas')
            ->layout(LayoutType::VERTICAL)
            ->title('Game Lobby');
        
        $screen->add(
            UIBuilder::button('new_game')
                ->label('New Game')
                ->action('create_game')
                ->style('primary')
        );
        
        $screen->add(
            UIBuilder::label('info')
                ->text('Select a saved game or create a new one')
                ->style('info')
        );
        
        $actionsContainer = UIBuilder::container('actions')
            ->layout(LayoutType::HORIZONTAL);
        
        $actionsContainer->add(
            UIBuilder::button('play')
                ->label('Play')
                ->icon('play')
                ->style('success')
        );
        
        $actionsContainer->add(
            UIBuilder::button('delete')
                ->label('Delete')
                ->icon('trash')
                ->style('danger')
        );
        
        $screen->add($actionsContainer->getContainer());
        
        $json = $screen->build();
        
        // Verify structure
        expect($json)->toHaveKey('game_lobby')
            ->and($json['game_lobby']['slot'])->toBe('canvas')
            ->and($json['game_lobby']['title'])->toBe('Game Lobby')
            ->and($json['game_lobby']['elements'])->toHaveKey('new_game')
            ->and($json['game_lobby']['elements'])->toHaveKey('info')
            ->and($json['game_lobby']['elements'])->toHaveKey('actions')
            ->and($json['game_lobby']['elements']['actions']['elements'])->toHaveKey('play')
            ->and($json['game_lobby']['elements']['actions']['elements'])->toHaveKey('delete');
    });

    test('can dynamically modify tree structure', function () {
        $container = UIBuilder::container('test')->getContainer();
        
        // Add initial elements
        $container->add(UIBuilder::button('btn1')->label('Button 1'));
        $container->add(UIBuilder::button('btn2')->label('Button 2'));
        $container->add(UIBuilder::button('btn3')->label('Button 3'));
        
        expect($container->count())->toBe(3);
        
        // Remove middle element
        $container->remove('btn2');
        expect($container->count())->toBe(2);
        
        // Update first element
        $container->update('btn1', UIBuilder::button('btn1')->label('Updated Button 1'));
        
        // Add new element
        $container->add(UIBuilder::label('lbl1')->text('New Label'));
        
        expect($container->count())->toBe(3);
        
        $json = $container->toJson();
        expect($json['test']['elements']['btn1']['label'])->toBe('Updated Button 1')
            ->and($json['test']['elements'])->not->toHaveKey('btn2')
            ->and($json['test']['elements'])->toHaveKey('lbl1');
    });

    test('can search and modify nested elements', function () {
        $root = UIBuilder::container('root')->getContainer();
        $child1 = new UIContainer('child1');
        $child2 = new UIContainer('child2');
        
        $root->add($child1);
        $root->add($child2);
        
        $child1->add(UIBuilder::button('btn1')->label('Button in Child 1'));
        $child2->add(UIBuilder::button('btn2')->label('Button in Child 2'));
        
        // Find and verify nested button
        $found = $root->find('btn2');
        expect($found)->not->toBeNull();
        
        // Modify child2 by adding another button
        $child2->add(UIBuilder::button('btn3')->label('Another Button'));
        
        expect($child2->count())->toBe(2);
    });
});
