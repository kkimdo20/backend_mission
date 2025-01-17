<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\LikeManager;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ArticleSaveRequest;

class ArticleController extends Controller
{   
    
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $articles = Article::orderBy('id', 'desc')->get();
        $likedCheck = LikeManager::get();

        return view('articles.list', [
            'articles' => $articles,
            'likedCheck' => $likedCheck

        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('articles.save', [
            'pageMode' => 'write',
            'article' => new Article()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ArticleSaveRequest $request)
    {   
        $you = auth()->user();
        $validated = $request->validated();
        $article = new Article();
        $article->user_id = $you->id;
        $article->title = $validated['title'];
        $article->body = $validated['body'];

        if ($request->hasFile('img_1')) {
            $article->img_1 = $request->file('img_1')->store('article/' . date('Y/m/d'), 'public');
        }

        $article->save();

        return redirect()->route('articles.show', $article->id)->with('success', "{$article->id}번 게시물이 작성되었습니다.");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @param  \App\Models\LikeManager  $likeManager
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {   

        $you = auth()->user(); 
        $likesCheck = LikeManager::where(['user_id'=>$you->id, 'articles_id'=>$article->id, 'like_check'=>1])->get();
        if($likesCheck == null){
            $likeCheck = 0;
        }
        else{
            $likeCheck = 1;
        }
        return view('articles.detail', compact('you','likeCheck'),[
            'article' => $article
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function edit(Article $article)
    {   
        $you = auth()->user(); 
        $likedCheck = LikeManager::where(['user_id'=>$you->id, 'articles_id'=>$article->id])->first();
        if($likedCheck != null){

        }
        return view('articles.save', [
            'pageMode' => 'edit',
            'article' => $article
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(ArticleSaveRequest $request, Article $article)
    {
        $validated = $request->validated();

        $article->title = $validated['title'];
        $article->body = $validated['body'];

        if ($request->hasFile('img_1')) {
            if ($article->img_1) {
                Storage::disk('public')->delete($article->img_1);
            }

            $article->img_1 = $request->file('img_1')->store('article/' . date('Y/m/d'), 'public');
        }

        $article->save();

        return redirect()->route('articles.show', $article->id)->with('success', "{$article->id}번 게시물을 수정하였습니다.");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        $id = $article->id;

        if ($article->img_1) {
            Storage::disk('public')->delete($article->img_1);
        }

        $article->delete();

        return redirect()->route('articles.index')->with('success', "{$id}번 게시물을 삭제하였습니다.");
    }
}
