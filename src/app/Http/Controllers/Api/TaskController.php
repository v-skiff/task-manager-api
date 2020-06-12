<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * @OA\Get(
     ** path="/api/tasks",
     *   tags={"Tasks"},
     *   summary="List tasks",
     *   operationId="task_index",
     *   description="Returns list of tasks",
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *)
     **/
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query = Task::query();

        // filtering
        $query->when(request()->filled('filter'), function ($query) {
            [$criteria, $value] = explode(':', request('filter'));
            return $query->where($criteria, $value);
        });

        // ordering
        $sort_direction = $request->input('sort', null);
        if (! empty($sort_direction) && in_array($sort_direction, ['newest_users', 'oldest_users'])) {
            $sort_column = 'created_at';
            $sort_direction = ($sort_direction == 'newest_users') ? 'DESC' : 'ASC';
            $query
                ->leftJoin('users', 'users.id', '=', 'tasks.user_id')
                ->select('tasks.*')
                ->orderBy('users.' . $sort_column, $sort_direction);
        }

        return TaskResource::collection($query->paginate(10));
    }

    /**
     * @OA\Post(
     ** path="/api/tasks",
     *   tags={"Tasks"},
     *   summary="Create task",
     *   operationId="task_store",
     *   description="Creates a task",
     *   @OA\Response(
     *      response=201,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *)
     **/
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return TaskResource
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'status' => 'required|in:View,In Progress,Done',
        ]);

        $task = new Task;
        $task->title = $request->title;
        $task->description = $request->description;
        $task->status = $request->status;
        $task->user_id = (int) $request->user()->id;
        $task->save();

        return new TaskResource($task);
    }

    /**
     * @OA\Get(
     ** path="/api/tasks/{id}",
     *   tags={"Tasks"},
     *   summary="Show task",
     *   operationId="task_show",
     *   description="Creates a task",
     *   @OA\Parameter(
     *      name="id",
     *      description="Task id",
     *      required=true,
     *      in="path",
     *      @OA\Schema(
     *          type="integer"
     *      )
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *)
     **/
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return TaskResource
     */
    public function show($id)
    {
        return new TaskResource(Task::findOrFail($id));
    }

    /**
     * @OA\Put(
     *      path="/api/tasks/{id}",
     *      operationId="task_update",
     *      tags={"Tasks"},
     *      summary="Update existing task",
     *      description="Returns updated task data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Task id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *      ),
     *      @OA\Response(
     *          response=202,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     */
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return TaskResource
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'status' => 'required|in:View,In Progress,Done',
            'user_id' => 'integer'
        ]);

        $task = Task::findOrFail($id);
        $task->title = $request->title;
        $task->description = $request->description;
        $task->status = $request->status;
        if (! empty($request->user_id)) {
            $task->user_id = (int) $request->user_id;
        }
        $task->save();

        return new TaskResource($task);
    }

    /**
     * @OA\Delete(
     *      path="/api/tasks/{id}",
     *      operationId="task_delete",
     *      tags={"Tasks"},
     *      summary="Delete existing task",
     *      description="Deletes a record and returns its content if success",
     *      @OA\Parameter(
     *          name="id",
     *          description="Task id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     */
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return TaskResource
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        if ($task->delete()) {
            return new TaskResource($task);
        }

        return null;
    }

    /**
     * @OA\Get(
     *      path="/api/tasks/change_user",
     *      operationId="task_change_user",
     *      tags={"Tasks"},
     *      summary="Change task user",
     *      description="Returns task data",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function changeUser(Request $request) {
        $request->validate([
            'task_id' => 'required',
            'user_id' => 'required',
        ]);

        $task = Task::findOrFail($request->task_id);
        $task->user_id = $request->user_id;
        $task->save();

        return new TaskResource($task);
    }

    /**
     * @OA\Get(
     *      path="/api/tasks/change_status",
     *      operationId="task_change_status",
     *      tags={"Tasks"},
     *      summary="Change task status",
     *      description="Returns task data",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function changeStatus(Request $request) {
        $request->validate([
            'task_id' => 'required',
            'status' => 'required|in:View,In Progress,Done',
        ]);

        $task = Task::findOrFail($request->task_id);
        $task->status = $request->status;
        $task->save();

        return new TaskResource($task);
    }
}
