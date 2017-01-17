<?php

namespace Znck\Repositories\Traits;

use Znck\Repositories\Contracts\Model;

trait ValidationHelper {
    /**
     * @var \Illuminate\Contracts\Validation\Factory
     */
    protected $validator;

    /**
     * Validation rules.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * @var bool
     */
    protected $skipValidation = false;

    /**
     * Skip validation.
     *
     * @param bool $skip
     *
     * @return \Znck\Repositories\Contracts\Repository
     */
    public function skipValidation($skip = true) {
        $this->skipValidation = $skip;

        return $this;
    }

    /**
     * Validate attributes.
     *
     * @param array $attributes
     * @param Model $model
     *
     * @throws \Illuminate\Validation\ValidationException
     * @return $this
     */
    public function validate(array $attributes, Model $model = null) {
        return $this->validateWith(
            $this->prepareAttributes($attributes),
            $this->getRules($attributes, $model)
        );
    }

    public function validateWith(array $attributes, array $rules) {
        if ($this->skipValidation) {
            return $this;
        }

        if (!$this->validator) {
            $this->validator = $this->app->make(Factory::class);
        }

        $validator = $this->validator->make($attributes, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this;
    }

    public function prepareAttributes(array $attributes) {
        return $attributes;
    }

    /**
     * @param array $attributes
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    public function getRules(array $attributes, Model $model = null): array {
        if (is_null($model)) {
            return $this->getCreateRules($attributes);
        }

        return $this->getUpdateRules($this->getCreateRules($attributes), $attributes, $model);
    }

    /**
     *
     * @param  array  $attributes [description]
     * @return array             [description]
     */
    public function getCreateRules(array $attributes) {
        return $this->rules;
    }

    /**
     * @param array $rules
     * @param array $attributes
     * @param Model $model
     *
     * @return array
     */
    public function getUpdateRules(array $rules, array $attributes, $model) {
        return array_only($rules, array_keys($attributes));
    }
}
