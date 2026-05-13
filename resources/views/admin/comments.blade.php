@extends('layouts.site', ['adminLayout' => true, 'active' => 'comments'])

@section('title', 'Admin Comment')

@section('content')
    <main class="container">
        <h1>Admin Comment</h1>
        @forelse ($comments as $comment)
            <article class="comment">
                <p><strong>{{ $comment->name }}</strong></p>
                <p class="muted">{{ $comment->created_at }}</p>
                <p><strong>Email:</strong> {{ $comment->email }}</p>
                <p>{!! nl2br(e($comment->message)) !!}</p>
            </article>
        @empty
            <p>No comments available.</p>
        @endforelse
    </main>
@endsection
