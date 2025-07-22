<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models\Components{
/**
 * @property int $id
 * @property int $game_object_id
 * @property string|null $state
 * @property string $type
 * @property bool $enabled
 * @property array<array-key, mixed>|null $messages
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\GameObject\GameObject $gameObject
 * @property-read Component|null $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Component newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Component newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Component query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Component whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Component whereGameObjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Component whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Component whereMessages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Component whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Component whereType($value)
 */
	class Component extends \Eloquent {}
}

namespace App\Models\Components{
/**
 * @property int $id
 * @property string $type
 * @property int $game_object_id
 * @property bool $enabled
 * @property string|null $state
 * @property array|null $messages
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component|null $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentBase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentBase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentBase query()
 */
	class ComponentBase extends \Eloquent implements \App\Contracts\IGameEventListener {}
}

namespace App\Models\Components{
/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component|null $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentFinders newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentFinders newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentFinders query()
 */
	class ComponentFinders extends \Eloquent {}
}

namespace App\Models\Components{
/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component|null $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentMessages newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentMessages newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentMessages query()
 */
	class ComponentMessages extends \Eloquent {}
}

namespace App\Models\Events{
/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $game_app_id
 * @property-read \App\Models\GameApp $gameApp
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $gameEventListenerManagers
 * @property-read int|null $game_event_listener_managers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameAppEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameAppEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameAppEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameAppEvent whereGameAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameAppEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameAppEvent whereName($value)
 */
	class GameAppEvent extends \Eloquent {}
}

namespace App\Models\Events{
/**
 * @property int $id
 * @property int $game_app_event_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Components\Component> $components
 * @property-read int|null $components_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameObject\GameObject> $gameObjects
 * @property-read int|null $game_objects_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameService> $gameServices
 * @property-read int|null $game_services_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameEventListenerManager newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameEventListenerManager newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameEventListenerManager query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameEventListenerManager whereGameAppEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameEventListenerManager whereId($value)
 */
	class GameEventListenerManager extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Game
 * Represents a game instance, which can be a playthrough of a game application.
 * 
 * It contains information about the game, its objects, players, and services.
 *
 * @property int $id
 * @property string $invitation_code
 * @property int $game_app_id
 * @property int|null $game_object_id
 * @property int|null $elapsed
 * @property string|null $name
 * @property \App\Enums\GameState|null $state
 * @property bool $auto_authorize_players
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $title
 * @package App\Models
 * @property-read \App\Models\GameApp $gameApp
 * @property-read \App\Models\GameObject\GameObject|null $gameObject
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameObject\GameObject> $gameObjects
 * @property-read int|null $game_objects_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $players
 * @property-read int|null $players_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameService> $services
 * @property-read int|null $services_count
 * @method static \Database\Factories\GameFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereAutoAuthorizePlayers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereElapsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereGameAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereGameObjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereInvitationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereUpdatedAt($value)
 */
	class Game extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $prefix
 * @property string $name
 * @property string $description
 * @property int $min_age
 * @property string|null $image
 * @property string|null $prefab_name
 * @property array|null $prefab_attributes
 * @property string $client
 * @property int $width
 * @property int $height
 * @property string|null $version
 * @property int $max_instances_per_user
 * @property int $min_players_per_instance
 * @property int $max_players_per_instance
 * @property bool $allow_late_join
 * @property bool $active
 * @property array|null $service_registry
 * @property string $card_image
 * @property int $min_users_per_instance
 * @property int $max_users_per_instance
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameAppEvent> $gameAppEvents
 * @property-read int|null $game_app_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Game> $games
 * @property-read int|null $games_count
 * @property-read \App\Models\Prefab\Prefab|null $prefab
 * @method static \Database\Factories\GameAppFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp whereAllowLateJoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp whereCardImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp whereClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp whereMaxInstancesPerUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp whereMaxUsersPerInstance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp whereMinAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp whereMinUsersPerInstance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp wherePrefabAttributes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp wherePrefabName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp wherePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp whereServiceRegistry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameApp whereWidth($value)
 */
	class GameApp extends \Eloquent {}
}

