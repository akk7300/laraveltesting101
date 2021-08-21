<?php

namespace Tests\Feature;

use App\User;
use App\Article;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use WithFaker;
    /**
     * @test
     */
    public function it_allows_anyone_to_see_users_profiles()
    {
        $user = User::get()->random();

        $response = $this->get(route('show_user_profile',['id' => $user->id]));

        $response->assertViewIs('users.show');

        $response->assertViewhas('user');

        $returnedUser = $response->original->user;

        $this->assertEquals($user->id,$returnedUser->id);
    }

    /**
     * @test
     */
    public function it_prevent_non_logged_in_users_from_creating_new_articles()
    {
        $response = $this->get(route('create_new_article'));
        $response->assertRedirect('login');
    }

    /**
     * @test
     */

    public function it_allows_logged_in_users_to_create_new_articles()
    {
        $user = User::get()->random();

        $this->actingAs($user);

        $response = $this->get(route('create_new_article'));

        $response->assertViewIs('articles.create');
    }

    /**
     * @test
     */
    public function it_allows_logged_in_users_to_save_new_articles()
    {
        \Session::start();
        $user = User::get()->random();

        $data = [
            "title" => $this->faker->sentence,
     		"body" => $this->faker->paragraph,
             '_token' => csrf_token()
        ];

        $totalNumberOfArticlesBefore = Article::count();

        $response = $this->actingAs($user)->post(route('save_new_article'), $data);

        $lastInsertedArticleInTheDB = Article::orderBy('id', 'desc')->first();

        $totalNumberOfArticlesAfter = Article::count();

        $this->assertEquals($lastInsertedArticleInTheDB->title, $data['title']);

        $this->assertEquals($lastInsertedArticleInTheDB->body, $data['body']);

        $this->assertEquals($totalNumberOfArticlesBefore+1,$totalNumberOfArticlesAfter);

        $response->assertRedirect(route('view_article',['id' => $lastInsertedArticleInTheDB->id]));
    }

    /**
     * @test
     */
    public function it_allows_owner_to_edit_article()
    {
        $user = User::get()->random();

        $article = $user->articles->random();

        $response = $this->actingAs($user)->get(route('edit_article',['id' => $article->id]));

        $response->assertViewIs('articles.edit');

        $returnedArticle = $response->original->article;

        $this->assertEquals($article->id,$returnedArticle->id);

    }

    /**
     * @test
     */
    public function it_allows_owner_to_update_article()
    {
        \Session::start();

        $user = User::get()->random();

        $article = $user->articles->random();

        $totalNumberOfArticlesBefore = Article::count();

        $data = [
            "title" => $this->faker->sentence,
     		"body" => $this->faker->paragraph,
             '_token' => csrf_token()
        ];

        $response = $this->actingAs($user)->post(route('update_article',['id' => $article->id]),$data);

        $article->refresh();

        $totalNumberOfArticlesAfter = Article::count();

        $this->assertEquals($article->title, $data['title']);

        $this->assertEquals($totalNumberOfArticlesBefore,$totalNumberOfArticlesAfter);
    }

    /**
     * @test
     */
    public function it_allows_owner_of_an_article_to_delete_it()
    {
        \Session::start();

        $user = User::get()->random();

        $totalNumberOfArticlesBefore = Article::count();

        $data = [
            "title" => $this->faker->sentence,
     		"body" => $this->faker->paragraph,
             '_token' => csrf_token()
        ];

        $response = $this->actingAs($user)->post(route('save_new_article'), $data);

        $lastInsertedArticleInTheDB = Article::orderBy('id', 'desc')->first();

        $response = $this->actingAs($user)->get(route('delete_article', ['id' => $lastInsertedArticleInTheDB->id]));

        $totalNumberOfArticlesAfter = Article::count();

        $this->assertEquals($totalNumberOfArticlesBefore,$totalNumberOfArticlesAfter);
    }

    /**
     * @test
     */
    public function it_doesnt_show_edit_button_to_non_owners_of_article()
    {
        \Session::start();

        $user = User::get()->random();

        $anotherUser = User::where('id', '<>', $user->id)->get()->random();

        $data = [
            "title" => $this->faker->sentence,
     		"body" => $this->faker->paragraph,
             '_token' => csrf_token()
        ];

        $response = $this->actingAs($user)->post(route('save_new_article'), $data);

        $article = $user->articles->random();

        $response = $this->actingAs($user)->get(route('view_article', ['id' => $article->id]));

        $response->assertSeeText('Edit Article');

        $response = $this->actingAs($anotherUser)->get(route('view_article', ['id' => $article->id]));
        $response->assertDontSeeText('Edit Article');

    }

    /**
     * @test
     */
    public function a_user_could_not_visit_the_edit_page_of_an_article_he_doesnot_own()
    {
        \Session::start();

        $user = User::get()->random();

        $anotherUser =  User::where('id', '<>', $user->id)->get()->random();

        $data = [
            "title" => $this->faker->sentence,
     		"body" => $this->faker->paragraph,
             '_token' => csrf_token()
        ];

        $response = $this->actingAs($user)->post(route('save_new_article'), $data);

        $article = $user->articles->random();

        $response = $this->actingAs($anotherUser)->get(route('edit_article', ['id' => $article->id]));

        $response->assertForbidden();

    }

    /**
     * @test
     */
    public function a_user_could_not_delete_of_an_article_he_doesnot_own()
    {
        \Session::start();

        $user = User::get()->random();

        $anotherUser =  User::where('id', '<>', $user->id)->get()->random();

        $data = [
            "title" => $this->faker->sentence,
     		"body" => $this->faker->paragraph,
             '_token' => csrf_token()
        ];

        $response = $this->actingAs($user)->post(route('save_new_article'), $data);

        $article = $user->articles->random();

        $response = $this->actingAs($anotherUser)->get(route('delete_article', ['id' => $article->id]));

        $response->assertForbidden();

    }
}

