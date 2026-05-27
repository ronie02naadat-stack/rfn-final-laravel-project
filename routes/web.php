<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DegreeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
// TEMPORARY FALLBACK – WILL SHOW DATABASE SCHEMA
Route::fallback(function () {
    $degreesColumns = Schema::getColumnListing('degrees');
    $coursesColumns = Schema::getColumnListing('courses');
    return response()->json([
        'degrees_columns' => $degreesColumns,
        'courses_columns' => $coursesColumns,
    ]);
});

Route::get('/check-schema', function () {
    // Get columns of degrees table
    $degreesColumns = Schema::getColumnListing('degrees');
    // Get columns of courses table
    $coursesColumns = Schema::getColumnListing('courses');
    
    return response()->json([
        'degrees_table_columns' => $degreesColumns,
        'courses_table_columns' => $coursesColumns,
    ]);
});
use App\Models\Degree;
use App\Models\Course;

Route::get('/seed-data', function () {
    // 1. Create degrees with correct column names
    $bsit = Degree::updateOrCreate(
        ['degree_title' => 'Bachelor of Science in Information Technology'],
        [
            'degree_title' => 'Bachelor of Science in Information Technology',
            'degree_code' => 'BSIT',
            'description' => 'Focuses on web development, networking, and database management.'
        ]
    );
    
    $cs = Degree::updateOrCreate(
        ['degree_title' => 'Bachelor of Science in Computer Science'],
        [
            'degree_title' => 'Bachelor of Science in Computer Science',
            'degree_code' => 'BSCS',
            'description' => 'Focuses on algorithms, programming paradigms, and software development.'
        ]
    );
    
    $is = Degree::updateOrCreate(
        ['degree_title' => 'Bachelor of Science in Information Systems'],
        [
            'degree_title' => 'Bachelor of Science in Information Systems',
            'degree_code' => 'BSIS',
            'description' => 'Focuses on business processes, IT project management, and systems analysis.'
        ]
    );

    // 2. Create courses for BSIT (using correct column names)
    $bsitCourses = [
        [
            'course_name' => 'Web Development',
            'code' => 'IT 301',
            'description' => 'HTML, CSS, JavaScript, Laravel framework',
            'degree_id' => $bsit->id
        ],
        [
            'course_name' => 'Database Management',
            'code' => 'IT 202',
            'description' => 'MySQL, SQL queries, database design and normalization',
            'degree_id' => $bsit->id
        ],
        [
            'course_name' => 'Networking',
            'code' => 'IT 203',
            'description' => 'Network fundamentals, OSI model, routing and switching',
            'degree_id' => $bsit->id
        ],
        [
            'course_name' => 'Object-Oriented Programming',
            'code' => 'IT 104',
            'description' => 'Java, OOP concepts, inheritance, polymorphism',
            'degree_id' => $bsit->id
        ],
    ];
    
    foreach ($bsitCourses as $course) {
        Course::updateOrCreate(
            ['course_name' => $course['course_name'], 'degree_id' => $course['degree_id']],
            $course
        );
    }

    // 3. Create courses for BSCS
    $csCourses = [
        [
            'course_name' => 'Data Structures',
            'code' => 'CS 301',
            'description' => 'Algorithms, trees, graphs, hash tables',
            'degree_id' => $cs->id
        ],
        [
            'course_name' => 'Theory of Computation',
            'code' => 'CS 401',
            'description' => 'Automata theory, complexity classes, Turing machines',
            'degree_id' => $cs->id
        ],
        [
            'course_name' => 'Artificial Intelligence',
            'code' => 'CS 402',
            'description' => 'Machine learning basics, neural networks, NLP',
            'degree_id' => $cs->id
        ],
    ];
    
    foreach ($csCourses as $course) {
        Course::updateOrCreate(
            ['course_name' => $course['course_name'], 'degree_id' => $course['degree_id']],
            $course
        );
    }

    // 4. Create courses for BSIS
    $isCourses = [
        [
            'course_name' => 'Business Process Management',
            'code' => 'IS 301',
            'description' => 'Workflow optimization, BPMN, process mining',
            'degree_id' => $is->id
        ],
        [
            'course_name' => 'Enterprise Architecture',
            'code' => 'IS 302',
            'description' => 'IT strategy, TOGAF, Zachman framework',
            'degree_id' => $is->id
        ],
        [
            'course_name' => 'Systems Analysis',
            'code' => 'IS 203',
            'description' => 'Requirement gathering, UML, SDLC',
            'degree_id' => $is->id
        ],
    ];
    
    foreach ($isCourses as $course) {
        Course::updateOrCreate(
            ['course_name' => $course['course_name'], 'degree_id' => $course['degree_id']],
            $course
        );
    }

    return response()->json([
        'message' => 'Degrees and courses seeded successfully!',
        'degrees' => Degree::with('courses')->get(['id', 'degree_title', 'degree_code']),
    ]);
});
Route::get('/admin/dashboard', [App\Http\Controllers\AdminDashboardController::class, 'index'])
    ->name('admin.dashboard')
    ->middleware('auth');

