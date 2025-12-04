<?php

use App\Http\Controllers\EditorController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\PaperController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VerificationController;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\JoinedEvent;
use App\Http\Middleware\LogUserAgent;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CameraReadyController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UserEventController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReviewerController;
use App\Http\Controllers\SupportingMaterialsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganizerController;

// Auth::routes(['verify' => true]);

// Email Verification
Route::middleware(['auth'])->group(function () {
    Route::controller(VerificationController::class)->group(function () {
        Route::get('email/verify', 'show')->name('verification.notice');
        Route::get('email/verify/{id}/{hash}', 'verify')->name('verification.verify');
        Route::post('email/verification-notification', 'send')->name('verification.send');
    });
});

// authenticated users
Route::middleware(['auth', EnsureEmailIsVerified::class])->group(function () {

    //landing page
    Route::controller(DashboardController::class)->group(function () {
        Route::get('dashboard', 'index')->name('dashboard');
        Route::get('dashboard/event/{event}', 'event')->name('dashboard.event')->middleware(JoinedEvent::class);
        Route::post('dashboard/event/{event}/change-role', 'change_role')->name('dashboard.event.change-role')->middleware(JoinedEvent::class);
    });

    Route::get('/', function () {
        return redirect('/dashboard');
    });

    //Events
    Route::controller(EventController::class)->group(function () {
        Route::get('events', 'index')->name('events');
        Route::get('events/create', 'create')->name('events.create')->middleware([RoleMiddleware::class . ':admin']);
        Route::post('events', 'store')->name('events.store')->middleware([RoleMiddleware::class . ':admin']);
        Route::get('events/{event}', 'show')->name('events.show');
        Route::get('events/{event}/edit', 'edit')->name('events.edit')->middleware([RoleMiddleware::class . ':admin']);
        Route::put('events/{event}', 'update')->name('events.update')->middleware([RoleMiddleware::class . ':admin']);
        Route::delete('events/{event}', 'destroy')->name('events.destroy')->middleware([RoleMiddleware::class . ':admin']);
        Route::post('events/j/{event}', 'join')->name('events.join');
        Route::get('e/{event}', 'join')->name('events.join.byurl');
    });

    Route::controller(CameraReadyController::class)->group(function () {
        Route::get('{event}/camera-ready', 'index')->name('index.camera-ready')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
        Route::get('{event}/camera-ready/upload/{paper_id}', 'page_upload')->name('page_upload.camera-ready')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
        Route::post('{event}/camera-ready/upload/{paper_id}/submit', 'upload')->name('upload.camera-ready')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
        Route::get('{event}/camera-ready/download/{paper_id}', 'download')->name('download.camera-ready')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
    });


    Route::controller(PaperController::class)->group(function () {
        Route::get('{event}/papers', 'index')->name('index.paper')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
        Route::get('{event}/submit-paper', 'index_submit')->name('index.submit.paper')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
        Route::post('{event}/submit-paper', 'submit_paper')->name('submit.paper')->middleware(JoinedEvent::class);
        Route::get('{event}/check-paper/{paper_id}', 'check')->name('check.paper')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
        Route::get('{event}/paper/{paper_id}/edit', 'index_edit')->name('edit.paper')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
        Route::put('{event}/paper/{paper_id}', 'update')->name('update.paper')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
        Route::get('{event}/revision-paper/{paper_id}', 'index_revision')->name('index.revise.paper')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
        Route::post('{event}/revision-paper/{paper_id}', 'revision_paper')->name('revision.paper')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
        Route::get('{event}/review/{paper_id}', 'index_review')->name('index.review.paper')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
        Route::get('{event}/paper/file-review/{review_id}', 'file_review')->name('paper.file.review')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
        Route::get('{event}/paper/detail/{paper_id}', 'detail')->name('paper.detail')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
    });

    Route::controller(SupportingMaterialsController::class)->group(function () {
        Route::get('{event}/supporting-materials', 'index')->name('index.supporting-materials')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
        Route::get('{event}/supporting-materials/upload/{paper_id}', 'page_upload')->name('page_upload.supporting-materials')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
        Route::post('{event}/supporting-materials/upload/{paper_id}/submit', 'upload')->name('upload.supporting-materials')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Presenter');
    });

    Route::controller(UserEventController::class)->group(function () {
        Route::get('events/{event}/{user}', 'edit')->name('users.events.edit')->middleware(JoinedEvent::class)->middleware([RoleMiddleware::class . ':admin']);
        Route::put('events/{event}/{user}', 'update')->name('users.events.update')->middleware(JoinedEvent::class)->middleware([RoleMiddleware::class . ':admin']);
    });

    Route::controller(EditorController::class)->group(function () {
        Route::post('{event}/editor/assign-reviewer/{paper}', 'assign_reviewer')->name('assign.reviewer')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Editor');
        Route::get('{event}/editor/desk-evaluation', 'index_desk_evaluation')->name('index.desk.evaluation')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Editor');
        Route::get('{event}/editor/evaluate/{paper}', 'view_paper')->name('view.paper')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Editor');
        Route::put('{event}/editor/evaluate/{paper}', 'decline_paper')->name('decline.paper')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Editor');
        Route::get('{event}/editor/reviewer-assignment', 'index_assign_reviewer')->name('index.assign.reviewer')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Editor');
        Route::get('{event}/editor/editor-decision', 'index_editor_decision')->name('index.editor.decision')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Editor');
        Route::get('{event}/editor/final-paper', 'index_final_paper')->name('index.final.paper')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Editor');
        Route::get('{event}/editor/create-decision/{paper}', 'index_create_decision')->name('index.create.decision')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Editor');
        Route::post('{event}/editor/create-decision/{paper}', 'create_decision')->name('create.decision')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Editor');
        Route::get('{event}/editor/detail-paper/{paper}', 'detail_paper')->name('detail.paper')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Editor');
        Route::get('{event}/editor/final-paper/detail/{paper}', 'detail_final')->name('detail.final')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Editor');
        Route::get('{event}/editor/check/{paper_id}', 'check')->name('editor.check')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Editor');
        Route::get('{event}/editor/file-review/{review_id}', 'file_review')->name('editor.file.review')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Editor');
        Route::get('{event}/editor/revision-paper', 'index_revision_paper')->name('index.revision.paper')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Editor');
        Route::get('{event}/editor/revision-paper/{paper}', 'view_revision_paper')->name('view.revision.paper')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Editor');
        Route::post('{event}/editor/send-loa-bulk', 'sendLoABulk')->name('editor.send-loa-bulk')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Organizer');
    });

    Route::controller(ReviewerController::class)->group(function () {
        Route::get('{event}/reviewer', 'index')->name('events.reviewer')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Paper Reviewer');
        Route::get('{event}/reviewer/check/{paper_id}', 'check')->name('events.reviewer.check')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Paper Reviewer');
        Route::get('{event}/reviewer/review/{paper_id}', 'review')->name('events.reviewer.review')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Paper Reviewer');
        Route::post('{event}/reviewer/review/{paper_id}/submit', 'submit_review')->name('review.submit')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Paper Reviewer');
        Route::get('{event}/reviewer/view/{paper_id}', [ReviewerController::class, 'view_review'])->name('events.reviewer.view')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Paper Reviewer');
        Route::get('{event}/reviewer/download/{paper_id}', [ReviewerController::class, 'download_review_file'])->name('events.reviewer.download')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Paper Reviewer');
        // Route::get('{event}/reviewer/history/{paper_id}', 'history')->name('events.reviewer.history')->middleware(JoinedEvent::class);
        Route::get('{event}/reviewer/history', [ReviewerController::class, 'history'])->name('events.reviewer.history')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class . ':Paper Reviewer');
        // Route::post('assign-reviewer', 'assign_reviewer')->name('assign.reviewer');
    });

    Route::controller(UserController::class)->group(function () {
        Route::get('users', 'index')->name('users.index')->middleware([RoleMiddleware::class . ':admin']);
        Route::get('edit-profile', 'edit_profile')->name('users.edit-profile');
        Route::get('users/create', 'create')->name('users.create')->middleware([RoleMiddleware::class . ':admin']);
        Route::post('users', 'store')->name('users.store')->middleware([RoleMiddleware::class . ':admin']);
        Route::get('users/{user}', 'show')->name('users.show')->middleware([RoleMiddleware::class . ':admin']);
        Route::get('users/{user}/edit', 'edit')->name('users.edit')->middleware([RoleMiddleware::class . ':admin']);
        Route::put('users/{user}', 'update')->name('users.update')->middleware([RoleMiddleware::class . ':admin']);
        Route::put('user-profile/{user}', 'update_profile')->name('users.update-profile');
        Route::delete('users/{user}', 'destroy')->name('users.destroy')->middleware([RoleMiddleware::class . ':admin']);
        Route::post('users/{user}/change-status', 'change_status')->name('users.change_status')->middleware([RoleMiddleware::class . ':admin']);
    });

    Route::controller(OrganizerController::class)->group(function () {
        Route::get('{event}/event-management', 'event_index')->name('organizer.event')->middleware(JoinedEvent::class)->middleware([RoleMiddleware::class . ':Organizer']);
        Route::get('{event}/users-management', 'users_index')->name('organizer.users')->middleware(JoinedEvent::class)->middleware([RoleMiddleware::class . ':Organizer']);
        Route::put('{event}/update-event', 'update_event')->name('organizer.update_event')->middleware(JoinedEvent::class)->middleware([RoleMiddleware::class . ':Organizer']);
        Route::get('{event}/payment', 'payment_index')->name('organizer.payment')->middleware(JoinedEvent::class)->middleware([RoleMiddleware::class . ':Organizer']);
        Route::get('{event}/payment-settings', 'payment_set_index')->name('organizer.payment-settings')->middleware(JoinedEvent::class)->middleware([RoleMiddleware::class . ':Organizer']);
        Route::post('{event}/payment-settings', 'payment_set_store')->name('organizer.payment-settings-store')->middleware(JoinedEvent::class)->middleware([RoleMiddleware::class . ':Organizer']);
        Route::put('{event}/payment/{payment_id}', 'payment_update')->name('organizer.payment_update')->middleware(JoinedEvent::class)->middleware([RoleMiddleware::class . ':Organizer']);
        // Route::put('{event}/payment-np/{user_id}', 'payment_update_np')->name('organizer.payment_update.np')->middleware(JoinedEvent::class)->middleware([RoleMiddleware::class . ':Organizer']);
        Route::get('{event}/users-management/{user}', 'edit_role_index')->name('organizer.users.edit')->middleware(JoinedEvent::class)->middleware([RoleMiddleware::class . ':Organizer']);
        Route::put('{event}/users-management/{user}', 'update_role')->name('organizer.users.update')->middleware(JoinedEvent::class)->middleware([RoleMiddleware::class . ':Organizer']);
        Route::get('{event}/loa/{paper}', 'send_loa')->name('organizer.send.loa')->middleware(JoinedEvent::class)->middleware([RoleMiddleware::class . ':Organizer']);
    });

    Route::controller(PaymentController::class)->group(function () {
        Route::get('{event}/my-payments', 'index')->name('index.my-payments')->middleware(JoinedEvent::class);
        Route::get('{event}/upload-receipt/{paper}', 'index_receipt')->name('index.receipt')->middleware(JoinedEvent::class);
        // Route::post('{event}/upload-receipt', 'upload_receipt')->name('upload.receipt')->middleware(JoinedEvent::class);
        Route::post('{event}/apply-payment-info/{paper}', 'apply_payment_info')->name('upload.apply_payment_info')->middleware(JoinedEvent::class);
        Route::post('{event}/upload-payment-proof/{payment_id}', 'payment_proof')->name('upload.payment_proof')->middleware(JoinedEvent::class);
        // Route::post('{event}/upload-receipt-np', 'upload_receipt_np')->name('upload.receipt.np')->middleware(JoinedEvent::class);
        Route::post('{event}/download-receipt/{payment_id}', 'download_receipt')->name('download.receipt')->middleware(JoinedEvent::class);
        // Route::post('{event}/download-receipt-np/{user}', 'download_receipt_np')->name('download.receipt.np')->middleware(JoinedEvent::class);
        // Route::get('{event}/my-payments/{payment_id}', 'show')->name('show.my-payments')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class.':Presenter');
        // Route::get('{event}/my-payments/{payment_id}/download', 'download')->name('download.my-payments')->middleware(JoinedEvent::class)->middleware(RoleMiddleware::class.':Presenter');
    });
});


// Auth

Route::controller(AuthController::class)->group(function () {
    Route::match(['get', 'post'], 'logout', 'logout')->name('logout')->middleware(LogUserAgent::class);
});

Route::middleware(['guest'])->group(function () {
    Route::controller(ForgotPasswordController::class)->group(function () {
        Route::get('forgot-password', 'forgot_password')->name('forgot_password.get');
        Route::post('forgot-password', 'send_reset_link')->name('forgot_password.send');
        Route::get('reset-password/{token}', 'reset_password')->name('password.reset');
        Route::post('reset-password', 'update_password')->name('password.update');
    });

    Route::controller(AuthController::class)->group(function () {
        Route::middleware(LogUserAgent::class)->group(function () {
            Route::post('login', 'authenticate')->name('authenticate');
        });

        Route::get('login', 'login')->name('login');

        Route::get('register', 'register')->name('register');
        Route::post('register', 'create_user')->name('create_user');
    });
});