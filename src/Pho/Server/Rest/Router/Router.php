<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pho\Server\Rest\Router;

class Router extends Select
{

    protected $locked = [];
    protected $disabled = [];

    public function list(): array
    {
        return $this->selected;
    }

    public function print(): void
    {
        print_r($this->export());
    }

    public function export(): array
    {
        return [
            "All" => $this->store,
            "Selected" => $this->selected,
            "Locked" => $this->locked,
            "Disabled" => $this->disabled
        ];
    }

    /**
     * If enabled, selected routes can be accessed by admins only.
     */
    public function lock(): void
    {
        $this->locked = array_merge($this->locked, $this->selected);
    }

    /**
     * If enabled, selected routes can be accessed by anyone
     */
    public function unlock(): void
    {
        foreach($this->selected as $key=>$value) {
            if(isset($this->locked[$key])) {
                unset($this->locked[$key]);
            }
        }
    }

    /**
     * Check if the selected route is already locked
     * @return boolean
     */
    protected function locked(string $key): boolean
    {
        return isset($this->locked[$key]);
    }

    /**
     * Check if the selected route is already disabled
     * @return boolean
     */
    protected function disabled(string $key): boolean
    {
        return isset($this->disabled[$key]);
    }

    /**
     * No one can access selected routes
     */
    public function disable(): void
    {
        $this->disabled = array_merge($this->disabled, $this->selected);
    }

    /**
     * Those with the required privileges can access the selected routes
     */
    public function enable(): void
    {
        foreach($this->selected as $key=>$value) {
            if(isset($this->disabled[$key])) {
                unset($this->disabled[$key]);
            }
        }
    }

}