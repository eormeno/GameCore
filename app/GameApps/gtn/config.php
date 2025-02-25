<?php

return [
	'image' => 'guess-the-number.jpeg',
    'card_image' => 'guess-the-number-card.320.200.jpeg',
	'name' => 'Adivina el número',
	'description' => 'Un simple juego donde adivinas un número entre 1 y 1024.',
	'prefab_name' => 'gtn.root-prefab',		// The name of the prefab that define the root GameObject.
	'prefab_attributes' => ['value' => 1],  // The init attributes for the prefab. Like a constructor parameters.
	'client' => 'blade',
    'active' => false
];
