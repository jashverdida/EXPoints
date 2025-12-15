@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
@endpush

@section('content')
<div class="admin-dashboard">
    <h1 class="page-title">Admin Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <i class="bi bi-people"></i>
            <div class="stat-content">
                <span class="stat-value">{{ $totalUsers }}</span>
                <span class="stat-label">Total Users</span>
            </div>
        </div>
        <div class="stat-card">
            <i class="bi bi-file-post"></i>
            <div class="stat-content">
                <span class="stat-value">{{ $totalPosts }}</span>
                <span class="stat-label">Total Posts</span>
            </div>
        </div>
        <div class="stat-card warning">
            <i class="bi bi-person-x"></i>
            <div class="stat-content">
                <span class="stat-value">{{ $bannedUsers }}</span>
                <span class="stat-label">Banned Users</span>
            </div>
        </div>
        <div class="stat-card danger">
            <i class="bi bi-person-slash"></i>
            <div class="stat-content">
                <span class="stat-value">{{ $disabledUsers }}</span>
                <span class="stat-label">Disabled Users</span>
            </div>
        </div>
    </div>
    
    <div class="admin-sections">
        <div class="admin-section">
            <h3>Recent Users</h3>
            <div class="user-list">
                @foreach($recentUsers as $user)
                <div class="user-item">
                    <img src="{{ $user['profile_picture'] }}" alt="{{ $user['username'] }}" class="user-avatar">
                    <div class="user-info">
                        <span class="username">{{ $user['username'] }}</span>
                        <span class="email">{{ $user['email'] }}</span>
                    </div>
                    <span class="badge {{ $user['role'] === 'admin' ? 'badge-admin' : ($user['role'] === 'mod' ? 'badge-mod' : 'badge-user') }}">
                        {{ ucfirst($user['role']) }}
                    </span>
                </div>
                @endforeach
            </div>
            <a href="{{ route('admin.users') }}" class="btn btn-view-all">View All Users</a>
        </div>
        
        <div class="admin-section">
            <h3>Recent Posts</h3>
            <div class="post-list">
                @foreach($recentPosts as $post)
                <div class="post-item">
                    <div class="post-info">
                        <span class="post-title">{{ $post['title'] }}</span>
                        <span class="post-author">by {{ $post['username'] }}</span>
                    </div>
                    <span class="post-date">{{ \Carbon\Carbon::parse($post['created_at'])->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <div class="admin-actions">
        <a href="{{ route('admin.users') }}" class="action-card">
            <i class="bi bi-people"></i>
            <span>Manage Users</span>
        </a>
        <a href="{{ route('admin.moderators') }}" class="action-card">
            <i class="bi bi-shield-check"></i>
            <span>Manage Moderators</span>
        </a>
    </div>
</div>
@endsection
