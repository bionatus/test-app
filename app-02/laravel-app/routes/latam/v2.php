<?php /** @noinspection DuplicatedCode */

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V2\ActivityController;
use App\Http\Controllers\Api\V2\AgentController;
use App\Http\Controllers\Api\V2\InternalNotificationController;
use App\Http\Controllers\Api\V2\Post\Comment\VoteController;
use App\Http\Controllers\Api\V2\Post\CommentController;
use App\Http\Controllers\Api\V2\Post\SolutionController;
use App\Http\Controllers\Api\V2\PostController;
use App\Http\Controllers\Api\V2\PushNotificationTokenController;
use App\Http\Controllers\Api\V2\Support\Ticket\AgentHistoryController;
use App\Http\Controllers\Api\V2\Support\Ticket\AgentRateController;
use App\Http\Controllers\Api\V2\Support\Ticket\CloseController;
use App\Http\Controllers\Api\V2\Support\Ticket\RateController;
use App\Http\Controllers\Api\V2\Support\TopicController;
use App\Http\Controllers\Api\V2\Taggable\FollowController;
use App\Http\Controllers\Api\V2\TaggableController;
use App\Http\Controllers\Api\V2\Twilio\TokenController;
use App\Http\Controllers\Api\V2\Twilio\Webhook\Call\ActionController;
use App\Http\Controllers\Api\V2\Twilio\Webhook\Call\Client;
use App\Http\Controllers\Api\V2\Twilio\Webhook\Call\CompleteController;
use App\Http\Controllers\Api\V2\Twilio\Webhook\Call\FallbackController;
use App\Http\Controllers\Api\V2\Twilio\Webhook\CallController;
use App\Http\Controllers\Api\V2\User\FollowedPostController;
use App\Http\Controllers\Api\V2\User\SettingController;
use App\Http\Controllers\ProductController as LegacyProductController;
use App\Http\Middleware\AuthenticateUser;
use App\Http\Middleware\DenyAgentGroupSettingIfNoAgentAuthenticated;
use App\Http\Middleware\ValidateTwilioRequest;
use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Routing\Router;

