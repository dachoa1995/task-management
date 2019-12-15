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
use App\Comment;

class CommentAPITest extends TestCase
{
    use RefreshDatabase;
    private $user;
    private $project;
    private $status;
    private $task;
    private $comment;
    private $header;
    private $commentAPI = '/api/comments';
    private $accessCommentAPI = '';


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

        //テストワークフローを作成
        $this->status = new Status();
        $this->status->project_id = $this->project->id;
        $this->status->name = 'TODO';
        $this->status->order = 1;
        $this->status->save();

        //テストタスクを作成
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

        //テストコメントを作成
        $this->comment = new Comment();
        $this->comment->user_id = $this->user->id;
        $this->comment->task_id = $this->task->id;
        $this->comment->content = 'コメントの内容';
        $this->comment->save();

        $this->accessCommentAPI = $this->commentAPI . '/' . $this->comment->id;

        $this->header = [
            'Authorization' => 'Bearer ' . $this->user->api_token,
            'Accept' => 'application/json',
        ];
    }

    /*
     * タスクでコメントを投稿の周りのテストです。
     * api_tokenがHeaderに含まらないとエラー
     * サーパーから返したレスポンスの形式があっているか
     * 作成したコメントを保存されたかチェック
     * 存在していないタスクでコメントできるか
    */
    public function testIfCreateComment()
    {

        $comment_content = [
            'project_id' => $this->project->id,
            'task_id' => $this->task->id,
            'content' => 'new comment content'
        ];
        //api_tokenがHeaderに含まらないとエラー
        $response = $this->json('POST', $this->commentAPI, $comment_content, []);
        $response->assertStatus(401);

        //コメントするというリクエストを送る
        $response = $this->json('POST', $this->commentAPI, $comment_content, $this->header);

        //サーパーから返したレスポンスの形式があっているか
        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'task_id' => $comment_content['task_id'],
                    'content' => $comment_content['content'],
                ],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);

        //作成したコメントを保存されたかチェック
        $comment = Comment::all()->last();
        $this->assertEquals($comment_content['task_id'], $comment->task_id);
        $this->assertEquals($comment_content['content'], $comment->content);

        //存在していないタスクでコメントできるか
        $comment_content['task_id'] = 300;
        $response = $this->json('POST', $this->commentAPI, $comment_content, $this->header);
        $response->assertStatus(404);
    }

    /*
     * コメントを修正の周りのテストです。
     * api_tokenがHeaderに含まらないとエラー
     * 自分のコメントを編集できるか
     * サーパーから返したレスポンスの形式があっているか
     * 編集したコメントは保存されたか
     * 存在していないコメントを編集すれば、エラー出るか
    */
    public function testIfChangeComment()
    {
        $comment_content = [
            'project_id' => $this->project->id,
            'content' => 'change comment content'
        ];

        //api_tokenがHeaderに含まらないとエラー
        $response = $this->json('PUT', $this->accessCommentAPI, $comment_content, []);
        $response->assertStatus(401);

        //自分のコメントを編集できるか
        $response = $this->json('PUT', $this->accessCommentAPI, $comment_content, $this->header);

        //サーパーから返したレスポンスの形式があっているか
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'content' => $comment_content['content'],
                ],
                'version' => '1.0.0',
                'author_url' => 'https://github.com/dachoa1995'
            ]);

        //編集したコメントは保存されたか
        $comment = Comment::all()->last();
        $this->assertEquals($comment_content['content'], $comment->content);

        //存在していないコメントを編集すれば、エラー出るか
        $response = $this->json('PUT', $this->commentAPI . '/300', $comment_content, []);
        $response->assertStatus(404);
    }

    /*
     * コメントを削除の周りのテストです
     * api_tokenがHeaderに含まらないとエラー出るか
     * 自分のコメントを削除できるか
     * サーパーから返したレスポンスの形式があっているか
     * コメントはデーターベースで削除されたか
     * 存在していないコメントを削除できるか
    */
    public function testIfDeleteComment()
    {
        $query = [
            'project_id' => $this->project->id,
        ];

        //api_tokenがHeaderに含まらないとエラー
        $response = $this->json('DELETE', $this->accessCommentAPI, $query, []);
        $response->assertStatus(401);

        //自分のコメントを削除できるか
        $response = $this->json('DELETE', $this->accessCommentAPI, $query, $this->header);

        //サーパーから返したレスポンスの形式があっているか
        $response->assertStatus(204);

        //コメントはデーターベースで削除されたか
        $comment = Comment::all()->last();
        $this->assertEquals(is_null($comment), true);

        //存在していないコメントを削除できるか
        $response = $this->json('DELETE', $this->commentAPI . '/300', $query, []);
        $response->assertStatus(404);
    }

    /*
     * タスクでのコメント一覧を取得
     * api_tokenがHeaderに含まらないとエラー出るか
     * コメント一覧を取得できるか
     * サーパーから返したレスポンスの形式があっているか
     */
    public function testIfAccessListComment()
    {
        $query = [
            'project_id' => $this->project->id,
            'task_id' => $this->task->id,
        ];
        //api_tokenがHeaderに含まらないとエラー
        $response = $this->json('GET', $this->commentAPI, $query, []);
        $response->assertStatus(401);

        //もう一つコメントする
        //テストコメントを作成
        $comment2 = new Comment();
        $comment2->user_id = $this->user->id;
        $comment2->task_id = $this->task->id;
        $comment2->content = 'これはコメントの内容';
        $comment2->save();

        //コメント一覧を取得できるか
        $response = $this->json('GET', $this->commentAPI, $query, $this->header);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'user_id' => $this->user->id,
                        'task_id' => $this->task->id,
                        'content' => $this->comment->content
                    ],
                    [
                        'user_id' => $this->user->id,
                        'task_id' => $this->task->id,
                        'content' => $comment2->content
                    ]
                ],
                'links' => [
                    'first' => 'http://localhost/api/comments?page=1',
                    'last' => 'http://localhost/api/comments?page=1',
                    'prev' => null,
                    'next' => null
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => 'http://localhost/api/comments',
                    'per_page' => 10
                ]
            ]);
    }

    /*
     * 権限がないアクセスの周りのテストです。
     * 権限がないタスクでコメントできるか
     * 他の人のコメントを修正できるか
     * 他の人のコメントを削除できるか
     */
    public function testIfAccessWithoutAuth()
    {
        //権限がないタスクでコメントできるか
        $another_user = factory(User::class)->create();
        $header = [
            'Authorization' => 'Bearer ' . $another_user->api_token,
            'Accept' => 'application/json',
        ];
        $query = [
            'project_id' => $this->project->id,
            'task_id' => $this->task->id,
            'content' => 'new comment content'
        ];
        $response = $this->json('POST', $this->commentAPI, $query, $header);
        $response->assertStatus(403);

        // another_userをプロジェクトのアクセス権限をあげる
        //プロジェクトとユーザーの関係を作成
        $project_user = new ProjectsUsers();
        $project_user->user_id = $another_user->id;
        $project_user->project_id = $this->project->id;
        $project_user->save();

        //他の人のコメントを修正できるか
        $query['content'] = 'change comment content';

        $response = $this->json('PUT', $this->accessCommentAPI, $query, []);
        $response->assertStatus(403);

        //他の人のコメントを削除できるか
        $response = $this->json('DELETE', $this->accessCommentAPI, $query, []);
        $response->assertStatus(403);
    }
}
