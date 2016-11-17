<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Article;
use Auth;
use DB;

class ArticleController extends Controller
{
    public function index()
    {
    	if (!Auth::guest()){
            $role = DB::table('users')
                      ->where('name', '=', Auth::user()->name)
                      ->join('roles', 'roles.id', '=', 'users.id_roles')
                      ->select('name_roles')
                      ->get();

            if ($role[0]->name_roles == 'administrator')
            {
                $articles = DB::table('articles')
                              ->join('users', 'users.id', '=', 'articles.id_users')
                              ->select('articles.*', 'users.surname', 'users.name')
                              ->get();
            } else {
                $articles = DB::table('articles')
                              ->where('name_articles', Auth::user()->name)
                              ->select('name_articles')
                              ->get();
            }
            return view('home',['articles' => $articles]);
        } else {
            return view('home');
        }
    	
    }

    public function showArticle($id)
    {
    	$article = Article::find($id);
    	return view('article.showArticle',['article' => $article]);
    }

    public function newArticle()
    {
        $setForms = DB::table('users')->where('users.name', Auth::user()->name)
                   ->join('queue_elements_in_forms as queue', 'queue.id_user', '=', 'users.id')
                   ->join('elements', 'elements.id', '=', 'queue.id_elements')
                   ->join('forms', 'forms.id', '=', 'queue.id_forms')
                   ->select('queue.id_forms', 'queue.id_elements', 'queue.queue', 'queue.width', 'forms.name as f_name', 'elements.name as el_name', 'elements.label as el_label', 'users.id as us_id')
                   ->get();
        $forms = DB::table('queue_elements_in_forms as queue')->where('id_user', '=', $setForms[0]->us_id)
            ->join('forms', 'forms.id', '=', 'queue.id_forms')
            ->select('forms.name')
            ->distinct()->get();
    	return view('article.newArticle', ['setForms' => $setForms, 'forms' => $forms]);
    }

    public function addNewArticle(Request $request)
    {
        DB::table('articles')->insert([
            'author' => $request->input('author'),
            'title' => $request->input('title'),
            'content' => $request->input('content')
            ]);
        return redirect('/');
    }

    public function editArticle($id)
    {
        $article = Article::find($id);

        return view('article.editArticle', ['article' => $article]);
    }

    public function updateArticle(Request $request)
    {
        if ($request->input('author') != Auth::user()->name){
            DB::table('messages')->insert([
                'sender' => Auth::user()->name,
                'recipient' => $request->input('author'),
                'message_content' => "Ваша статья отредактирована пользователем ".Auth::user()->name.". Причина редактирования (".$request->input('edit_reason').")"
                ]);
        }
        DB::table('articles')->where('id', $request->input('id'))
            ->update([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'updated_at' => \Carbon\Carbon::now()
                ]);        
        return redirect('/');
    }

    public function removeArticle($id)
    {
        DB::table('articles')->where('id', $id)->delete();
        return redirect('/');
    }
}
