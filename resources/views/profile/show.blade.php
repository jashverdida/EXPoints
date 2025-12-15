@extends('layouts.app')

@section('title', 'Profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}">
@endpush

@section('content')
<div class="profile-container">
    <div class="profile-header">
        <div class="profile-cover"></div>
        <div class="profile-info">
            <img src="{{ $profilePicture }}" alt="{{ $displayName }}" class="profile-avatar">
            <div class="profile-details">
                <h1 class="profile-name">{{ $displayName }}</h1>
                <span class="profile-handle">{{ $handle }}</span>
                @if($bio)
                <p class="profile-bio">{{ $bio }}</p>
                @endif
            </div>
            <a href="{{ route('profile.edit') }}" class="btn btn-edit-profile">
                <i class="bi bi-pencil"></i> Edit Profile
            </a>
        </div>
    </div>

    <div class="profile-stats">
        <div class="stat-item">
            <span class="stat-value">{{ $level }}</span>
            <span class="stat-label">Level</span>
        </div>
        <div class="stat-item">
            <span class="stat-value">{{ $expPoints }}</span>
            <span class="stat-label">EXP</span>
        </div>
        <div class="stat-item">
            <span class="stat-value">{{ $totalReviews }}</span>
            <span class="stat-label">Reviews</span>
        </div>
        <div class="stat-item">
            <span class="stat-value">{{ $totalStars }}</span>
            <span class="stat-label">Stars</span>
        </div>
    </div>

    <div class="profile-exp-bar">
        <div class="exp-progress" style="width: {{ $levelProgress }}%"></div>
        <span class="exp-text">{{ $levelProgress }}% to next level</span>
    </div>

    @if(count($bestPosts) > 0)
    <div class="profile-section">
        <h3>Best Reviews</h3>
        <div class="best-posts-grid">
            @foreach($bestPosts as $post)
            <a href="{{ route('posts.show', $post['id']) }}" class="best-post-card">
                <h4>{{ $post['title'] }}</h4>
                <div class="post-stats">
                    <span><i class="bi bi-star-fill"></i> {{ $post['like_count'] }}</span>
                    <span><i class="bi bi-chat"></i> {{ $post['comment_count'] }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="profile-section">
        <h3>Member Since</h3>
        <p>{{ $startedDate }}</p>
    </div>
</div>
@endsection