namespace App\Models\GameObject{
/**
 * @property int $id
 * @property int $version
 * @property string $name
 * @property bool $active
 * @property bool $active_parents
 * @property int $game_id
 * @property int|null $game_object_id
 * @property string|null $state
 * @property array<array-key, mixed>|null $state_components
 * @property array<array-key, mixed>|null $indexed_children
 * @property-read \Illuminate\Database\Eloquent\Collection<int, GameObject> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Components\Component> $components
 * @property-read int|null $components_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Game $game
 * @property-read GameObject|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameObject activesOfGame(\App\Models\Game $game)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameObject newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameObject newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameObject query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameObject whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameObject whereActiveParents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameObject whereGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameObject whereGameObjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameObject whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameObject whereIndexedChildren($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameObject whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameObject whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameObject whereStateComponents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameObject whereVersion($value)
 */
	class GameObject extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $slug
 * @property string $type
 * @property int $game_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Game $game
 * @property-read GameService|null $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameService query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameService whereGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameService whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameService whereType($value)
 */
	class GameService extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $game_id
 * @property int $user_id
 * @property \App\Enums\GameUserRole $role
 * @property \App\Enums\GameUserStatus $status
 * @property \App\Enums\GameUserJoinMethod|null $join_method
 * @property int|null $actioned_by
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon|null $joined_at
 * @property \Illuminate\Support\Carbon|null $left_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $actionedBy
 * @property-read \App\Models\Game $game
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser administrative()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser administrators()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser byRole(\App\Enums\GameUserRole|string $role)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser owners()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser testers()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser whereActionedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser whereGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser whereJoinMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser whereJoinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser whereLeftAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameUser whereUserId($value)
 */
	class GameUser extends \Eloquent {}
}

