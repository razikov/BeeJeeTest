<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="jobs")
 */
class Job
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string")
     */
    private $name;
    
    /**
     * @ORM\Column(type="string")
     */
    private $email;
    
    /**
     * @ORM\Column(type="string")
     */
    private $content;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $status;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $edited_by_admin;
    
    public function __construct(array $dto = [])
    {
        $this->id = $dto['id'] ?? null;
        $this->name = $dto['name'] ?? '';
        $this->email = $dto['email'] ?? '';
        $this->content = $dto['content'] ?? '';
        $this->status = $dto['status'] ?? false;
        $this->edited_by_admin = $dto['edited_by_admin'] ?? false;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function setName($value)
    {
        return $this->name = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setEmail($value)
    {
        return $this->email = $value;
    }
    
    public function getEmail(): string
    {
        return $this->email;
    }
    
    public function setContent($value)
    {
        if (!empty($this->content) && $this->content != $value) {
            $this->edited_by_admin = true;
        }
        return $this->content = $value;
    }
    
    public function getContent(): string
    {
        return $this->content;
    }
    
    public function setStatus($value)
    {
        return $this->status = $value;
    }
    
    public function getStatus(): bool
    {
        return (bool)$this->status;
    }
    
    public function getEditedByAdmin(): bool
    {
        return (bool)$this->edited_by_admin;
    }
    
    public function loadForm(\App\Models\JobForm $form)
    {
        $this->setName($form->name);
        $this->setEmail($form->email);
        $this->setContent($form->content);
        $this->setStatus($form->status);
    }
    
    public function getDto()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'content' => $this->content,
            'status' => $this->status,
            'edited_by_admin' => $this->edited_by_admin,
        ];
    }


}