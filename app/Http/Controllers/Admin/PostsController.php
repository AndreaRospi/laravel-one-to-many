<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use function GuzzleHttp\Promise\all;
use App\Category;
use CategoriesTableSeeder;

class PostsController extends Controller
{
    protected $validationRule = [
        "title" => "required|string|max:120",
        "content" => "required",
        "published" => "sometimes|accepted",
        "category_id" => "nullable|exists:categories,id"
    ];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::all();

        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();

        return view('admin.posts.create', compact("categories"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //validazione dei dati
        $request->validate($this->validationRule);
        //creazione del post
        $data = $request->all();

        $newPost = new Post();
        $newPost->title = $data["title"];
        $newPost->content = $data["content"];
        $newPost->published = isset($data["published"]);
        $newPost->category_id = $data["category_id"];

        $slug = Str::of($newPost->title)->slug("-");
        $count = 1;

        while (Post::where("slug", $slug)->first()) {
            $slug = Str::of($newPost->title)->slug("-") . "-{$count}";
            $count++;
        }

        $newPost->slug = $slug;

        $newPost->save();
        //redirect al post 

        return redirect()->route("posts.show", $newPost->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {


        return view('admin.posts.show', compact('post'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        $categories = Category::all();

        return view("admin.posts.edit", compact("post", "categories"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $request->validate($this->validationRule);

        //aggiorno il post
        $data = $request->all();

        if ($post->title != $data['title']) {
            $post->title = $data['title'];

            $slug = Str::of($post->title)->slug("-");

            if ($slug != $post->slug) {
                $count = 1;

                while (Post::where("slug", $slug)->first()) {
                    $slug = Str::of($post->title)->slug("-") . "-{$count}";
                    $count++;
                }

                $post->slug = $slug;
            }
        }


        $post->content = $data["content"];
        $post->category_id = $data["category_id"];
        $post->published = isset($data["published"]);


        $post->save();

        return redirect()->route("posts.show", $post->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route("posts.index");
    }
}
