<?php

namespace App\Providers;

use App\AppNotification;
use App\Events;
use App\Listeners\AgentCall\SendAgentAnsweredNotification;
use App\Listeners\AgentCall\SendTechCallingNotification;
use App\Listeners\AgentCall\SendTechEngagedNotification;
use App\Listeners\AuthenticationCode\SendSmsRequestedNotification;
use App\Listeners\AuthenticationCode\StartPhoneAuthenticationCall;
use App\Listeners\Order\AddPoints;
use App\Listeners\Order\CreateOrderInvoice;
use App\Listeners\Order\DelayCompleteApprovedJob;
use App\Listeners\Order\Delivery\Curri\CalculateUserConfirmationTime;
use App\Listeners\Order\Delivery\Curri\DelayCompleteDoneJob;
use App\Listeners\Order\Delivery\Curri\RemoveSupplierDeliveryInformation;
use App\Listeners\Order\Delivery\Curri\RemoveUserDeliveryInformation;
use App\Listeners\Order\Delivery\Curri\SetDeliverySupplierInformation;
use App\Listeners\Order\Delivery\Curri\SetUserDeliveryInformation;
use App\Listeners\Order\Delivery\SendOrderEtaUpdatedInAppNotification;
use App\Listeners\Order\ItemOrder\RemovePointsOnRemoved;
use App\Listeners\Order\ProcessInvoiceOnCanceledOrder;
use App\Listeners\Order\RemovePointsOnCanceled;
use App\Listeners\Order\SendApprovedByTeamInAppNotification;
use App\Listeners\Order\SendApprovedByTeamSmsNotification;
use App\Listeners\Order\SendApprovedNotification;
use App\Listeners\Order\SendAssignInAppNotification;
use App\Listeners\Order\SendAssignSmsNotification;
use App\Listeners\Order\SendCanceledByUserNotification;
use App\Listeners\Order\SendCanceledNotification;
use App\Listeners\Order\SendChatApprovedNotification;
use App\Listeners\Order\SendCreatedNotification;
use App\Listeners\Order\SendDeclinedNotification;
use App\Listeners\Order\SendPointsEarnedInAppNotification;
use App\Listeners\Order\SendPointsEarnedSmsNotification;
use App\Listeners\Order\SendSentForApprovalNotification;
use App\Listeners\OrderSnap\SaveOrderSnapInformation;
use App\Listeners\Phone\DelayRemoveVerifiedUnassignedJob;
use App\Listeners\SendCommentPostRepliedNotification;
use App\Listeners\SendCommentUsersTaggedNotification;
use App\Listeners\SendHatRequestedEmail;
use App\Listeners\SendPostCreatedNotification;
use App\Listeners\SendPostRepliedNotification;
use App\Listeners\SendPostSolvedNotification;
use App\Listeners\SendSolutionCreatedNotification;
use App\Listeners\Service\CreateLog;
use App\Listeners\Supplier\LogOrderIntoMissedOrderRequest;
use App\Listeners\Supplier\SendPubnubNewMessageNotification as SupplierSendNewMessageNotification;
use App\Listeners\Supplier\SendSelectionNotification;
use App\Listeners\Supplier\UpdateCustomersCounter;
use App\Listeners\Supplier\UpdateInboundCounter;
use App\Listeners\Supplier\UpdateLastOrderCanceledAt;
use App\Listeners\Supplier\UpdateOutboundCounter;
use App\Listeners\Supplier\UpdateTotalCustomers;
use App\Listeners\User\CallUserVerificationProcess;
use App\Listeners\User\CreateHubspotContact;
use App\Listeners\User\SendCurriDeliveryArrivedAtDestinationInAppNotification;
use App\Listeners\User\SendCurriDeliveryArrivedAtDestinationSmsNotification;
use App\Listeners\User\SendCurriDeliveryConfirmationRequiredPushNotification;
use App\Listeners\User\SendCurriDeliveryConfirmationRequiredSmsNotification;
use App\Listeners\User\SendCurriDeliveryOnRoutePubnubMessage;
use App\Listeners\User\SendCurriDeliveryOnRoutePushNotification;
use App\Listeners\User\SendCurriDeliveryOnRouteSmsNotification;
use App\Listeners\User\SendInitialPubnubMessage;
use App\Listeners\User\SendNewMessagePubnubNotification as UserSendNewMessageNotification;
use App\Listeners\User\UpdateHubspotCompany;
use App\Listeners\User\UpdateHubspotContact;
use App\Listeners\User\UpdateHubspotStores;
use App\Listeners\User\UpdatePendingApprovalOrdersCounter;
use App\Models\Agent;
use App\Models\AuthenticationCode;
use App\Models\CartItem;
use App\Models\Comment;
use App\Models\Communication;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\CurriDelivery;
use App\Models\InternalNotification;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\ItemWishlist;
use App\Models\Oem;
use App\Models\OemPart;
use App\Models\OemSearchCounter;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\PartSearchCounter;
use App\Models\Phone;
use App\Models\Post;
use App\Models\PubnubChannel;
use App\Models\PushNotificationToken;
use App\Models\Replacement;
use App\Models\Series;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupplySearchCounter;
use App\Models\SupportCall;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\XoxoRedemption;
use App\Observers\AgentObserver;
use App\Observers\AppNotificationObserver;
use App\Observers\AuthenticationCodeObserver;
use App\Observers\CartItemObserver;
use App\Observers\CommentObserver;
use App\Observers\CommunicationObserver;
use App\Observers\CompanyObserver;
use App\Observers\CompanyUserObserver;
use App\Observers\CurriDeliveryObserver;
use App\Observers\InternalNotificationObserver;
use App\Observers\ItemObserver;
use App\Observers\ItemOrderObserver;
use App\Observers\ItemWishlistObserver;
use App\Observers\LegacyUserObserver;
use App\Observers\OemObserver;
use App\Observers\OemPartObserver;
use App\Observers\OemSearchCounterObserver;
use App\Observers\OrderDeliveryObserver;
use App\Observers\OrderObserver;
use App\Observers\OrderSubstatusObserver;
use App\Observers\PartSearchCounterObserver;
use App\Observers\PhoneObserver;
use App\Observers\PostObserver;
use App\Observers\PubnubChannelObserver;
use App\Observers\PushNotificationTokenObserver;
use App\Observers\ReplacementObserver;
use App\Observers\SeriesObserver;
use App\Observers\StaffObserver;
use App\Observers\SupplierObserver;
use App\Observers\SupplySearchCounterObserver;
use App\Observers\SupportCallObserver;
use App\Observers\TicketObserver;
use App\Observers\UserObserver;
use App\Observers\WishlistObserver;
use App\Observers\XoxoRedemptionObserver;
use App\User as LegacyUser;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // User Related Events...
        'Laravel\Spark\Events\Subscription\UserSubscribed' => [
            'Laravel\Spark\Listeners\Subscription\UpdateActiveSubscription',
            'Laravel\Spark\Listeners\Subscription\UpdateTrialEndingDate',
        ],

        'Laravel\Spark\Events\Profile\ContactInformationUpdated' => [
            'Laravel\Spark\Listeners\Profile\UpdateContactInformationOnStripe',
        ],

        'Laravel\Spark\Events\PaymentMethod\VatIdUpdated' => [
            'Laravel\Spark\Listeners\Subscription\UpdateTaxPercentageOnStripe',
        ],

        'Laravel\Spark\Events\PaymentMethod\BillingAddressUpdated' => [
            'Laravel\Spark\Listeners\Subscription\UpdateTaxPercentageOnStripe',
        ],

        'Laravel\Spark\Events\Subscription\SubscriptionUpdated' => [
            'Laravel\Spark\Listeners\Subscription\UpdateActiveSubscription',
        ],

        'Laravel\Spark\Events\Subscription\SubscriptionCancelled' => [
            'Laravel\Spark\Listeners\Subscription\UpdateActiveSubscription',
        ],

        // Team Related Events...
        'Laravel\Spark\Events\Teams\TeamCreated'                  => [
            'Laravel\Spark\Listeners\Teams\UpdateOwnerSubscriptionQuantity',
        ],

        'Laravel\Spark\Events\Teams\TeamDeleted' => [
            'Laravel\Spark\Listeners\Teams\UpdateOwnerSubscriptionQuantity',
        ],

        'Laravel\Spark\Events\Teams\TeamMemberAdded' => [
            'Laravel\Spark\Listeners\Teams\UpdateTeamSubscriptionQuantity',
        ],

        'Laravel\Spark\Events\Teams\TeamMemberRemoved' => [
            'Laravel\Spark\Listeners\Teams\UpdateTeamSubscriptionQuantity',
        ],

        'Laravel\Spark\Events\Teams\Subscription\TeamSubscribed' => [
            'Laravel\Spark\Listeners\Teams\Subscription\UpdateActiveSubscription',
            'Laravel\Spark\Listeners\Teams\Subscription\UpdateTrialEndingDate',
        ],

        'Laravel\Spark\Events\Teams\Subscription\SubscriptionUpdated' => [
            'Laravel\Spark\Listeners\Teams\Subscription\UpdateActiveSubscription',
        ],

        'Laravel\Spark\Events\Teams\Subscription\SubscriptionCancelled' => [
            'Laravel\Spark\Listeners\Teams\Subscription\UpdateActiveSubscription',
        ],

        'Laravel\Spark\Events\Teams\UserInvitedToTeam' => [
            'Laravel\Spark\Listeners\Teams\CreateInvitationNotification',
        ],

        Events\AgentCall\Answered::class => [
            SendAgentAnsweredNotification::class,
            SendTechEngagedNotification::class,
        ],

        Events\AgentCall\Ringing::class => [
            SendTechCallingNotification::class,
        ],

        Events\AuthenticationCode\CallRequested::class => [
            StartPhoneAuthenticationCall::class,
        ],

        Events\AuthenticationCode\SmsRequested::class => [
            SendSmsRequestedNotification::class,
        ],

        Events\Order\LegacyApproved::class => [
            SendApprovedNotification::class,
            UpdateOutboundCounter::class,
            UpdatePendingApprovalOrdersCounter::class,
            CreateOrderInvoice::class,
            DelayCompleteApprovedJob::class,
            AddPoints::class,
            CalculateUserConfirmationTime::class,
            SaveOrderSnapInformation::class,
        ],

        Events\Order\Approved::class => [
            SendApprovedNotification::class,
            SendChatApprovedNotification::class,
            CreateOrderInvoice::class,
            SaveOrderSnapInformation::class,
        ],

        Events\Order\ApprovedByTeam::class => [
            SendApprovedByTeamInAppNotification::class,
            SendApprovedByTeamSmsNotification::class,
        ],

        Events\Order\Assigned::class => [
            SendAssignInAppNotification::class,
            SendAssignSmsNotification::class,
        ],

        Events\Order\Canceled::class => [
            UpdateOutboundCounter::class,
            UpdatePendingApprovalOrdersCounter::class,
            RemovePointsOnCanceled::class,
            ProcessInvoiceOnCanceledOrder::class,
            SendCanceledNotification::class,
            RemoveUserDeliveryInformation::class,
        ],

        Events\Order\CanceledByUser::class => [
            SendCanceledByUserNotification::class,
            UpdateInboundCounter::class,
            UpdateLastOrderCanceledAt::class,
            UpdatePendingApprovalOrdersCounter::class,
        ],

        Events\Order\LegacyCompleted::class => [
            UpdateOutboundCounter::class,
        ],

        Events\Order\Completed::class => [
            UpdateOutboundCounter::class,
            AddPoints::class,
        ],

        Events\Order\Created::class => [
            SendCreatedNotification::class,
            UpdateInboundCounter::class,
            LogOrderIntoMissedOrderRequest::class,
            SaveOrderSnapInformation::class,
        ],

        Events\Order\Declined::class => [
            SendDeclinedNotification::class,
            UpdateInboundCounter::class,
            UpdateOutboundCounter::class,
        ],

        Events\Order\DeliveryEtaUpdated::class => [
            CalculateUserConfirmationTime::class,
            SendOrderEtaUpdatedInAppNotification::class,
        ],

        Events\Order\Delivery\Curri\ArrivedAtDestination::class => [
            SendCurriDeliveryArrivedAtDestinationInAppNotification::class,
            SendCurriDeliveryArrivedAtDestinationSmsNotification::class,
            DelayCompleteDoneJob::class,
        ],

        Events\Order\Delivery\Curri\Booked::class => [
            SetDeliverySupplierInformation::class,
        ],

        Events\Order\Delivery\Curri\ConfirmedByUser::class => [
            RemoveUserDeliveryInformation::class,
        ],

        Events\Order\Delivery\Curri\Notice\EnRoute\ConfirmedBySupplier::class => [
            RemoveSupplierDeliveryInformation::class,
        ],

        Events\Order\Delivery\Curri\OnRoute::class => [
            SendCurriDeliveryOnRoutePubnubMessage::class,
            SendCurriDeliveryOnRoutePushNotification::class,
            SendCurriDeliveryOnRouteSmsNotification::class,
        ],

        Events\Order\Delivery\Curri\UserConfirmationRequired::class => [
            SetUserDeliveryInformation::class,
            SendCurriDeliveryConfirmationRequiredPushNotification::class,
            SendCurriDeliveryConfirmationRequiredSmsNotification::class,
        ],

        Events\Order\ItemOrder\Removed::class => [
            RemovePointsOnRemoved::class,
        ],

        Events\Order\PointsEarned::class => [
            SendPointsEarnedInAppNotification::class,
            SendPointsEarnedSmsNotification::class,
        ],

        Events\Order\Reopen::class => [
            UpdatePendingApprovalOrdersCounter::class,
        ],

        Events\Order\SentForApproval::class => [
            SendSentForApprovalNotification::class,
            UpdateInboundCounter::class,
            UpdatePendingApprovalOrdersCounter::class,
            SaveOrderSnapInformation::class,
        ],

        Events\Phone\Verified::class => [
            DelayRemoveVerifiedUnassignedJob::class,
        ],

        Events\Post\Created::class => [
            SendPostCreatedNotification::class,
        ],

        Events\Post\Comment\Created::class => [
            SendPostRepliedNotification::class,
            SendCommentPostRepliedNotification::class,
        ],

        Events\Post\Comment\UserTagged::class => [
            SendCommentUsersTaggedNotification::class,
        ],

        Events\Post\Solution\Created::class => [
            SendSolutionCreatedNotification::class,
            SendPostSolvedNotification::class,
        ],

        Events\PubnubChannel\Created::class => [
            SendInitialPubnubMessage::class,
        ],

        Events\Service\Log::class => [
            CreateLog::class,
        ],

        Events\Supplier\NewMessage::class => [
            SupplierSendNewMessageNotification::class,
        ],

        Events\Supplier\Selected::class => [
            SendSelectionNotification::class,
            UpdateCustomersCounter::class,
            UpdateTotalCustomers::class,
        ],

        Events\Supplier\Unselected::class => [
            UpdateTotalCustomers::class,
            UpdateCustomersCounter::class,
        ],

        Events\User\CompanyUpdated::class => [
            UpdateHubspotCompany::class,
        ],

        Events\User\ConfirmedBySupplier::class => [
            UpdateCustomersCounter::class,
        ],

        Events\User\Created::class => [
            CreateHubspotContact::class,
        ],

        Events\User\HatRequested::class => [
            SendHatRequestedEmail::class,
        ],

        Events\User\HubspotFieldUpdated::class => [
            UpdateHubspotContact::class,
        ],

        Events\User\NewMessage::class => [
            UserSendNewMessageNotification::class,
        ],

        Events\User\SuppliersUpdated::class => [
            UpdateHubspotStores::class,
            CallUserVerificationProcess::class,
        ],

        Events\User\UnconfirmedBySupplier::class => [
            UpdateCustomersCounter::class,
        ],

        Events\User\RemovedBySupplier::class => [
            UpdateCustomersCounter::class,
        ],

        Events\User\RestoredBySupplier::class => [
            UpdateCustomersCounter::class,
        ],
    ];

    public function boot()
    {
        parent::boot();

        Agent::observe(AgentObserver::class);
        AppNotification::observe(AppNotificationObserver::class);
        AuthenticationCode::observe(AuthenticationCodeObserver::class);
        CartItem::observe(CartItemObserver::class);
        Comment::observe(CommentObserver::class);
        Communication::observe(CommunicationObserver::class);
        Company::observe(CompanyObserver::class);
        CompanyUser::observe(CompanyUserObserver::class);
        CurriDelivery::observe(CurriDeliveryObserver::class);
        InternalNotification::observe(InternalNotificationObserver::class);
        Item::observe(ItemObserver::class);
        ItemOrder::observe(ItemOrderObserver::class);
        ItemWishlist::observe(ItemWishlistObserver::class);
        LegacyUser::observe(LegacyUserObserver::class);
        Oem::observe(OemObserver::class);
        OemPart::observe(OemPartObserver::class);
        OemSearchCounter::observe(OemSearchCounterObserver::class);
        Order::observe(OrderObserver::class);
        OrderDelivery::observe(OrderDeliveryObserver::class);
        OrderSubstatus::observe(OrderSubstatusObserver::class);
        PartSearchCounter::observe(PartSearchCounterObserver::class);
        Phone::observe(PhoneObserver::class);
        Post::observe(PostObserver::class);
        PubnubChannel::observe(PubnubChannelObserver::class);
        PushNotificationToken::observe(PushNotificationTokenObserver::class);
        Replacement::observe(ReplacementObserver::class);
        Series::observe(SeriesObserver::class);
        Staff::observe(StaffObserver::class);
        SupplySearchCounter::observe(SupplySearchCounterObserver::class);
        Supplier::observe(SupplierObserver::class);
        SupportCall::observe(SupportCallObserver::class);
        Ticket::observe(TicketObserver::class);
        User::observe(UserObserver::class);
        Wishlist::observe(WishlistObserver::class);
        XoxoRedemption::observe(XoxoRedemptionObserver::class);
    }
}
