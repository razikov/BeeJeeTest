<?php

namespace App\Models;

class JobRepository
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function countAll(): int
    {
        return $this->pdo->query('SELECT COUNT(id) FROM jobs')->fetchColumn();
    }

    public function all(int $offset, int $limit, $order = ''): array
    {
        if ($order) {
            $orderDirection = substr($order, 0, 1) == '-' ? 'DESC' : 'ASC';
            $orderAttr = $orderDirection == 'DESC' ? substr($order, 1) : $order;
            $orderBy = sprintf('ORDER BY %s %s', $orderAttr, $orderDirection);
        } else {
            $orderBy = 'ORDER BY id ASC';
        }
        $stmt = $this->pdo->prepare('
            SELECT *
            FROM jobs '.$orderBy.' LIMIT :limit OFFSET :offset
        ');

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();

        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function find(int $id)
    {
        
        $stmt = $this->pdo->prepare('SELECT j.* FROM jobs j WHERE id = :id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        if (!$job = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return null;
        }
        
        return $this->hydrateObj($job);
    }
    
    public function save(\App\Entity\Job $job)
    {
        if ($job->getId() === null) {
            $stmt = $this->pdo->prepare("INSERT INTO jobs (name, email, content, status, edited_by_admin) value (:name, :email, :content, :status, :edited_by_admin)");
            $stmt->bindValue(':name', $job->getName(), \PDO::PARAM_STR);
            $stmt->bindValue(':email', $job->getEmail(), \PDO::PARAM_STR);
            $stmt->bindValue(':content', $job->getContent(), \PDO::PARAM_STR);
            $stmt->bindValue(':status', $job->getStatus(), \PDO::PARAM_INT);
            $stmt->bindValue(':edited_by_admin', $job->getEditedByAdmin(), \PDO::PARAM_INT);
        } else {
            $stmt = $this->pdo->prepare("UPDATE jobs SET name = :name, email = :email, content = :content, status = :status, edited_by_admin = :edited_by_admin WHERE id = :id");
            $stmt->bindValue(':id', $job->getId(), \PDO::PARAM_INT);
            $stmt->bindValue(':name', $job->getName(), \PDO::PARAM_STR);
            $stmt->bindValue(':email', $job->getEmail(), \PDO::PARAM_STR);
            $stmt->bindValue(':content', $job->getContent(), \PDO::PARAM_STR);
            $stmt->bindValue(':status', $job->getStatus(), \PDO::PARAM_BOOL);
            $stmt->bindValue(':edited_by_admin', $job->getEditedByAdmin(), \PDO::PARAM_BOOL);
        }
        return $stmt->execute();
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

    private function hydrateObj($row)
    {
        $model = new \App\Entity\Job($row);
        return $model;
    }
}