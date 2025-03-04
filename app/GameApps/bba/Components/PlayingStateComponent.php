<?php

namespace App\GameApps\bba\Components;

use App\Traits\HasNamespacePrefix;
use Illuminate\Support\Facades\DB;
use App\Models\GameObject\GameObject;
use App\Models\Components\PersistentComponent;

class PlayingStateComponent extends PersistentComponent
{
    use HasNamespacePrefix;

    private const VX = 20;
    private const VY = 20;

    public static function config(): array
    {
        return [
            'vx' => ['float', self::VX],
            'vy' => ['float', self::VY],
        ];
    }

    public function onEnter(): void
    {
        $playingView = $this->gameObject->findChild('playing_view');
        if ($playingView) {
            $playingView->activate();
        }
    }

    public function onExit(): void
    {
        $playingView = $this->gameObject->findChild('playing_view');
        if ($playingView) {
            $playingView->deactivate();
        }
    }

    public function onUpdateEvent(): void
    {
        $ball = $this->findGameObject('ball');
        $this->move($ball, 800, 450);
    }

    public function move(GameObject $ball, $screenWidth, $screenHeight)
    {
        $curVX = $this->vx;
        $curVY = $this->vy;
        $newVX = $curVX;
        $newVY = $curVY;
        $sprite = $ball->getComponent('sprite');
        $sprite_width = $sprite->width * $sprite->scale;
        $sprite_height = $sprite->height * $sprite->scale;
        $curX = $sprite->x;
        $curY = $sprite->y;
        $curR = $sprite->rotation;

        // Actualizar posición
        $newX = $curX + $this->vx;
        $newY = $curY + $this->vy;

        // Detección de colisiones
        if ($newX <= $sprite_width || $newX + $sprite_width >= $screenWidth) {
            $newVX = -$curVX;
            $newX = max($sprite_width, min($newX, $screenWidth - $sprite_width));
        }
        if ($newY <= $sprite_height || $newY + $sprite_height >= $screenHeight) {
            $newVY = -$curVY;
            $newY = max($sprite_height, min($newY, $screenHeight - $sprite_height));
        }

        // Rotación
        $newRotation = ($curR + 5) % 360;

        // Actualizar valores del sprite, la bola o el componente actual si es necesario
        DB::transaction(function () use ($ball, $sprite, $curX, $curY, $curR, $newVX, $newVY, $curVX, $curVY, $newX, $newY, $newRotation) {
            if ($newVX !== $curVX || $newVY !== $curVY) {
                $this->updateQuietly([
                    'vx' => $newVX,
                    'vy' => $newVY,
                ]);
            }
            if ($newX !== $curX || $newY !== $curY || $newRotation !== $curR) {
                $sprite->updateQuietly([
                    'x' => $newX,
                    'y' => $newY,
                    'rotation' => $newRotation,
                ]);
            }
            $ball->updateView();
        });
    }

    public function onRestartEvent()
    {
        $ball = $this->findGameObject('ball');
        $sprite = $ball->getComponent('sprite');
        $sprite->updateQuietly([
            'x' => 400,
            'y' => 225,
            'rotation' => 0,
        ]);
        $this->updateQuietly([
            'vx' => self::VX,
            'vy' => self::VY,
        ]);
    }

    public function view()
    {
        // TODO Revisar esto! Se ve muy raro
        return [
            'updatable' => true,
        ];
    }
}
