<?php

namespace App\Services;

use Psr\EventDispatcher\EventDispatcherInterface;
use App\Entity\Task;
use App\Models\TaskForm;

class TaskService
{
    private $tasks;
    private $dispatcher;
 
    public function __construct(TaskRepository $tasks, EventDispatcherInterface $dispatcher)
    {
        $this->tasks = $tasks;
        $this->dispatcher = $dispatcher;
    }
    
    public function getTask(int $id): Task
    {
        $task = $this->tasks->find($id);
        if (!$task) {
            throw new \DomainException();
        }
        return $task;
    }
 
    public function create(TaskForm $form): bool
    {
        $task = new Task();
        $task->setName($form->name);
        $task->setEmail($form->email);
        $task->setContent($form->content);
        $task->setStatus($form->status);
        
        $result = $this->tasks->save($task);
//        $this->dispatcher->dispatch(new CreateTaskEvent($task));
        return $result;
    }
 
    public function update($id, TaskForm $form): bool
    {
        $task = $this->getTask($id);
        $task->setName($form->name);
        $task->setEmail($form->email);
        $task->setContent($form->content);
        $task->setStatus($form->status);
        
        $result = $this->tasks->save($task);
//        $this->dispatcher->dispatch(new UpdateTaskEvent($task));
        return $result;
    }
 
//    public function remove($id): bool
//    {
//        $task = $this->getTask($id);
//        $result = $this->tasks->remove($task);
//        $this->dispatcher->dispatch(new RemoveTaskEvent($task));
//        return $result;
//    }
}
