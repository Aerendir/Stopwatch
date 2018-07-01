<?php

/*
 *
 * This file is part of the Serendipity HQ Stopwatch Component.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * (c) Adamo Crespi <hello@aerendir.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with the Symfony Framework.
 */

namespace SerendipityHQ\Component\Stopwatch;

/**
 * Stopwatch provides a way to profile code.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Adamo Crespi <hello@aerendir.me>
 */
class Stopwatch
{
    // Identifier of the root section, the one that contains all the data collected by Stopwatch
    private const STOPWATCH_ROOT     = '__root__';
    private const SECTION            = '__section__';
    private const SECTION_CHILD      = '__section__.child';
    private const STOPWATCH_CATEGORY = 'section';

    /** @var Section[] $sections */
    private $sections;

    /** @var Section[] $activeSections */
    private $activeSections;

    /**
     * Initializes the Stopwatch.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Starts an event.
     *
     * @param string $name     The event name
     * @param string $category The event category
     *
     * @return Event
     */
    public function start(string $name, string $category = null): Event
    {
        return $this->getCurrentSection()->startEvent($name, $category);
    }

    /**
     * Stops an event.
     *
     * @param string $name The event name
     *
     * @return Event
     */
    public function stop(string $name): Event
    {
        return $this->getCurrentSection()->stopEvent($name);
    }

    /**
     * Stops then restarts an event.
     *
     * @param string $name The event name
     *
     * @return Event
     */
    public function lap(string $name): Event
    {
        return $this->getCurrentSection()->stopEvent($name)->start();
    }

    /**
     * Returns a specific event by name.
     *
     * @param string $name The event name
     *
     * @return Event
     */
    public function getEvent(string $name): Event
    {
        return $this->getCurrentSection()->getEvent($name);
    }

    /**
     * Checks if the event was started.
     *
     * @param string $name The event name
     *
     * @return bool
     */
    public function isStarted(string $name): bool
    {
        return $this->getCurrentSection()->isEventStarted($name);
    }

    /**
     * Gets all events for a given section.
     *
     * @param string $id A section identifier
     *
     * @return Event[]
     */
    public function getSectionEvents(string $id): array
    {
        return $this->hasSection($id) ? $this->getSection($id)->getEvents() : [];
    }

    /**
     * Creates a new section or re-opens an existing section.
     *
     * @param string|null $id The id of the session to re-open, null to create a new one
     *
     * @throws \LogicException When the section to re-open is not reachable
     */
    public function openSection(?string $id = null): void
    {
        // The $id is accepted only to re-open a previously closed section
        if (null !== $id && null === $this->getCurrentSection()->getChildSection($id)) {
            throw new \LogicException(sprintf('The section "%s" has been started at an other level and can not be opened.', $id));
        }

        // Create a new Event meant to measure the timing and memory of the opening child section
        $this->start(self::SECTION_CHILD, self::STOPWATCH_CATEGORY);

        // Then, actually open the child section and append it to the active ones
        $this->activeSections[] = $this->getCurrentSection()->openChildSection($id);

        // Now start another Event.
        // This is opened in the just created child section (that is now
        // the currently active one) and measures the timing and memory
        // of the just created child section.
        // If you like, compare Event self::SECTION_CHILD and Event self::SECTION
        // to know how much time and memory is required to open a new Section.
        $this->start(self::SECTION, self::STOPWATCH_CATEGORY);
    }

    /**
     * Stops the last started section.
     *
     * The id parameter is assigned to the closing Section and can be
     * used later to retrieve the events from this section with
     * self::getSectionEvents($id).
     *
     * @param string $id The identifier of the section
     *
     * @throws \LogicException When there's no started section to be stopped
     */
    public function stopSection(string $id): void
    {
        // First, stop the Event self::SECTION.
        // This is the Event created in every Section and that
        // measures the timing and memory of the Section itself
        $this->stop(self::SECTION);

        // This happens if the only active section is the self::STOPWATCH_ROOT one.
        if (1 === count($this->activeSections)) {
            throw new \LogicException('There is no started section to stop.');
        }

        // Remove the closing section from the active ones
        /** @var Section $section */
        $section = array_pop($this->activeSections);

        // Add the closing section to list of Sections managed by Stopwatch
        // AND ASSIGN THE GIVEN ID TO IT
        $this->sections[$id] = $section->stopSection($id);

        // Then stop the Event that measures the time (and memory) passed
        // between the creation of the Section and its closing of now
        $this->stop(self::SECTION_CHILD);
    }

    /**
     * @param string $id
     *
     * @return Section
     */
    public function getSection(string $id): Section
    {
        if (false === $this->hasSection($id)) {
            throw new \InvalidArgumentException(sprintf('The section "%s" doesn\'t exist. Maybe you have not still closed it.', $id));
        }

        return $this->sections[$id];
    }

    /**
     * @return Section[]
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasSection(string $id): bool
    {
        return isset($this->sections[$id]);
    }

    /**
     * Resets the stopwatch to its original state.
     *
     * Practically recreates the root Section in which all other Sections,
     * Events and Periods collected are stored.
     */
    public function reset(): void
    {
        // On initialization, Section doesn't collect anything nor has any Event
        $this->sections = $this->activeSections = [self::STOPWATCH_ROOT => new Section()];
    }

    /**
     * @return Section
     */
    private function getCurrentSection():Section
    {
        return end($this->activeSections);
    }
}
