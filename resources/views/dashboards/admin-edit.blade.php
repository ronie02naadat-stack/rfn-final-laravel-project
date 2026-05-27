@extends('layouts.app')

@section('content')
<div style="padding: 40px 20px; max-width: 600px; margin: 0 auto;">
    <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <h1 style="font-size: 28px; color: #1f2937; margin-top: 0;">✏️ Edit User</h1>
        <p style="color: #6b7280; margin-bottom: 30px;">Update {{ $user->name }}'s account information.</p>

        @if($errors->any())
            <div style="background: #fee2e2; color: #991b1b; padding: 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ef4444;">
                <strong>❌ Validation Errors:</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div style="background: #d1fae5; color: #065f46; padding: 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #10b981;">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #667eea; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Account Type</label>
                <div style="padding: 12px 16px; background: #f3f4f6; border: 2px solid #e5e7eb; border-radius: 10px; color: #111827; font-weight: 500;">
                    @if($user->user_type === 'student')
                        👨‍🎓 Student
                    @elseif($user->user_type === 'teacher')
                        👨‍🏫 Teacher
                    @else
                        {{ ucfirst($user->user_type) }}
                    @endif
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="email" style="display: block; font-size: 13px; font-weight: 700; color: #667eea; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 15px; box-sizing: border-box;" required>
            </div>

            <div style="background: #f0f4ff; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #667eea;">
                <h3 style="margin-top: 0; font-size: 14px; color: #667eea; font-weight: 600;">📋 Name Information</h3>
                <div>
                    <label for="name" style="display: block; font-size: 13px; font-weight: 700; color: #1f2937; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 15px; box-sizing: border-box;" required>
                </div>
            </div>

            @if($user->user_type === 'student')
                <div style="background: #f0fdf4; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #10b981;">
                    <h3 style="margin-top: 0; font-size: 14px; color: #10b981; font-weight: 600;">👨‍🎓 Student Information</h3>
                    <div style="margin-bottom: 15px;">
                        <label for="degree_id" style="display: block; font-size: 13px; font-weight: 700; color: #1f2937; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Degree Program</label>
                        <select name="degree_id" style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; box-sizing: border-box;">
                            <option value="">-- Select a Degree --</option>
                            @foreach($degrees ?? [] as $degree)
                                <option value="{{ $degree->id }}" {{ old('degree_id', $student->degree_id ?? '') == $degree->id ? 'selected' : '' }}>
                                    {{ $degree->degree_title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label for="student_address" style="display: block; font-size: 13px; font-weight: 700; color: #1f2937; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Address</label>
                        <textarea name="student_address" style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; box-sizing: border-box; resize: vertical; min-height: 80px;">{{ old('student_address', $student->address ?? '') }}</textarea>
                    </div>
                    <div>
                        <label for="student_contact" style="display: block; font-size: 13px; font-weight: 700; color: #1f2937; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Contact Number</label>
                        <input type="tel" name="student_contact" value="{{ old('student_contact', $student->contact_number ?? '') }}" style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; box-sizing: border-box;">
                    </div>
                </div>
            @endif

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" style="flex: 1; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 14px 24px; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer;">✅ Save Changes</button>
                <a href="{{ route('admin.dashboard') }}" style="flex: 1; background: #e5e7eb; color: #111827; padding: 14px 24px; border-radius: 10px; font-size: 16px; font-weight: 600; text-decoration: none; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection