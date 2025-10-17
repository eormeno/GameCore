<?php

/**
 * UI Builder - Tree Architecture Examples
 * 
 * This file demonstrates the new tree-based UI architecture.
 * Run with: php docs/examples/ui-builder-tree-example.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Services\UI\UIBuilder;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Enums\LayoutType;

echo "=== UI Builder Tree Architecture Examples ===\n\n";

// Example 1: Simple Button and Label
echo "Example 1: Simple Container with Button and Label\n";
echo "---------------------------------------------------\n";

$simple = UIBuilder::container('simple_ui')
    ->slot('canvas')
    ->title('Simple UI Example');

$simple->add(
    UIBuilder::button('submit')
        ->label('Submit Form')
        ->action('submit')
        ->icon('check')
        ->style('primary')
);

$simple->add(
    UIBuilder::label('info')
        ->text('Click the button to submit')
        ->style('info')
);

$json1 = $simple->build();
echo json_encode($json1, JSON_PRETTY_PRINT) . "\n\n";

// Example 2: Nested Containers
echo "Example 2: Nested Containers with Horizontal Layout\n";
echo "------------------------------------------------------\n";

$root = UIBuilder::container('dashboard')->getContainer();
$root->slot('canvas')->title('Dashboard');

// Header with logo and title
$header = new UIContainer('header');
$header->layout(LayoutType::HORIZONTAL);
$header->add(UIBuilder::label('logo')->text('🎮 GameCore')->style('primary'));
$header->add(UIBuilder::label('user')->text('Welcome, User!')->style('default'));
$root->add($header);

// Main content area
$content = new UIContainer('content');
$content->layout(LayoutType::VERTICAL);
$content->add(UIBuilder::button('new_game')->label('New Game')->icon('plus')->style('success'));
$content->add(UIBuilder::button('load_game')->label('Load Game')->icon('folder')->style('default'));
$content->add(UIBuilder::button('settings')->label('Settings')->icon('gear')->style('default'));
$root->add($content);

$json2 = $root->toJson();
echo json_encode($json2, JSON_PRETTY_PRINT) . "\n\n";

// Example 3: Dynamic Modification
echo "Example 3: Dynamic Tree Modification\n";
echo "--------------------------------------\n";

$dynamic = UIBuilder::container('dynamic')->getContainer();

// Add initial buttons
$dynamic->add(UIBuilder::button('btn1')->label('Button 1'));
$dynamic->add(UIBuilder::button('btn2')->label('Button 2'));
$dynamic->add(UIBuilder::button('btn3')->label('Button 3'));

echo "Initial state: " . $dynamic->count() . " elements\n";

// Remove middle button
$dynamic->remove('btn2:button');
echo "After removing btn2: " . $dynamic->count() . " elements\n";

// Update first button
$dynamic->update('btn1:button', UIBuilder::button('btn1')->label('Updated Button 1')->enabled(false));
echo "After updating btn1: label changed and disabled\n";

// Add new label
$dynamic->add(UIBuilder::label('new_label')->text('This is a new label'));
echo "After adding label: " . $dynamic->count() . " elements\n";

$json3 = $dynamic->toJson();
echo json_encode($json3, JSON_PRETTY_PRINT) . "\n\n";

// Example 4: Finding Elements Recursively
echo "Example 4: Recursive Element Search\n";
echo "-------------------------------------\n";

$searchRoot = new UIContainer('root');
$level1 = new UIContainer('level1');
$level2 = new UIContainer('level2');

$level2->add(UIBuilder::button('deep_button')->label('Deep Button'));
$level1->add($level2);
$searchRoot->add($level1);

$found = $searchRoot->find('deep_button:button');
if ($found) {
    echo "Found element: " . $found->getId() . "\n";
    echo "Element type: " . $found->getType() . "\n";
    echo "Is visible: " . ($found->isVisible() ? 'yes' : 'no') . "\n";
} else {
    echo "Element not found\n";
}

echo "\n";

// Example 5: Game Lobby Screen (Real-world Example)
echo "Example 5: Game Lobby Screen Structure\n";
echo "----------------------------------------\n";

$gameLobby = UIBuilder::container('game_lobby_screen')
    ->slot('canvas')
    ->title('Battle Arena - Game Lobby');

// New Game Button
$gameLobby->add(
    UIBuilder::button('new_game')
        ->label('Create New Game')
        ->action('create_new_game')
        ->icon('plus')
        ->style('primary')
);

// Warning Label
$gameLobby->add(
    UIBuilder::label('warning')
        ->text('Maximum 5 games per user')
        ->style('warning')
        ->visible(false)
);

// Saved Games Table
$table = UIBuilder::table('saved_games')
    ->title('Your Saved Games')
    ->addHeader('Game #')
    ->addHeader('Name')
    ->addHeader('Status')
    ->addHeader('Actions', 'actions', width: '200px');

$tableRows = [];
for ($i = 1; $i <= 3; $i++) {
    $actionsContainer = UIBuilder::container("game_{$i}_actions")
        ->layout(LayoutType::HORIZONTAL);
    
    $actionsContainer->add(
        UIBuilder::button("play_{$i}")
            ->label('Play')
            ->icon('play')
            ->style('success')
            ->action('play_game', ['game_id' => $i])
    );
    
    $actionsContainer->add(
        UIBuilder::button("delete_{$i}")
            ->label('Delete')
            ->icon('trash')
            ->style('danger')
            ->action('delete_game', ['game_id' => $i])
    );
    
    $tableRows[] = [
        $i,
        "Game $i",
        'Ready',
        $actionsContainer->build()
    ];
}

$table->rows($tableRows);
$gameLobby->add($table);

$json5 = $gameLobby->build();
echo json_encode($json5, JSON_PRETTY_PRINT) . "\n\n";

// Example 6: Conditional UI Building
echo "Example 6: Conditional UI Elements\n";
echo "------------------------------------\n";

function buildConditionalUI(bool $isAdmin, int $messageCount): array
{
    $container = UIBuilder::container('conditional')->getContainer();
    
    // Always show welcome message
    $container->add(
        UIBuilder::label('welcome')
            ->text('Welcome to the application')
    );
    
    // Admin-only button
    if ($isAdmin) {
        $container->add(
            UIBuilder::button('admin_panel')
                ->label('Admin Panel')
                ->icon('shield')
                ->style('danger')
        );
    }
    
    // Show messages button only if there are messages
    if ($messageCount > 0) {
        $container->add(
            UIBuilder::button('messages')
                ->label("Messages ($messageCount)")
                ->icon('envelope')
                ->style('primary')
        );
    }
    
    // Always show logout button
    $container->add(
        UIBuilder::button('logout')
            ->label('Logout')
            ->icon('sign-out')
            ->style('default')
    );
    
    return $container->toJson();
}

echo "Admin user with 5 messages:\n";
$ui1 = buildConditionalUI(true, 5);
echo "Elements: " . count($ui1['conditional:container']['elements']) . "\n";

echo "\nRegular user with 0 messages:\n";
$ui2 = buildConditionalUI(false, 0);
echo "Elements: " . count($ui2['conditional:container']['elements']) . "\n\n";

echo "=== All Examples Completed ===\n";
