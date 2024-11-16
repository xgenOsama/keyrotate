<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tenant;
use App\Services\EaasService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

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
            'file' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048', // Adjust file types and size as needed
        ]);
        $user = auth()->user();
        $file = $request->file('file');
        $path = "";
        if(isset($file)){
            $eaasService = new EaasService();
            $extension = $file->getClientOriginalExtension();
            $encryptedContent = $eaasService->encryptFile($file->getPathname());
            // Save the encrypted content with a unique name
            $encryptedFileName = 'encrypted/' . uniqid() . '.enc'.".".$extension;
            $path = $encryptedFileName;
            Storage::disk('public')->put($encryptedFileName, $encryptedContent);
        }
        $post = new Post([
            'title' => $request->title,
            'content' => $request->content,
            'tenant_id' => $user->tenant_id,
            "file" => $path
        ]);
        $user->posts()->save($post);
        return redirect()->route('posts');
    }


    public function serveImage(Post $post)
    {
        // Ensure the encrypted file exists
        if (!Storage::disk('public')->exists($post->file)) {
            abort(404, 'File not found');
        }
        // Fetch the encrypted file content
        $encryptedFilePath = storage_path('app/public/' . $post->file);
        // Decrypt the file content
        try {
            $eaasService = new EaasService();
            $keyVersion = $post->encryption_key_version;
            $decryptedContent = $eaasService->decryptFile($encryptedFilePath, $keyVersion);
        } catch (Exception $e) {
            abort(500, 'Decryption failed: ' . $e->getMessage());
        }
        // Determine the mime type of the decrypted content
        $mimeType = mime_content_type($encryptedFilePath);
        // Return the decrypted content as a response
        return response($decryptedContent, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($post->file) . '"');
    }
}
