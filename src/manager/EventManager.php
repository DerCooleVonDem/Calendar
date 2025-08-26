<?php

declare(strict_types=1);

namespace JonasWindmann\Calendar\manager;

use JonasWindmann\Calendar\event\CalendarEvent;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class EventManager {
    private Plugin $plugin;
    private array $events = [];
    
    /**
     * EventManager constructor
     * 
     * @param Plugin $plugin The plugin instance
     */
    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;
        $this->loadEvents();
    }
    
    /**
     * Load events from storage
     */
    public function loadEvents(): void {
        $file = $this->plugin->getDataFolder() . "events.json";
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if (isset($data["events"]) && is_array($data["events"])) {
                foreach ($data["events"] as $eventData) {
                    $event = new CalendarEvent(
                        $eventData["id"],
                        $eventData["title"],
                        $eventData["description"],
                        $eventData["timestamp"],
                        $eventData["location"],
                        $eventData["isActive"]
                    );
                    $this->events[$event->getId()] = $event;
                }
            }
        }
    }
    
    /**
     * Save events to storage
     */
    public function saveEvents(): void {
        $data = ["events" => []];
        foreach ($this->events as $event) {
            $data["events"][] = [
                "id" => $event->getId(),
                "title" => $event->getTitle(),
                "description" => $event->getDescription(),
                "timestamp" => $event->getTimestamp(),
                "location" => $event->getLocation(),
                "isActive" => $event->isActive()
            ];
        }
        
        $file = $this->plugin->getDataFolder() . "events.json";
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    /**
     * Create a new event
     * 
     * @param string $title Event title
     * @param string $description Event description
     * @param int $timestamp Event timestamp
     * @param string $location Event location
     * @param bool $isActive Whether the event is active
     * @return CalendarEvent The created event
     */
    public function createEvent(
        string $title,
        string $description,
        int $timestamp,
        string $location,
        bool $isActive = true
    ): CalendarEvent {
        $id = uniqid("event_");
        $event = new CalendarEvent($id, $title, $description, $timestamp, $location, $isActive);
        $this->events[$id] = $event;
        $this->saveEvents();
        return $event;
    }
    
    /**
     * Update an existing event
     * 
     * @param string $id Event ID
     * @param string $title Event title
     * @param string $description Event description
     * @param int $timestamp Event timestamp
     * @param string $location Event location
     * @param bool $isActive Whether the event is active
     * @return CalendarEvent|null The updated event, or null if the event doesn't exist
     */
    public function updateEvent(
        string $id,
        string $title,
        string $description,
        int $timestamp,
        string $location,
        bool $isActive
    ): ?CalendarEvent {
        if (!isset($this->events[$id])) {
            return null;
        }
        
        $event = $this->events[$id];
        $event->setTitle($title)
            ->setDescription($description)
            ->setTimestamp($timestamp)
            ->setLocation($location)
            ->setActive($isActive);
        
        $this->saveEvents();
        return $event;
    }
    
    /**
     * Delete an event
     * 
     * @param string $id Event ID
     * @return bool True if the event was deleted, false if it doesn't exist
     */
    public function deleteEvent(string $id): bool {
        if (!isset($this->events[$id])) {
            return false;
        }
        
        unset($this->events[$id]);
        $this->saveEvents();
        return true;
    }
    
    /**
     * Get an event by ID
     * 
     * @param string $id Event ID
     * @return CalendarEvent|null The event, or null if it doesn't exist
     */
    public function getEvent(string $id): ?CalendarEvent {
        return $this->events[$id] ?? null;
    }
    
    /**
     * Get all events
     * 
     * @return CalendarEvent[] Array of events
     */
    public function getEvents(): array {
        return $this->events;
    }
    
    /**
     * Get upcoming events
     * 
     * @param int $limit Maximum number of events to return (0 for unlimited)
     * @return CalendarEvent[] Array of upcoming events
     */
    public function getUpcomingEvents(int $limit = 0): array {
        $events = array_filter($this->events, function(CalendarEvent $event) {
            return $event->isActive() && $event->isFuture();
        });
        
        // Sort by timestamp (ascending)
        uasort($events, function(CalendarEvent $a, CalendarEvent $b) {
            return $a->getTimestamp() <=> $b->getTimestamp();
        });
        
        if ($limit > 0) {
            return array_slice($events, 0, $limit);
        }
        
        return $events;
    }
    
    /**
     * Get past events
     * 
     * @param int $limit Maximum number of events to return (0 for unlimited)
     * @return CalendarEvent[] Array of past events
     */
    public function getPastEvents(int $limit = 0): array {
        $events = array_filter($this->events, function(CalendarEvent $event) {
            return $event->isActive() && $event->isPast();
        });
        
        // Sort by timestamp (descending)
        uasort($events, function(CalendarEvent $a, CalendarEvent $b) {
            return $b->getTimestamp() <=> $a->getTimestamp();
        });
        
        if ($limit > 0) {
            return array_slice($events, 0, $limit);
        }
        
        return $events;
    }
    
    /**
     * Get the current event (happening now)
     * 
     * @return CalendarEvent|null The current event, or null if there is none
     */
    public function getCurrentEvent(): ?CalendarEvent {
        foreach ($this->events as $event) {
            if ($event->isActive() && $event->isNow()) {
                return $event;
            }
        }
        
        // If no event is happening now, return the next upcoming event
        $upcomingEvents = $this->getUpcomingEvents(1);
        return !empty($upcomingEvents) ? reset($upcomingEvents) : null;
    }
}