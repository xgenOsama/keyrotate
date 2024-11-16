@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Posts') }}</h5>
                    <a href="{{ route('posts.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> Create New Post
                    </a>
                </div>
                <div class="card-body">
                    @foreach ($posts as $post)
                    <div class="post-item mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white d-flex align-items-center">
                                <h5 class="mb-0 me-2">
                                    <i class="bi bi-file-earmark-text"></i> {{ $post->title }}
                                </h5>
                                <span class="badge bg-light text-primary ms-auto">
                                    <i class="bi bi-person-fill"></i> {{ $post->user->name }}
                                </span>
                            </div>
                            <div class="card-body">
                                <p class="card-text text-muted">{{ Str::limit($post->content, 150, '...') }}</p>

                                <!-- Display the image -->
                                @if ($post->file)
                                    <div class="post-image text-center mb-3">
                                        <img src="{{ route('posts.serveImage', ['post' => $post->id]) }}" 
                                             alt="Image for {{ $post->title }}" 
                                             class="img-fluid rounded shadow-sm" 
                                             style="max-width: 250px; height: auto;">
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer text-muted d-flex justify-content-between align-items-center">
                                <small>Posted on {{ $post->created_at->format('M d, Y') }}</small>
                                <a href="/posts/edit/{{$post->id}}" class="btn btn-outline-warning btn-sm">
                                    <i class="bi bi-arrow-right-circle"></i> Edit
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <div class="d-flex justify-content-center mt-4">
                        {{ $posts->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