namespace App\Models\Prefab{
/**
 * @property string $name
 * @property string|null $type
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prefab newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prefab newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prefab query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prefab whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prefab whereType($value)
 */
	class Prefab extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Game> $games
 * @property-read int|null $games_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App\GameApps\gtn\Services{
/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Game|null $game
 * @property-read \App\Models\GameService|null $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClueService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClueService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClueService query()
 */
	class ClueService extends \Eloquent {}
}

namespace App\GameApps\gtn\Services{
/**
 * @property int $id
 * @property int|null $cheat_number
 * @property int|null $min_number
 * @property int|null $max_number
 * @property int|null $max_attempts
 * @property int|null $last_number
 * @property int|null $remaining_attempts
 * @property int|null $random_number
 * @property int|null $score
 * @property int|null $times_played
 * @property bool|null $finished
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Game|null $game
 * @property-read int $remaining_free_attempts
 * @property-read array $remaining_message
 * @property-read \App\Models\User $user
 * @property-read \App\Models\GameService $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GtnService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GtnService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GtnService query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GtnService whereCheatNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GtnService whereFinished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GtnService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GtnService whereLastNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GtnService whereMaxAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GtnService whereMaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GtnService whereMinNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GtnService whereRandomNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GtnService whereRemainingAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GtnService whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GtnService whereTimesPlayed($value)
 */
	class GtnService extends \Eloquent implements \App\Contracts\IPersistent {}
}

namespace App\GameApps\gtn\Services{
/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Game|null $game
 * @property-read \App\Models\GameService|null $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuessService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuessService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuessService query()
 */
	class GuessService extends \Eloquent {}
}

namespace App\GameApps\mtq\Services{
/**
 * @property int $id
 * @property string|null $questions
 * @property int|null $current_question
 * @property int|null $current_score
 * @property int|null $finished
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Game|null $game
 * @property-read \App\Models\GameService $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MtqService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MtqService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MtqService query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MtqService whereCurrentQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MtqService whereCurrentScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MtqService whereFinished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MtqService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MtqService whereQuestions($value)
 */
	class MtqService extends \Eloquent implements \App\Contracts\IPersistent {}
}

namespace App\GameApps\Common\Components{
/**
 * @property int $id
 * @property string|null $event
 * @property string|null $text
 * @property string|null $style
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ButtonComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ButtonComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ButtonComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ButtonComponent whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ButtonComponent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ButtonComponent whereStyle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ButtonComponent whereText($value)
 */
	class ButtonComponent extends \Eloquent {}
}

namespace App\GameApps\Common\Components{
/**
 * @property int $id
 * @property int|null $x
 * @property int|null $y
 * @property string|null $layout
 * @property string|null $width
 * @property string|null $height
 * @property string|null $image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerComponent whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerComponent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerComponent whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerComponent whereLayout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerComponent whereWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerComponent whereX($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerComponent whereY($value)
 */
	class ContainerComponent extends \Eloquent {}
}

namespace App\GameApps\Common\Components{
/**
 * @property int $id
 * @property string|null $text
 * @property string|null $style
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabelComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabelComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabelComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabelComponent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabelComponent whereStyle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabelComponent whereText($value)
 */
	class LabelComponent extends \Eloquent {}
}

namespace App\GameApps\Common\Components{
/**
 * @property int $id
 * @property string|null $sound
 * @property int|null $loop
 * @property float|null $volume
 * @property int|null $autoplay
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoundComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoundComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoundComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoundComponent whereAutoplay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoundComponent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoundComponent whereLoop($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoundComponent whereSound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoundComponent whereVolume($value)
 */
	class SoundComponent extends \Eloquent {}
}

namespace App\GameApps\Common\Components{
/**
 * @property int $id
 * @property string|null $texture
 * @property int|null $layer
 * @property int|null $x
 * @property int|null $y
 * @property int|null $width
 * @property int|null $height
 * @property float|null $scale
 * @property float|null $pivot_x
 * @property float|null $pivot_y
 * @property float|null $rotation
 * @property int|null $visible
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpriteComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpriteComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpriteComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpriteComponent whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpriteComponent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpriteComponent whereLayer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpriteComponent wherePivotX($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpriteComponent wherePivotY($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpriteComponent whereRotation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpriteComponent whereScale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpriteComponent whereTexture($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpriteComponent whereVisible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpriteComponent whereWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpriteComponent whereX($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SpriteComponent whereY($value)
 */
	class SpriteComponent extends \Eloquent {}
}

namespace App\GameApps\Common\Components{
/**
 * @property int $id
 * @property int|null $width
 * @property int|null $height
 * @property string|null $view_port
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilemapComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilemapComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilemapComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilemapComponent whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilemapComponent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilemapComponent whereViewPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilemapComponent whereWidth($value)
 */
	class TilemapComponent extends \Eloquent {}
}

namespace App\GameApps\Common\Components{
/**
 * @property int $id
 * @property int|null $order
 * @property string|null $data
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilemapLayerComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilemapLayerComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilemapLayerComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilemapLayerComponent whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilemapLayerComponent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilemapLayerComponent whereOrder($value)
 */
	class TilemapLayerComponent extends \Eloquent {}
}

namespace App\GameApps\Common\Components{
/**
 * @property int $id
 * @property int|null $number
 * @property int|null $tile_width
 * @property int|null $tile_height
 * @property string|null $image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilesetComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilesetComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilesetComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilesetComponent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilesetComponent whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilesetComponent whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilesetComponent whereTileHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TilesetComponent whereTileWidth($value)
 */
	class TilesetComponent extends \Eloquent {}
}

namespace App\GameApps\bba\Components{
/**
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameOverStateComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameOverStateComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameOverStateComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameOverStateComponent whereId($value)
 */
	class GameOverStateComponent extends \Eloquent {}
}

namespace App\GameApps\bba\Components{
/**
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InitialStateComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InitialStateComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InitialStateComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InitialStateComponent whereId($value)
 */
	class InitialStateComponent extends \Eloquent {}
}

namespace App\GameApps\bba\Components{
/**
 * @property int $id
 * @property float|null $vx
 * @property float|null $vy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayingStateComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayingStateComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayingStateComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayingStateComponent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayingStateComponent whereVx($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayingStateComponent whereVy($value)
 */
	class PlayingStateComponent extends \Eloquent {}
}

namespace App\GameApps\bba\Components{
/**
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StartingStateComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StartingStateComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StartingStateComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StartingStateComponent whereId($value)
 */
	class StartingStateComponent extends \Eloquent {}
}

namespace App\GameApps\cnt\Components{
/**
 * @property int $id
 * @property int|null $min
 * @property int|null $max
 * @property int|null $value
 * @property int|null $step
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CounterComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CounterComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CounterComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CounterComponent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CounterComponent whereMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CounterComponent whereMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CounterComponent whereStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CounterComponent whereValue($value)
 */
	class CounterComponent extends \Eloquent {}
}

namespace App\GameApps\gtn\Components{
/**
 * @property int $id
 * @property int|null $score
 * @property int|null $max_attempts
 * @property int|null $min_number
 * @property int|null $max_number
 * @property int|null $attempts
 * @property int|null $random_number
 * @property int|null $otron
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDataComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDataComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDataComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDataComponent whereAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDataComponent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDataComponent whereMaxAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDataComponent whereMaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDataComponent whereMinNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDataComponent whereOtron($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDataComponent whereRandomNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDataComponent whereScore($value)
 */
	class GameDataComponent extends \Eloquent {}
}

namespace App\GameApps\gtn\Components{
/**
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameOverStateComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameOverStateComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameOverStateComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameOverStateComponent whereId($value)
 */
	class GameOverStateComponent extends \Eloquent {}
}

namespace App\GameApps\gtn\Components{
/**
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InitialStateViewComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InitialStateViewComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InitialStateViewComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InitialStateViewComponent whereId($value)
 */
	class InitialStateViewComponent extends \Eloquent {}
}

namespace App\GameApps\gtn\Components{
/**
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayingStateComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayingStateComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayingStateComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayingStateComponent whereId($value)
 */
	class PlayingStateComponent extends \Eloquent {}
}

namespace App\GameApps\gtn\Components{
/**
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreparingStateComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreparingStateComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreparingStateComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PreparingStateComponent whereId($value)
 */
	class PreparingStateComponent extends \Eloquent {}
}

namespace App\GameApps\gtn\Components{
/**
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowingClueStateComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowingClueStateComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowingClueStateComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowingClueStateComponent whereId($value)
 */
	class ShowingClueStateComponent extends \Eloquent {}
}

namespace App\GameApps\gtn\Components{
/**
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuccessStateComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuccessStateComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuccessStateComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SuccessStateComponent whereId($value)
 */
	class SuccessStateComponent extends \Eloquent {}
}

namespace App\GameApps\mtq\Components{
/**
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InitialStateComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InitialStateComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InitialStateComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InitialStateComponent whereId($value)
 */
	class InitialStateComponent extends \Eloquent {}
}

namespace App\GameApps\mtq\Components{
/**
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuStateComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuStateComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuStateComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuStateComponent whereId($value)
 */
	class MenuStateComponent extends \Eloquent {}
}

namespace App\GameApps\mtq\Components{
/**
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayingStateComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayingStateComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayingStateComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayingStateComponent whereId($value)
 */
	class PlayingStateComponent extends \Eloquent {}
}

namespace App\GameApps\mtq\Components{
/**
 * @property int $id
 * @property int|null $x
 * @property int|null $y
 * @property string|null $state
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Events\GameEventListenerManager> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Components\Component $super
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TileComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TileComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TileComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TileComponent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TileComponent whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TileComponent whereX($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TileComponent whereY($value)
 */
	class TileComponent extends \Eloquent {}
}

