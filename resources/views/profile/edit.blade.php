@extends('layouts.app')

@section('title', 'Edit Profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}">
@endpush

@section('content')
<div class="edit-profile-container">
    <h2 class="page-title">Edit Profile</h2>
    
    <form method="POST" action="{{ route('profile.update') }}" class="edit-profile-form">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control" 
                   value="{{ $userInfo['username'] ?? '' }}" placeholder="Enter username">
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" 
                           value="{{ $userInfo['first_name'] ?? '' }}" placeholder="First name">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" class="form-control" 
                           value="{{ $userInfo['middle_name'] ?? '' }}" placeholder="Middle name">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" 
                           value="{{ $userInfo['last_name'] ?? '' }}" placeholder="Last name">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="suffix">Suffix</label>
                    <input type="text" id="suffix" name="suffix" class="form-control" 
                           value="{{ $userInfo['suffix'] ?? '' }}" placeholder="Suffix">
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" class="form-control" rows="4" 
                      placeholder="Tell us about yourself...">{{ $userInfo['bio'] ?? '' }}</textarea>
        </div>
        
        <div class="form-actions">
            <a href="{{ route('profile.show') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
