@extends('layouts.app')

@section('title', 'URL Shortener - Demo Project')

@section('content')
<div style="display: flex; justify-content: center; align-items: center; min-height: 80vh; text-align: center; font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div>
        <h1 style="color: #fff; margin-bottom: 30px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">URL Shortener</h1>
        <div style="background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); padding: 40px; border-radius: 20px; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37); border: 1px solid rgba(255, 255, 255, 0.18); max-width: 500px;">
            <h2 style="color: #fff; margin-bottom: 20px; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">Demo Project</h2>
            <p style="color: rgba(255, 255, 255, 0.9); font-size: 16px; line-height: 1.6; margin-bottom: 30px; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                This is a demonstration project showcasing a URL shortening service built with Laravel.
            </p>
            <div style="margin-top: 30px;">
                <a href="/api/documentation"
                   style="display: inline-block; background: rgba(0, 123, 255, 0.8); backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); color: white; padding: 12px 24px; text-decoration: none; border-radius: 10px; font-weight: bold; transition: all 0.3s; margin-bottom: 15px; border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 4px 15px 0 rgba(0, 123, 255, 0.3);">
                    API Documentation
                </a>
                <br>
                <a href="https://github.com/laravel/laravel"
                   target="_blank"
                   style="display: inline-block; background: rgba(51, 51, 51, 0.8); backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); color: white; padding: 12px 24px; text-decoration: none; border-radius: 10px; font-weight: bold; transition: all 0.3s; border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 4px 15px 0 rgba(51, 51, 51, 0.3);">
                    GitHub Repository
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
