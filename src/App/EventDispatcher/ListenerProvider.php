<?php

namespace App\EventDispatcher;

use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface
{
    private $listeners = [];

    /**
     * {@inheritdoc}
     */
    public function getListenersForEvent(object $event): iterable
    {
        $eventName = \get_class($event);
        if (isset($this->listeners[$eventName])) {
            return $this->listeners[$eventName];
        } else {
            return [];
        }
    }

    /**
     *  $lp->add($event, '1', 'is_object');
     *  $lp->add($event, '2', [SiteController::class, 'foo']);
     *  $lp->add($event, '3', SiteController::class.'::bar');
     *  $lp->add($event, '4', [$listener, 'test']);
     *  $lp->add($event, '5', function ($event) {echo "callback\n"; return;});
     *  $lp->add($event, '6', new class {
     *      public function __invoke() {
     *          echo "invoke\n";
     *          return;
     *      }
     *  });
     */
    public function add(string $eventName, $key, callable $listener)
    {
        $this->listeners[$eventName][$key] = $listener;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $eventName, $key)
    {
        return isset($this->listeners[$eventName][$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $eventName, $key)
    {
        if ($this->has($eventName, $key)) {
            unset($this->listeners[$eventName][$key]);
            if (count($this->listeners[$eventName]) < 1) {
                unset($this->listeners[$eventName]);
            }
            return true;
        }
        
        return false;
    }
}
