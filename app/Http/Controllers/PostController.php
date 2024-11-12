<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PostController extends Controller
{
    public function index(Tenant $tenant)
    {
        $user = auth()->user();
        $posts = Post::where('tenant_id', $user->tenant_id)->where('user_id',$user->id)->with('user')->orderBy('created_at','DESC')->paginate(5);
        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        return view('posts.create');
    }


    public function edit($id)
    {
        $post = Post::where('id',$id)->where('user_id',auth()->user()->id)->first();
        return view('posts.edit')->with(['post' => $post]);
    }


    public function update(Request $request,$id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        $post = Post::where('id',$id)->where('user_id',auth()->user()->id)->first();
        $post->title = $request->title;
        $post->content = $request->content;
        $post->update();
        return redirect()->route('posts');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        // Assume authenticated user
        $user = auth()->user();
        $post = new Post([
            'title' => $request->title,
            'content' => $request->content,
            'tenant_id' => $user->tenant_id,
        ]);
        $user->posts()->save($post);
        return redirect()->route('posts');
    }
}
