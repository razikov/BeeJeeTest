<?php

namespace App\Models;

class JobRepository
{
    private $pdo;
    private $em;

    public function __construct(\PDO $pdo, \Doctrine\ORM\EntityManager $em)
    {
        $this->pdo = $pdo;
        $this->em = $em;
    }

    public function countAll(): int
    {
        return $this->pdo->query('SELECT COUNT(id) FROM jobs')->fetchColumn();
    }

    public function all(int $offset, int $limit, $order = ''): array
    {
        $validateAttribute = function($attribute) {
            if (in_array($attribute, ['name', 'email', 'status'])) {
                return $attribute;
            } else {
                return 'id';
            }
        };
        if ($order) {
            $direction = substr($order, 0, 1) == '-' ? 'DESC' : 'ASC';
            $attribute = $direction == 'DESC' ? substr($order, 1) : $order;
        } else {
            $direction = 'DESC';
            $attribute = 'id';
        }
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM jobs ORDER BY {$validateAttribute($attribute)} {$direction} LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();

        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function find(int $id)
    {
        $job = $this->em->find(\App\Entity\Job::class, $id);
        return $job;
    }
    
    public function save(\App\Entity\Job $job)
    {
        $this->em->persist($job);
        $this->em->flush();
        return true;
    }

    private function hydrate($row): array
    {
        return [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'content' => $row['content'],
            'status' => $row['status'],
            'edited_by_admin' => $row['edited_by_admin'],
        ];
    }
}