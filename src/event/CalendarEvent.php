<?php

declare(strict_types=1);

namespace JonasWindmann\Calendar\event;

class CalendarEvent {
    private string $id;
    private string $title;
    private string $description;
    private int $timestamp;
    private string $location;
    private bool $isActive;
    
    /**
     * CalendarEvent constructor
     * 
     * @param string $id Unique identifier for the event
     * @param string $title Event title
     * @param string $description Event description
     * @param int $timestamp Event timestamp
     * @param string $location Event location
     * @param bool $isActive Whether the event is active
     */
    public function __construct(
        string $id,
        string $title,
        string $description,
        int $timestamp,
        string $location,
        bool $isActive = true
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->timestamp = $timestamp;
        $this->location = $location;
        $this->isActive = $isActive;
    }
    
    /**
     * Get the event ID
     * 
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }
    
    /**
     * Get the event title
     * 
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }
    
    /**
     * Set the event title
     * 
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self {
        $this->title = $title;
        return $this;
    }
    
    /**
     * Get the event description
     * 
     * @return string
     */
    public function getDescription(): string {
        return $this->description;
    }
    
    /**
     * Set the event description
     * 
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self {
        $this->description = $description;
        return $this;
    }
    
    /**
     * Get the event timestamp
     * 
     * @return int
     */
    public function getTimestamp(): int {
        return $this->timestamp;
    }
    
    /**
     * Set the event timestamp
     * 
     * @param int $timestamp
     * @return self
     */
    public function setTimestamp(int $timestamp): self {
        $this->timestamp = $timestamp;
        return $this;
    }
    
    /**
     * Get the event location
     * 
     * @return string
     */
    public function getLocation(): string {
        return $this->location;
    }
    
    /**
     * Set the event location
     * 
     * @param string $location
     * @return self
     */
    public function setLocation(string $location): self {
        $this->location = $location;
        return $this;
    }
    
    /**
     * Check if the event is active
     * 
     * @return bool
     */
    public function isActive(): bool {
        return $this->isActive;
    }
    
    /**
     * Set whether the event is active
     * 
     * @param bool $isActive
     * @return self
     */
    public function setActive(bool $isActive): self {
        $this->isActive = $isActive;
        return $this;
    }
    
    /**
     * Check if the event is in the future
     * 
     * @return bool
     */
    public function isFuture(): bool {
        return $this->timestamp > time();
    }
    
    /**
     * Check if the event is in the past
     * 
     * @return bool
     */
    public function isPast(): bool {
        return $this->timestamp < time();
    }
    
    /**
     * Check if the event is happening now
     * 
     * @param int $buffer Buffer time in seconds (default: 1 hour)
     * @return bool
     */
    public function isNow(int $buffer = 3600): bool {
        $now = time();
        return $this->timestamp <= $now && $this->timestamp + $buffer >= $now;
    }
}