<?php

namespace TaskBoard\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="tasks")
 */
class Task
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $board_id;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $tag_id;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $checklist_id;
    
    private $name;
    private $description;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $owner_id;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $responsible_id;
    
    private $created_at;
    private $closed_at;
    private $changed_at;
    private $deadline_at;
}
