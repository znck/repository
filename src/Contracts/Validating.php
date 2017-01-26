<?php

namespace Znck\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Validating
{
    /**
     * Validate attributes.
     *
     * @param array $attributes
     * @param Model $model
     *
     * @return $this
     */
    public function validate(array $attributes, Model $model = null);

    /**
     * Validate with given rules.
     *
     * @param array $attributes [description]
     * @param array $rules      [description]
     *
     * @return $this
     */
    public function validateWith(array $attributes, array $rules);

    /**
     * Skip validation.
     *
     * @param bool $skip
     *
     * @return $this
     */
    public function skipValidation($skip = true);
}