Route::get('/fix-admin-role', function () {
    // Get the admin user
    $admin = User::where('email', 'admin@example.com')->first();
    
    if (!$admin) {
        return "Admin user not found. Please create one first using /create-admin";
    }
    
    // Show current attributes (excluding password for security)
    $data = $admin->toArray();
    unset($data['password']);
    
    // Determine which columns exist in the users table
    $columns = DB::getSchemaBuilder()->getColumnListing('users');
    
    // Common role column names
    $roleColumns = ['role', 'user_role', 'is_admin', 'user_type', 'type', 'level'];
    $existingRoleCols = array_intersect($roleColumns, $columns);
    
    // Update role if needed (assuming 'role' column exists; change based on your schema)
    if (in_array('role', $columns)) {
        $admin->role = 'admin';
        $admin->save();
        $updated = "Updated role column to 'admin'.";
    } elseif (in_array('is_admin', $columns)) {
        $admin->is_admin = true;
        $admin->save();
        $updated = "Updated is_admin to true.";
    } elseif (in_array('user_type', $columns)) {
        $admin->user_type = 'admin';
        $admin->save();
        $updated = "Updated user_type to 'admin'.";
    } else {
        $updated = "No recognized role column found. Available columns: " . implode(', ', $columns);
    }
    
    // Fetch updated admin
    $updatedAdmin = User::where('email', 'admin@example.com')->first();
    $updatedData = $updatedAdmin->toArray();
    unset($updatedData['password']);
    
    return response()->json([
        'original_admin_data' => $data,
        'available_columns' => $columns,
        'action_taken' => $updated,
        'updated_admin_data' => $updatedData,
        'suggested_login_credentials' => ['email' => 'admin@example.com', 'password' => 'admin123']
    ]);
});

// Temporary admin creator – remove after use
Route::get('/create-admin', function () {
    try {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
                // Add any extra fields your users table has, e.g.:
                // 'is_admin' => true,
                // 'email_verified_at' => now(),
            ]
        );
        return '✅ Admin user created successfully.<br>Email: admin@example.com<br>Password: admin123<br><a href="/login">Go to Login</a>';
    } catch (\Exception $e) {
        return '❌ Error: ' . $e->getMessage();
    }
});

Route::get('/setup-admin', function () {
    try {
        // Test database connection
        DB::connection()->getPdo();
        $dbConnected = 'Database connected successfully.';
    } catch (\Exception $e) {
        return 'Database connection error: ' . $e->getMessage();
    }

    // Check if users table exists and count users
    try {
        $userCount = User::count();
        $users = User::all(['id', 'name', 'email']);
    } catch (\Exception $e) {
        return 'Users table error: ' . $e->getMessage();
    }

    // Create or update admin user
    try {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
            ]
        );
        $adminCreated = 'Admin user created/updated successfully.';
    } catch (\Exception $e) {
        $adminCreated = 'Error creating admin: ' . $e->getMessage();
    }

    return response()->json([
        'db_connection' => $dbConnected,
        'user_count' => $userCount,
        'users' => $users,
        'admin_action' => $adminCreated,
        'admin_email' => 'admin@example.com',
        'admin_password' => 'admin123'
    ]);
});

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->user_type === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->user_type === 'teacher') {
            return redirect()->route('teacher.dashboard');
        } else {
            return redirect()->route('student.dashboard');
        }
    }
    return redirect('/login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('/password/verify', [AuthController::class, 'verifyPassword'])->name('password.verify');
    Route::post('/password/update', [AuthController::class, 'updatePassword'])->name('password.update');

    // Password change routes for first-time login
    Route::get('/change-password', [PasswordChangeController::class, 'show'])->name('password.change.show');
    Route::post('/change-password', [PasswordChangeController::class, 'update'])->name('password.change.update');

    // Dashboard routes with role-based access
    Route::middleware('check-user-type:student')->get('/student-dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    Route::middleware('check-user-type:teacher')->get('/teacher-dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
    
    Route::middleware('check-user-type:admin')->group(function () {
        Route::get('/admin-dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/latest-counts', [AdminDashboardController::class, 'getLatestCounts'])->name('admin.latest-counts');
        Route::get('/admin/user/{id}', [AdminDashboardController::class, 'getUserData'])->name('admin.user-data');
        Route::get('/admin/create', [AdminDashboardController::class, 'create'])->name('admin.create');
        Route::post('/admin/store', [AdminDashboardController::class, 'store'])->name('admin.store');
        Route::get('/admin/edit/{id}', [AdminDashboardController::class, 'edit'])->name('admin.edit');
        Route::put('/admin/update/{id}', [AdminDashboardController::class, 'update'])->name('admin.update');
        Route::delete('/admin/users/{id}', [AdminDashboardController::class, 'destroy'])->name('admin.destroy');
    });
});

// Student routes - with maintenance mode check (only index/show - management via admin dashboard)
Route::middleware('check-maintenance-mode')->group(function () {
    Route::resource('students', StudentController::class, ['only' => ['index', 'show']]);
});

// Other resource routes - without maintenance redirect (show promotion instead)
Route::resource('degrees', DegreeController::class);
Route::resource('profiles', ProfileController::class);
Route::resource('posts', PostController::class);
Route::resource('courses', CourseController::class);

// Custom course routes
Route::get('/courses-by-degree', [CourseController::class, 'byDegree'])->name('courses.by-degree');

// Log routes - without maintenance redirect
Route::prefix('logs')->name('logs.')->controller(LogController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/clear', 'clear')->name('clear');
    Route::get('/download', 'download')->name('download');
});

// Maintenance mode routes
Route::get('/maintenance-mode', [MaintenanceController::class, 'show'])->name('maintenance.show');

Route::prefix('maintenance')->name('maintenance.')->middleware('auth')->controller(MaintenanceController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::post('/{id}/activate', 'activate')->name('activate');
    Route::post('/{id}/deactivate', 'deactivate')->name('deactivate');
    Route::delete('/{id}', 'destroy')->name('destroy');
});
