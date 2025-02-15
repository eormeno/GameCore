<?php

namespace App\Models\Components;

class ComponentMessages extends ComponentFinders
{

    public function updateView(array $attributes): bool
    {
        $view_name = $this->view_name ?? '';
        $messages = $this->super->messages ?? [];
        // si está la clave i18n, entonces se debe traducir
        if (array_key_exists('i18n', $attributes)) {
            $result = $this->recurseAttrs($attributes['i18n'], $view_name);
            if (!$result) {
                return false;
            }
            // copia los valores de i18n a messages
            $messages = array_merge($messages, $attributes['i18n']);
            unset($attributes['i18n']);
        }
        //$this->recurseAttrs($attributes, $view_name);
        $this->super->messages = array_merge($messages, $attributes);
        $this->super->save();
        return true;
    }

    private function recurseAttrs(array &$array, string $prefijo = ''): bool
    {
        foreach ($array as $clave => &$valor) {
            $valor ??= [];
            if (!is_scalar($clave) && $clave !== null) {
                return false;
            }
            if (is_scalar($valor)) {
                $valor = ['value' => $valor];
            }
            // Concatena las claves para formar la ruta actual
            $rutaBase = $prefijo === '' ? (string) $clave : $prefijo;
            $rutaClave = "$rutaBase.$clave";
            if (__($rutaClave) !== $rutaClave) {
                // if i18n key exists, then use it for i18n the value
                //$this->success($rutaClave);
                $valor = __($rutaClave, $valor);
                continue;
            }
            //$this->warn($rutaClave);
            if (is_array($valor) && count($valor) > 0) {
                if (!$this->recurseAttrs($valor, $rutaBase)) {
                    return false;
                }
            }
        }
        return true;
    }

    protected function messages(): array
    {
        return $this->super->messages ?? [];
    }
}
