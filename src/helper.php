<?php

if (!function_exists('repository')) {
    /**
     * @param \Illuminate\Database\Eloquent\Model|string $model
     *
     * @return \Znck\Repositories\Repository
     */
    function repository($model) {
        if (!is_string($model)) {
            $model = get_class($model);
        }

        if (array_key_exists($model, \Znck\Repositories\Repository::$repositories)) {
            return app(\Znck\Repositories\Repository::$repositories[$model]);
        }

        $name = str_replace(app()->getNamespace(), '', $model);

        if (str_contains($name, ['Models', 'Eloquent'])) {
            $name = str_replace('Models', 'Repositories', $name);
            $name = str_replace('Eloquent', 'Repositories', $name);
        } else {
            $name = 'Repositories\\'.$name;
        }

        $class = app()->getNamespace().$name.'Repository';

        return app($class);
    }
}