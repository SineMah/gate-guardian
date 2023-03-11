<?php

namespace GateGuardian\Creator\Traits;

use GateGuardian\Creator\Exceptions\ProxyMethodNotFound;

trait HasMagicCall
{
    protected bool $reload = true;

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws ProxyMethodNotFound
     */
    public function __call(string $method, array $parameters)
    {
        if(method_exists($this->guard, $method)) {
            $inheritanceResponse = $this->guard->$method(...$parameters);

            return $this->reloadAndReturnResponse($inheritanceResponse);
        }

        throw new ProxyMethodNotFound(sprintf('%s::%s', get_class($this->guard), $method));
    }

    protected function reloadAndReturnResponse(mixed $inheritanceResponse): mixed
    {
        if($this->reload && $this->isSameClass($inheritanceResponse)) {

            $this->guard = $inheritanceResponse;

            // make sure we always use proxy objects if classes match
            return $this;
        }

        return $inheritanceResponse;
    }

    protected function isSameClass(mixed $inheritanceResponse): bool
    {
        if(is_object($inheritanceResponse)) {

            return get_class($this->guard) === get_class($inheritanceResponse);
        }

        return false;
    }
}
