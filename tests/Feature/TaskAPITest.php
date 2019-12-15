<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Project;
use App\User;
use App\ProjectsUsers;
use App\Status;
use App\Task;
use App\TasksUsers;

class TaskAPITest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $project;
    private $status;
    private $task;
    private $header;
    private $taskAPI = '/api/tasks';
    private $assignTaskAPI = '/api/assign_task';
    private $accessTaskAPI = '';

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザー作成
        $this->user = factory(User::class)->create();

        // テストプロジェクトを作成
        $this->project = factory(Project::class)->create();

        //プロジェクトとユーザーの関係を作成
        $project_user = new ProjectsUsers();
        $project_user->user_id = $this->user->id;
        $project_user->project_id = $this->project->id;
        $project_user->save();

        //ワークフローを作成
        $this->status = new Status();
        $this->status->project_id = $this->project->id;
        $this->status->name = 'TODO';
        $this->status->order = 1;
        $this->status->save();

        //タスクを作成
        $this->task = new Task();
        $this->task->status_id = $this->status->id;
        $this->task->name = 'システム設計';
        $this->task->description = '設計の説明文';
        $this->task->deadline = date('Y-m-d', strtotime('2020-10-10'));
        $this->task->save();

        //ユーザーとタスクの関係を作成
        $task_user = new TasksUsers();
        $task_user->user_id = $this->user->id;
        $task_user->task_id = $this->task->id;
        $task_user->save();

        $this->accessTaskAPI = $this->taskAPI . '/' . $this->task->id;

        $this->header = [
            'Authorization' => 'Bearer ' . $this->user->api_token,
            'Accept' => 'application/json',
        ];
    }

    /*
     * プロジェクトで新しいタスクを作成の周りのテストです。
     * api_tokenがHeaderに含まらないとエラー
     * 作成したタスクを保存されたかチェック
     * タスクとユーザーの関係を作成されたか。
     * サーパーから返したレスポンスの形式があっているか
     * ワークフロー・プロジェクトが存在していない場合、エラーが出るか
    */
    public function testIfCreateTask()
    {

        $query = [
            'project_id' => $this->project->id,
            'status_id' => $this->status->id,
            'name' => '新しいタスク',
            'description' => 'タスクの説明文',
            'deadline' => date('Y-m-d', strtotime('2020-10-10'))
        ];

        //api_tokenがHeaderに含まらないとエラー
        $response = $this->json('POST', $this->taskAPI, $query, []);
        $response->assertStatus(401);

        //新しいタスク作成というリクエストを送る
        $response = $this->json('POST', $this->taskAPI, $query, $this->header);

        $response->assertStatus(201);

        //作成したタスクを保存されたかチェック
        $task = Task::all()->last();
        $this->assertEquals($query['status_id'], $task->status_id);
        $this->assertEquals($query['name'], $task->name);
        $this->assertEquals($query['description'], $task->description);
        $this->assertEquals($query['deadline'], $task->deadline);

        //タスクとユーザーの関係を作成されたか
        $task_user = TasksUsers::all()->last();
        $this->assertEquals($this->user->id, $task_user->user_id);
        $this->assertEquals($task->id, $task_user->task_id);

        //サーパーから返したレスポンスの形式があっているか
        $response->assertJson([
            'data' => [
                'status_id' => $task->status_id,
                'name' => $task->name,
                'description' => $task->description,
                'deadline' => $task->deadline
            ],
            'version' => '1.0.0',
            'author_url' => 'https://github.com/dachoa1995'
        ]);

        //プロジェクトが存在していない場合、エラーが出るか
        $query['project_id'] = 300;
        $response = $this->json('POST', $this->taskAPI, $query, $this->header);
        $response->assertStatus(403);

        //ワークフローが存在していない場合、エラーが出るか
        $query['project_id'] = $this->project->id;
        $query['status_id'] = 300;
        $response = $this->json('POST', $this->taskAPI, $query, $this->header);
        $response->assertStatus(404);
    }

    /*
     * プロジェクトでのタスクを取得の周りのテストです。
     * api_tokenがHeaderに含まらないとエラー
     * 自分のプロジェクトでタスクを取得できるか
     * サーパーから返したレスポンスの形式があっているか
     * 存在していないプロジェクトでタスクを取得すれば、エラー出るか
     * 存在していないタスクを取得すれば、エラー出るか
    */
    public function testIfAccessTask()
    {
        $query = [
            'project_id' => $this->project->id,
        ];

        //api_tokenがHeaderに含まらないとエラー
        $response = $this->json('GET', $this->accessTaskAPI, $query, []);
        $response->assertStatus(401);

        //自分のプロジェクトでタスクを取得できるか
        $response = $this->json('GET', $this->accessTaskAPI, $query, $this->header);

        //サーパーから返したレスポンスの形式があっているか
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status_id' => $this->task->status_id,
                    'name' => $this->task->name,
                    'description' => $this->task->description,
                    'deadline' => $this->task->deadline
                ],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);

        //存在していないプロジェクトでタスクを取得すれば、エラー出るか
        $query['project_id'] = 300;
        $response = $this->json('GET', $this->accessTaskAPI, $query, $this->header);
        $response->assertStatus(403);

        //存在していないタスクを取得すれば、エラー出るか
        $query['project_id'] = $this->project->id;
        $response = $this->json('GET', $this->taskAPI . '/300', $query, $this->header);
        $response->assertStatus(404);
    }

    /*
     * プロジェクトでのタスクを修正の周りのテストです。
     * api_tokenがHeaderに含まらないとエラー
     * 自分のプロジェクトでのタスクを編集できるか
     * サーパーから返したレスポンスの形式があっているか
     * プロジェクトでのタスクは編集して、保存されたか
     * 存在していないプロジェクトでのタスクを編集すれば、エラー出るか
     * プロジェクトで存在していないタスクを編集すれば、エラー出るか
     */
    public function testIfChangeTask()
    {
        $query = [
            'project_id' => $this->project->id,
            'status_id' => $this->status->id,
            'name' => 'new name',
            'description' => 'new description',
            'deadline' => date('Y-m-d', strtotime('2020-10-10'))
        ];

        //api_tokenがHeaderに含まらないとエラー
        $response = $this->json('PUT', $this->accessTaskAPI, $query, []);
        $response->assertStatus(401);

        //自分のプロジェクトでのタスクを編集できるか
        $response = $this->json('PUT', $this->accessTaskAPI, $query, $this->header);

        //サーパーから返したレスポンスの形式があっているか
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status_id' => $query['status_id'],
                    'name' => $query['name'],
                    'description' => $query['description'],
                    'deadline' => $query['deadline']
                ],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);

        //プロジェクトでのタスクは編集して、保存されたか
        $task = Task::all()->last();
        $this->assertEquals($query['status_id'], $task->status_id);
        $this->assertEquals($query['name'], $task->name);
        $this->assertEquals($query['description'], $task->description);
        $this->assertEquals($query['deadline'], $task->deadline);

        //存在していないプロジェクトでのタスクを編集すれば、エラー出るか
        $query['project_id'] = 300;
        $response = $this->json('PUT', $this->accessTaskAPI, $query, $this->header);
        $response->assertStatus(403);

        //プロジェクトで存在していないタスクを編集すれば、エラー出るか
        $query['project_id'] = $this->project->id;
        $response = $this->json('PUT', $this->taskAPI . '/300', $query, $this->header);
        $response->assertStatus(404);
    }

    /*
     * プロジェクトでタスク削除の周りのテストです
     * api_tokenがHeaderに含まらないとエラー出るか
     * 存在していないタスクを削除できるか
     * 自分のタスクを削除できるか
     * サーパーから返したレスポンスの形式があっているか
     * タスクはデーターベースで削除されたか
     */
    public function testIfDeleteTask()
    {

        //api_tokenがHeaderに含まらないとエラー出るか
        $response = $this->json('DELETE', $this->accessTaskAPI, [], []);
        $response->assertStatus(401);

        //存在していないタスクを削除できるか
        $query = [
            'project_id' => $this->project->id,
        ];
        $response = $this->json('DELETE', $this->taskAPI . '/300', $query, $this->header);
        $response->assertStatus(404);

        //自分のタスクを削除できるか
        $response = $this->json('DELETE', $this->accessTaskAPI, $query, $this->header);

        //サーパーから返したレスポンスの形式があっているか
        $response->assertStatus(204);

        //タスクはデーターベースで削除されたか
        $task = Task::first();
        $this->assertEquals(is_null($task), true);

        $tasks_users = TasksUsers::first();
        $this->assertEquals(is_null($tasks_users), true);
    }

    /*
     * タスクに担当者アサインの周りのテストです
     * api_tokenがHeaderに含まらないとエラー出るか
     * サーパーから返したレスポンスの形式があっているか
     * タスクとユーザーの関係を作成されたか
     * 存在していないタスクに担当者アサインできるか
     */
    public function testIfAssignToTask()
    {
        //他のユーザーテスト作成
        $another_user = factory(User::class)->create();

        //api_tokenがHeaderに含まらないとエラー出るか
        $query = [
            'email' => $another_user->email,
            'project_id' => $this->project->id,
            'task_id' => $this->task->id,
        ];
        $response = $this->json('POST', $this->assignTaskAPI, $query, []);
        $response->assertStatus(401);

        //タスクに担当者アサインする
        $response = $this->json('POST', $this->assignTaskAPI, $query, $this->header);

        //サーパーから返したレスポンスの形式があっているか
        $response->assertStatus(204);

        //タスクとユーザーの関係を作成されたか
        $tasks_users = TasksUsers::all()->last();
        $this->assertEquals($tasks_users['user_id'], $another_user->id);
        $this->assertEquals($tasks_users['task_id'], $this->task->id);

        //存在していないタスクに担当者アサインできるか
        $query['task_id'] = 300;
        $response = $this->json('POST', $this->assignTaskAPI, $query, $this->header);
        $response->assertStatus(404);
    }

    /*
     * プロジェクトでのタスク一覧を取得の周りのテストです。
     * api_tokenがHeaderに含まらないとエラー出るか
     * 存在していないプロジェクトでのタスク一覧を取得すれば、エラー出るか
     * 存在していないワークフローでのタスク一覧を取得すれば、エラー出るか
     * 自分のプロジェクトでのタスク一覧を取得できるか
     */
    public function testIfGetTaskList()
    {
        //api_tokenがHeaderに含まらないとエラー出るか
        $response = $this->json('GET', $this->taskAPI, [], []);
        $response->assertStatus(401);

        //存在していないプロジェクトでのタスク一覧を取得すれば、エラー出るか
        $query = [
            'project_id' => 300,
            'status_id' => $this->status->id,
        ];
        $response = $this->json('GET', $this->taskAPI, $query, $this->header);
        $response->assertStatus(403);

        //自分のプロジェクトでのタスク一覧を取得できるか
        $query['project_id'] = $this->project->id;

        $response = $this->json('GET', $this->taskAPI, $query, $this->header);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->task->id,
                        'status_id' => $this->status->id,
                        'name' => $this->task->name,
                        'description' => $this->task->description,
                        'deadline' => $this->task->deadline
                    ],
                ],
                'links' => [
                    'first' => 'http://localhost/api/tasks?page=1',
                    'last' => 'http://localhost/api/tasks?page=1',
                    'prev' => null,
                    'next' => null
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => 'http://localhost/api/tasks',
                    'per_page' => 10
                ]
            ]);
    }

    /*
     * 権限がないアクセスの周りのテストです。
     * 他の人のプロジェクトでタスクを作成できるか
     * 他の人のプロジェクトのタスクを取得できるか
     * 他の人のプロジェクトのタスクを編集できるか
     * 他の人のプロジェクトのタスクを削除できるか
     * 他の人のプロジェクトのタスク一覧を取得できるか
     */
    public function testIfAccessWithoutAuth()
    {
        //他の人のプロジェクトでタスクを作成できるか
        $another_user = factory(User::class)->create();
        $header = [
            'Authorization' => 'Bearer ' . $another_user->api_token,
            'Accept' => 'application/json',
        ];
        $query = [
            'project_id' => $this->project->id,
            'status_id' => $this->status->id,
            'name' => '新しいタスク',
            'description' => 'タスクの説明文',
            'deadline' => date('Y-m-d', strtotime('2020-10-10'))
        ];

        $response = $this->json('POST', $this->taskAPI, $query, $header);
        $response->assertStatus(403);

        //他の人のプロジェクトのタスクを取得できるか
        $query['task_id'] = $this->task->id;
        $response = $this->json('GET', $this->accessTaskAPI, $query, $header);
        $response->assertStatus(403);

        //他の人のプロジェクトのタスクを編集できるか
        $response = $this->json('PUT', $this->accessTaskAPI, $query, $header);
        $response->assertStatus(403);

        //他の人のプロジェクトのタスクを削除できるか
        $response = $this->json('DELETE', $this->accessTaskAPI, $query, $header);
        $response->assertStatus(403);
    }
}
