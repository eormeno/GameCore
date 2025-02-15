<?php

namespace App\Helpers;

use ReflectionClass;
use App\FSM\IState;

class StatesLocalCache
{
    private const INSTANCED_STATES_KEY = 'instanced_states';

    public static function getStateInstance(ReflectionClass $rflStateClass, int $id)
    {
        self::findRegisteredStateInstance($rflStateClass, $id);
        $strStateInstanceForObject = $rflStateClass->getName() . $id;
        return session(self::INSTANCED_STATES_KEY)[$strStateInstanceForObject];
    }

    public static function findRegisteredStateInstance(ReflectionClass $rflStateClass, int $id): IState
    {
        if (!in_array(IState::class, $rflStateClass->getInterfaceNames())) {
            $strShortName = $rflStateClass->getShortName();
            throw new \Exception("The state class [$strShortName] must implement the StateInterface.");
        }
        if (!session()->has(self::INSTANCED_STATES_KEY)) {
            session()->put(self::INSTANCED_STATES_KEY, []);
        }
        $strStateInstanceForObject = $rflStateClass->getName() . $id;
        $arrInstancedStates = session(self::INSTANCED_STATES_KEY);
        if (!array_key_exists($strStateInstanceForObject, $arrInstancedStates)) {
            $staNewInstance = $rflStateClass->newInstance();
            $arrInstancedStates[$strStateInstanceForObject] = $staNewInstance;
            session()->put(self::INSTANCED_STATES_KEY, $arrInstancedStates);
        }
        return $arrInstancedStates[$strStateInstanceForObject];
    }

    public static function reset(): void
    {
        session()->forget(self::INSTANCED_STATES_KEY);
    }
}
