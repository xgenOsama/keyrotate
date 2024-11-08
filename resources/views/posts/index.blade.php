@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Posts') }}</h5>
                    <a href="{{ route('posts.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> Create New Post
                    </a>
                </div>
                <div class="card-body">
                    @foreach ($posts as $post)
                    <div class="col-md-12 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-0 me-2">
                                        <i class="bi bi-file-earmark-text"></i> {{ $post->title }}
                                    </h5>
                                    <span class="badge bg-light text-primary ms-auto">
                                        <i class="bi bi-person-fill"></i> {{ $post->user->name }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="card-text">{{ Str::limit($post->content, 150, '...') }}</p>
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
                <div class="row">
                        <!-- Pagination Links -->
        <div class="d-flex justify-content-center mt-4">
            {{ $posts->links('pagination::bootstrap-5') }}
        </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