Route::middleware([AuthenticateUser::class])->group(function(Router $route) {
    $route->prefix('user')->group(function(Router $route) {
        $route->get('followed-posts', [FollowedPostController::class, 'index'])
            ->name(RouteNames::API_V2_USER_FOLLOWED_POST_INDEX);

        $route->patch('settings/{' . RouteParameters::SETTING_USER . '}', [SettingController::class, 'update'])
            ->middleware(DenyAgentGroupSettingIfNoAgentAuthenticated::class)
            ->name(RouteNames::API_V2_USER_SETTING_UPDATE);
    });

    $route->prefix('posts')->group(function(Router $route) {
        $route->get('/', [PostController::class, 'index'])->name(RouteNames::API_V2_POST_INDEX);
        $route->post('/', [PostController::class, 'store'])->name(RouteNames::API_V2_POST_STORE);
        $route->prefix('{' . RouteParameters::POST . '}')->group(function(Router $route) {
            $route->get('/', [PostController::class, 'show'])->name(RouteNames::API_V2_POST_SHOW);
            $route->patch('/', [PostController::class, 'update'])
                ->name(RouteNames::API_V2_POST_UPDATE)
                ->middleware('can:update,' . RouteParameters::POST);
            $route->delete('/', [PostController::class, 'delete'])
                ->name(RouteNames::API_V2_POST_DELETE)
                ->middleware('can:delete,' . RouteParameters::POST);

            $route->prefix('comments')->group(function(Router $route) {
                $route->get('/', [CommentController::class, 'index'])->name(RouteNames::API_V2_POST_COMMENT_INDEX);
                $route->post('/', [CommentController::class, 'store'])->name(RouteNames::API_V2_POST_COMMENT_STORE);
                $route->prefix('{' . RouteParameters::COMMENT . ':' . Comment::routeKeyName() . '}')->group(function(
                    Router $route
                ) {
                    $route->patch('/', [CommentController::class, 'update'])
                        ->name(RouteNames::API_V2_POST_COMMENT_UPDATE)
                        ->middleware('can:update,' . RouteParameters::COMMENT);
                    $route->delete('/', [CommentController::class, 'delete'])
                        ->name(RouteNames::API_V2_POST_COMMENT_DELETE)
                        ->middleware('can:delete,' . RouteParameters::COMMENT);

                    $route->prefix('vote')->group(function(Router $route) {
                        $route->post('/', [VoteController::class, 'store'])
                            ->name(RouteNames::API_V2_POST_COMMENT_VOTE_STORE);
                        $route->delete('/', [VoteController::class, 'delete'])
                            ->name(RouteNames::API_V2_POST_COMMENT_VOTE_DELETE);
                    });
                });
            });

            $route->prefix('solution')->group(function(Router $route) {
                $route->post('/', [SolutionController::class, 'store'])
                    ->name(RouteNames::API_V2_POST_SOLUTION_STORE)
                    ->middleware('can:solve,' . RouteParameters::POST);

                $route->delete('/{' . RouteParameters::COMMENT . ':' . Comment::routeKeyName() . '}',
                    [SolutionController::class, 'delete'])
                    ->name(RouteNames::API_V2_POST_SOLUTION_DELETE)
                    ->middleware('can:unSolve,' . RouteParameters::POST);
            });
        });
    });

    $route->prefix('tags')->group(function(Router $route) {
        $route->get('/', [TaggableController::class, 'index'])->name(RouteNames::API_V2_TAGGABLE_INDEX);
        $route->prefix('{' . RouteParameters::TAGGABLE . '}')->group(function(
            Router $route
        ) {
            $route->get('/', [TaggableController::class, 'show'])->name(RouteNames::API_V2_TAGGABLE_SHOW);
            $route->prefix('follow')->group(function(
                Router $route
            ) {
                $route->post('/', [FollowController::class, 'store'])->name(RouteNames::API_V2_TAGGABLE_FOLLOW_STORE);
                $route->delete('/', [FollowController::class, 'delete'])
                    ->name(RouteNames::API_V2_TAGGABLE_FOLLOW_DELETE);
            });
        });
    });

    $route->prefix('twilio')->group(function(Router $route) {
        $route->prefix('token')->group(function(Router $route) {
            $route->post('/', [TokenController::class, 'store'])->name(RouteNames::API_V2_TWILIO_TOKEN_STORE);
        });
    });

    $route->prefix('agents')->group(function(Router $route) {
        $route->get('/', [AgentController::class, 'index'])->name(RouteNames::API_V2_AGENT_INDEX);
    });

    $route->prefix('push-notification-token')->group(function(Router $route) {
        $route->post('/', [PushNotificationTokenController::class, 'store'])
            ->name(RouteNames::API_V2_PUSH_NOTIFICATION_TOKEN_STORE);
    });

    $route->prefix('support')->group(function(Router $route) {
        $route->prefix('topics')->group(function(Router $route) {
            $route->get('/', [TopicController::class, 'index'])->name(RouteNames::API_V2_SUPPORT_TOPIC_INDEX);
        });
        $route->prefix('ticket')->group(function(Router $route) {
            $route->get('agent-history', [AgentHistoryController::class, 'index'])
                ->middleware('can:seeAgentHistory,' . Ticket::class)
                ->name(RouteNames::API_V2_SUPPORT_TICKET_AGENT_HISTORY_INDEX);
            $route->prefix('{' . RouteParameters::TICKET . '}')->group(function(Router $route) {
                $route->post('close', [CloseController::class, 'store'])
                    ->middleware('can:close,' . RouteParameters::TICKET)
                    ->name(RouteNames::API_V2_SUPPORT_TICKET_CLOSE_STORE);
                $route->post('rate', [RateController::class, 'store'])
                    ->middleware('can:rate,' . RouteParameters::TICKET)
                    ->name(RouteNames::API_V2_SUPPORT_TICKET_RATE_STORE);
                $route->post('agent-rate', [AgentRateController::class, 'store'])
                    ->middleware('can:agentRate,' . RouteParameters::TICKET)
                    ->name(RouteNames::API_V2_SUPPORT_TICKET_AGENT_RATE_STORE);
            });
        });
    });

    $route->prefix('products')->group(function(Router $route) {
        $route->prefix('{' . RouteParameters::PRODUCT . '}')->group(function(Router $route) {
            $route->get('/', [LegacyProductController::class, 'info']);
        });
    });

    $route->prefix('activity')->group(function(Router $route) {
        $route->get('/', [ActivityController::class, 'index'])->name(RouteNames::API_V2_ACTIVITY_INDEX);
    });

    $route->prefix('internal-notifications')->group(function(Router $route) {
        $route->get('/', [InternalNotificationController::class, 'index'])
            ->name(RouteNames::API_V2_INTERNAL_NOTIFICATION_INDEX);
        $route->get('/{' . RouteParameters::INTERNAL_NOTIFICATION . '}',
            [InternalNotificationController::class, 'show'])
            ->name(RouteNames::API_V2_INTERNAL_NOTIFICATION_SHOW)
            ->middleware('can:read,' . RouteParameters::INTERNAL_NOTIFICATION);
    });
});

Route::prefix('twilio/webhook/call')->middleware(ValidateTwilioRequest::class)->group(function(Router $route) {
    $route->post('/', [CallController::class, 'store'])->name(RouteNames::API_V2_TWILIO_WEBHOOK_CALL_STORE);
    $route->post('action', [ActionController::class, 'store'])
        ->name(RouteNames::API_V2_TWILIO_WEBHOOK_CALL_ACTION_STORE);
    $route->post('complete', [CompleteController::class, 'store'])
        ->name(RouteNames::API_V2_TWILIO_WEBHOOK_CALL_COMPLETE_STORE);
    $route->post('fallback', [FallbackController::class, 'store'])
        ->name(RouteNames::API_V2_TWILIO_WEBHOOK_CALL_FALLBACK_STORE);
    $route->post('client/status', [Client\StatusController::class, 'store'])
        ->name(RouteNames::API_V2_TWILIO_WEBHOOK_CALL_CLIENT_STATUS_STORE);
});
